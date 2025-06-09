<?php
/**
 * 旅行モジュール改良版
 * ユーザーフレンドリーな表示と正しいリンク生成
 */
class Travel_Module_V2 extends Base_Affiliate_Module {
    
    /**
     * モジュール名
     */
    protected $module_name = 'travel_v2';
    
    /**
     * 表示名
     */
    protected $display_name = '旅行＆ビール体験';
    
    /**
     * 優先度
     */
    protected $module_priority = 90;
    
    /**
     * コンテンツアナライザー
     */
    private $content_analyzer;
    
    /**
     * リンクジェネレーター
     */
    private $link_generator;
    
    /**
     * ディスプレイマネージャー
     */
    private $display_manager;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct();
        
        // 必要なクラスをロード
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-content-analyzer.php';
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-link-generator.php';
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-display-manager.php';
        
        $this->content_analyzer = new Travel_Content_Analyzer();
        $this->link_generator = new Travel_Link_Generator();
        $this->display_manager = new Travel_Display_Manager();
    }
    
    /**
     * キーワードを抽出
     */
    public function extract_keywords($content) {
        return $this->content_analyzer->analyze($content);
    }
    
    /**
     * リンクを生成（ユーザーフレンドリー版）
     */
    public function generate_links($keywords) {
        $all_links = array();
        $locations = isset($keywords['locations']) ? $keywords['locations'] : array();
        
        if (empty($locations)) {
            return array();
        }
        
        // 最初の地域に焦点を当てる
        $primary_location = reset($locations);
        $location_name = $primary_location['name'];
        $is_international = isset($primary_location['country']) && $primary_location['country'] !== '日本';
        
        // カテゴリー別にリンクを整理
        $categories = array(
            'hotel' => array(
                'title' => "🏨 {$location_name}で泊まる",
                'links' => array()
            ),
            'experience' => array(
                'title' => "🍺 {$location_name}のビール体験",
                'links' => array()
            ),
            'travel' => array(
                'title' => "✈️ {$location_name}への旅行プラン",
                'links' => array()
            )
        );
        
        // ホテル予約リンク
        $hotel_links = $this->generate_hotel_links($primary_location);
        if (!empty($hotel_links)) {
            $categories['hotel']['links'] = $hotel_links;
        }
        
        // ビール体験リンク
        $experience_links = $this->generate_experience_links($primary_location);
        if (!empty($experience_links)) {
            $categories['experience']['links'] = $experience_links;
        }
        
        // 旅行プランリンク
        $travel_links = $this->generate_travel_links($primary_location);
        if (!empty($travel_links)) {
            $categories['travel']['links'] = $travel_links;
        }
        
        // 空のカテゴリーを除外
        foreach ($categories as $key => $category) {
            if (!empty($category['links'])) {
                $all_links[$key] = $category;
            }
        }
        
        return $all_links;
    }
    
    /**
     * ホテル予約リンクを生成
     */
    private function generate_hotel_links($location) {
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-programs.php';
        $links = array();
        $city = $location['name'];
        
        // 楽天トラベル
        $links[] = array(
            'url' => Beer_Affiliate_Programs::generate_rakuten_url(array(
                'f_area' => $city,
                'f_keyword' => $city . ' クラフトビール'
            )),
            'label' => "楽天トラベルで{$city}のホテルを探す",
            'description' => '口コミ評価の高いホテルを表示',
            'service' => '楽天トラベル'
        );
        
        // JTB
        $jtb_url = Beer_Affiliate_Programs::generate_a8_url(
            'JTB国内旅行',
            "https://www.jtb.co.jp/kokunai/hotel/list/{$city}/"
        );
        if ($jtb_url) {
            $links[] = array(
                'url' => $jtb_url,
                'label' => "JTBで{$city}のホテルを予約",
                'description' => '安心の大手旅行会社',
                'service' => 'JTB'
            );
        }
        
        // J-TRIP（JAL格安国内旅行）
        $jtrip_url = Beer_Affiliate_Programs::generate_a8_url(
            'J-TRIP',
            "https://www.jtrip.co.jp/hotel/search/?keyword={$city}"
        );
        if ($jtrip_url) {
            $links[] = array(
                'url' => $jtrip_url,
                'label' => "JALで{$city}へお得に旅行",
                'description' => 'JAL航空券とホテルのセット',
                'service' => 'J-TRIP'
            );
        }
        
        return $links;
    }
    
    /**
     * ビール体験リンクを生成
     */
    private function generate_experience_links($location) {
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-programs.php';
        $links = array();
        $city = $location['name'];
        
        // 一休レストラン
        $ikyu_url = Beer_Affiliate_Programs::generate_a8_url(
            '一休.comレストラン',
            "https://restaurant.ikyu.com/search/?keyword={$city}+ビール"
        );
        if ($ikyu_url) {
            $links[] = array(
                'url' => $ikyu_url,
                'label' => "{$city}のビアレストランを予約",
                'description' => '人気のビアレストランを厳選',
                'service' => '一休レストラン'
            );
        }
        
        // JTBショッピング（地ビール）
        $jtb_shopping_url = Beer_Affiliate_Programs::generate_a8_url(
            'JTBショッピング',
            "https://shopping.jtb.co.jp/search/?q={$city}+地ビール"
        );
        if ($jtb_shopping_url) {
            $links[] = array(
                'url' => $jtb_shopping_url,
                'label' => "{$city}の地ビールをお取り寄せ",
                'description' => '現地の味を自宅で楽しむ',
                'service' => 'JTBショッピング'
            );
        }
        
        // Otomoni（クラフトビール定期便）
        $otomoni_url = Beer_Affiliate_Programs::generate_a8_url(
            'Otomoni',
            'https://otomoni.jp/'
        );
        if ($otomoni_url) {
            $links[] = array(
                'url' => $otomoni_url,
                'label' => 'クラフトビール定期便を始める',
                'description' => '全国のブルワリーから毎月お届け',
                'service' => 'Otomoni'
            );
        }
        
        return $links;
    }
    
    /**
     * 旅行プランリンクを生成
     */
    private function generate_travel_links($location) {
        $links = array();
        $city = $location['name'];
        $is_international = isset($location['country']) && $location['country'] !== '日本';
        
        // 読売旅行
        $yomiuri_url = Beer_Affiliate_Programs::generate_a8_url(
            '読売旅行',
            "https://www.yomiuri-ryokou.co.jp/search/?keyword={$city}"
        );
        if ($yomiuri_url) {
            $links[] = array(
                'url' => $yomiuri_url,
                'label' => "読売旅行で{$city}ツアーを探す",
                'description' => 'お得なパックツアー',
                'service' => '読売旅行'
            );
        }
        
        if ($is_international) {
            // 海外の場合
            // カタール航空
            $qatar_url = Beer_Affiliate_Programs::generate_a8_url(
                'カタール航空',
                "https://www.qatarairways.com/ja-jp/destinations.html"
            );
            if ($qatar_url) {
                $links[] = array(
                    'url' => $qatar_url,
                    'label' => "カタール航空で{$city}へ",
                    'description' => '快適な空の旅',
                    'service' => 'カタール航空'
                );
            }
            
            // Saily eSIM
            $saily_url = Beer_Affiliate_Programs::generate_a8_url(
                'Saily',
                'https://saily.app/'
            );
            if ($saily_url) {
                $links[] = array(
                    'url' => $saily_url,
                    'label' => '海外で使えるeSIMを購入',
                    'description' => 'データ通信の心配なし',
                    'service' => 'Saily'
                );
            }
            
            // Oooh（ウー）
            $oooh_url = Beer_Affiliate_Programs::generate_a8_url(
                'Oooh(ウー)',
                "https://oooh.io/search?q={$city}+brewery+tour"
            );
            if ($oooh_url) {
                $links[] = array(
                    'url' => $oooh_url,
                    'label' => "{$city}のブルワリーツアー",
                    'description' => '現地のビール文化を体験',
                    'service' => 'Oooh'
                );
            }
            
            // 海外WiFi・eSIM関連
            $gigsky_url = Beer_Affiliate_Programs::generate_a8_url(
                'GigSky',
                'https://www.gigsky.com/'
            );
            if ($gigsky_url) {
                $links[] = array(
                    'url' => $gigsky_url,
                    'label' => 'GigSky海外eSIMで快適通信',
                    'description' => 'グローバルに繋がる安心のeSIM',
                    'service' => 'GigSky'
                );
            }
            
            $across_wifi_url = Beer_Affiliate_Programs::generate_a8_url(
                'アクロスWiFi',
                'https://www.across-wifi.jp/'
            );
            if ($across_wifi_url) {
                $links[] = array(
                    'url' => $across_wifi_url,
                    'label' => '海外WiFi無制限プラン',
                    'description' => '出張・旅行もストレスフリー',
                    'service' => 'アクロスWiFi'
                );
            }
            
            // 特定地域向けサービス
            if (strpos($city, 'ハワイ') !== false || strpos($city, 'ホノルル') !== false) {
                $airtri_hawaii_url = Beer_Affiliate_Programs::generate_a8_url(
                    'エアトリハワイ',
                    'https://www.airtri.co.jp/hawaii/'
                );
                if ($airtri_hawaii_url) {
                    $links[] = array(
                        'url' => $airtri_hawaii_url,
                        'label' => 'ハワイ旅行専門エアトリ',
                        'description' => 'ハワイ旅行のことならお任せ',
                        'service' => 'エアトリハワイ'
                    );
                }
            }
            
            if (strpos($city, 'トルコ') !== false || strpos($city, 'イスタンブール') !== false) {
                $tourqua_url = Beer_Affiliate_Programs::generate_a8_url(
                    'TOURQUA',
                    'https://www.tourqua.com/'
                );
                if ($tourqua_url) {
                    $links[] = array(
                        'url' => $tourqua_url,
                        'label' => 'トルコ専門ツアー',
                        'description' => '添乗員付きパッケージツアー',
                        'service' => 'TOURQUA'
                    );
                }
            }
        }
        
        return $links;
    }
    
    
    /**
     * 楽天設定を取得
     */
    private function get_rakuten_config() {
        $options = get_option('beer_affiliate_settings', array());
        return array(
            'affiliate_id' => isset($options['rakuten_affiliate_id']) ? $options['rakuten_affiliate_id'] : '',
            'application_id' => isset($options['rakuten_application_id']) ? $options['rakuten_application_id'] : ''
        );
    }
    
    /**
     * A8設定を取得
     */
    private function get_a8_config() {
        $options = get_option('beer_affiliate_settings', array());
        return array(
            'media_id' => isset($options['a8_media_id']) ? $options['a8_media_id'] : ''
        );
    }
    
    /**
     * 表示テンプレートを取得
     */
    public function get_display_template($links, $template_type = 'card') {
        return $this->display_manager->render($links, $template_type);
    }
    
    /**
     * このモジュールが適用可能かチェック
     */
    public function is_applicable($content) {
        // ビール関連キーワードをチェック
        $keywords = array('ビール', 'beer', 'ブルワリー', 'brewery', 'クラフトビール');
        foreach ($keywords as $keyword) {
            if (mb_stripos($content, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
}