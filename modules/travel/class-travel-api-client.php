<?php
/**
 * 楽天トラベルAPIクライアント
 * 宿泊施設情報を取得
 */
class Travel_API_Client {
    
    /**
     * 楽天トラベル空室検索API URL
     */
    const API_URL = 'https://app.rakuten.co.jp/services/api/Travel/VacantHotelSearch/20170426';
    
    /**
     * APIキー
     */
    private $application_id;
    
    /**
     * アフィリエイトID
     */
    private $affiliate_id;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // 設定から取得
        $settings = get_option('beer_affiliate_settings', array());
        $this->application_id = isset($settings['rakuten_application_id']) ? $settings['rakuten_application_id'] : '';
        $this->affiliate_id = isset($settings['rakuten_affiliate_id']) ? $settings['rakuten_affiliate_id'] : '';
    }
    
    /**
     * 都市名で宿泊施設を検索
     * 
     * @param string $city_name 都市名
     * @param array $options オプション（チェックイン日など）
     * @return array 宿泊施設情報
     */
    public function search_hotels($city_name, $options = array()) {
        // APIキーがない場合は空の配列を返す
        if (empty($this->application_id)) {
            return array();
        }
        
        // デフォルトパラメータ
        $params = array(
            'applicationId' => $this->application_id,
            'affiliateId' => $this->affiliate_id,
            'format' => 'json',
            'keyword' => $city_name,
            'hits' => isset($options['hits']) ? $options['hits'] : 3, // デフォルトは3件
            'datumType' => 1,
            'sort' => isset($options['sort']) ? $options['sort'] : '+roomCharge' // 価格の安い順
        );
        
        // チェックイン・チェックアウト日を設定（明日から1泊）
        if (!isset($options['checkinDate'])) {
            $tomorrow = new DateTime('tomorrow');
            $day_after = new DateTime('tomorrow');
            $day_after->modify('+1 day');
            
            $params['checkinDate'] = $tomorrow->format('Y-m-d');
            $params['checkoutDate'] = $day_after->format('Y-m-d');
        } else {
            $params['checkinDate'] = $options['checkinDate'];
            $params['checkoutDate'] = $options['checkoutDate'];
        }
        
        // 大人2名で検索
        $params['adultNum'] = isset($options['adultNum']) ? $options['adultNum'] : 2;
        
        // APIリクエスト
        $url = self::API_URL . '?' . http_build_query($params);
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'Beer Affiliate Engine/' . BEER_AFFILIATE_VERSION
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('楽天トラベルAPI エラー: ' . $response->get_error_message());
            return array();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || isset($data['error'])) {
            if (isset($data['error_description'])) {
                error_log('楽天トラベルAPI エラー: ' . $data['error_description']);
            }
            return array();
        }
        
        // ホテル情報を整形
        $hotels = array();
        
        if (isset($data['hotels']) && is_array($data['hotels'])) {
            foreach ($data['hotels'] as $hotel_data) {
                if (!isset($hotel_data['hotel'])) {
                    continue;
                }
                
                $hotel_info = $hotel_data['hotel'][0]['hotelBasicInfo'];
                $room_info = isset($hotel_data['hotel'][1]) ? $hotel_data['hotel'][1]['roomInfo'][0]['roomBasicInfo'] : null;
                
                $hotel = array(
                    'name' => $hotel_info['hotelName'],
                    'url' => $hotel_info['hotelInformationUrl'],
                    'thumbnail' => $hotel_info['hotelThumbnailUrl'],
                    'image' => isset($hotel_info['roomImageUrl']) ? $hotel_info['roomImageUrl'] : $hotel_info['hotelImageUrl'],
                    'price' => isset($hotel_info['hotelMinCharge']) ? $hotel_info['hotelMinCharge'] : 0,
                    'price_formatted' => isset($hotel_info['hotelMinCharge']) ? number_format($hotel_info['hotelMinCharge']) . '円〜' : '価格未定',
                    'review_average' => isset($hotel_info['reviewAverage']) ? $hotel_info['reviewAverage'] : null,
                    'review_count' => isset($hotel_info['reviewCount']) ? $hotel_info['reviewCount'] : 0,
                    'postal_code' => isset($hotel_info['postalCode']) ? $hotel_info['postalCode'] : '',
                    'address' => isset($hotel_info['address1']) ? $hotel_info['address1'] . $hotel_info['address2'] : '',
                    'telephone' => isset($hotel_info['telephoneNo']) ? $hotel_info['telephoneNo'] : '',
                    'access' => isset($hotel_info['access']) ? $hotel_info['access'] : '',
                    'parking' => isset($hotel_info['parkingInformation']) ? $hotel_info['parkingInformation'] : '',
                    'room_name' => $room_info ? $room_info['roomName'] : '',
                    'plan_name' => $room_info ? $room_info['planName'] : ''
                );
                
                // アフィリエイトURL生成
                if (!empty($this->affiliate_id)) {
                    $affiliate_url = sprintf(
                        'https://hb.afl.rakuten.co.jp/hgc/%s/?pc=%s&m=%s',
                        $this->affiliate_id,
                        urlencode($hotel['url']),
                        urlencode($hotel['url'])
                    );
                    $hotel['affiliate_url'] = $affiliate_url;
                } else {
                    $hotel['affiliate_url'] = $hotel['url'];
                }
                
                $hotels[] = $hotel;
            }
        }
        
        return $hotels;
    }
    
    /**
     * 宿泊施設情報をHTMLで表示
     * 
     * @param array $hotels 宿泊施設情報
     * @param string $template テンプレートタイプ
     * @return string HTML
     */
    public function render_hotels($hotels, $template = 'card') {
        if (empty($hotels)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="beer-affiliate-hotels">
            <h3 class="beer-affiliate-hotels-title">🏨 おすすめの宿泊施設</h3>
            <div class="beer-affiliate-hotels-list">
                <?php foreach ($hotels as $hotel): ?>
                    <div class="beer-affiliate-hotel-card">
                        <div class="beer-affiliate-hotel-image">
                            <img src="<?php echo esc_url($hotel['thumbnail']); ?>" alt="<?php echo esc_attr($hotel['name']); ?>" loading="lazy">
                        </div>
                        <div class="beer-affiliate-hotel-info">
                            <h4 class="beer-affiliate-hotel-name">
                                <a href="<?php echo esc_url($hotel['affiliate_url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html($hotel['name']); ?>
                                </a>
                            </h4>
                            <div class="beer-affiliate-hotel-price">
                                <span class="price-label">料金：</span>
                                <span class="price-value"><?php echo esc_html($hotel['price_formatted']); ?></span>
                            </div>
                            <?php if ($hotel['review_average']): ?>
                                <div class="beer-affiliate-hotel-review">
                                    <span class="review-stars">★<?php echo number_format($hotel['review_average'], 1); ?></span>
                                    <span class="review-count">(<?php echo number_format($hotel['review_count']); ?>件)</span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($hotel['access'])): ?>
                                <div class="beer-affiliate-hotel-access">
                                    <small><?php echo esc_html(mb_strimwidth($hotel['access'], 0, 60, '...')); ?></small>
                                </div>
                            <?php endif; ?>
                            <div class="beer-affiliate-hotel-action">
                                <a href="<?php echo esc_url($hotel['affiliate_url']); ?>" class="beer-affiliate-button" target="_blank" rel="noopener noreferrer">
                                    詳細を見る
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="beer-affiliate-hotels-footer">
                <p class="beer-affiliate-notice">※ 料金は参考価格です。実際の料金は日程や条件により変動します。</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}