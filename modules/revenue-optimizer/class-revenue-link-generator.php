<?php
/**
 * 収益最適化リンクジェネレータ
 */
class Revenue_Link_Generator {
    
    /**
     * プログラム設定
     */
    private $programs = array(
        'トラベル・スタンダード・ジャパン' => array(
            'url' => 'https://px.a8.net/svt/ejp?a8mat=24-0730&a8ejpredirect=https://www.travel-standard.com/search/?destination={DESTINATION}&utm_source=beerblog&utm_medium=affiliate',
            'label' => '🌟 オーダーメイドでビール旅を計画する（最大5000円還元）',
            'category' => 'travel'
        ),
        '読売旅行' => array(
            'url' => 'https://px.a8.net/svt/ejp?a8mat=25-0129&a8ejpredirect=https://www.yomiuri-ryokou.co.jp/search/?keyword={KEYWORD}&utm_source=beerblog',
            'label' => '📍 {KEYWORD}のビールツアーを探す',
            'category' => 'travel'
        ),
        'JTB国内旅行' => array(
            'url' => 'https://px.a8.net/svt/ejp?a8mat=07-0223&a8ejpredirect=https://www.jtb.co.jp/kokunai/hotel/list/{CITY_CODE}/?utm_source=beerblog',
            'label' => '🏨 {CITY}のホテルを予約（JTB）',
            'category' => 'travel'
        ),
        '楽天トラベル' => array(
            'url' => 'https://travel.rakuten.co.jp/keyword/search/?keyword={KEYWORD}&f_cqg=4&utm_source=beerblog',
            'label' => '🔍 {KEYWORD}周辺のホテルを探す',
            'category' => 'travel'
        ),
        '一休.comレストラン' => array(
            'url' => 'https://px.a8.net/svt/ejp?a8mat=11-0412&a8ejpredirect=https://restaurant.ikyu.com/search/?keyword={KEYWORD}+ビール&utm_source=beerblog',
            'label' => '🍽️ {KEYWORD}のビアレストランを予約',
            'category' => 'experience'
        ),
        'Oooh(ウー)' => array(
            'url' => 'https://px.a8.net/svt/ejp?a8mat=24-1129&a8ejpredirect=https://oooh.io/search?q={KEYWORD}+brewery+tour&utm_source=beerblog',
            'label' => '🏭 現地のブルワリーツアーを探す',
            'category' => 'experience'
        ),
        'Otomoni' => array(
            'url' => 'https://px.a8.net/svt/ejp?a8mat=22-0209&a8ejpredirect=https://otomoni.jp/?utm_source=beerblog&utm_medium=affiliate',
            'label' => '🍺 全国のクラフトビール定期便を申し込む（初回2000円還元）',
            'category' => 'shopping'
        ),
        'JTBショッピング' => array(
            'url' => 'https://px.a8.net/svt/ejp?a8mat=18-0525&a8ejpredirect=https://shopping.jtb.co.jp/search/?q={KEYWORD}+地ビール&utm_source=beerblog',
            'label' => '🎁 {KEYWORD}の地ビールをお取り寄せ',
            'category' => 'shopping'
        ),
        'Saily' => array(
            'url' => 'https://px.a8.net/svt/ejp?a8mat=24-0806&a8ejpredirect=https://saily.app/destinations/{COUNTRY_CODE}?utm_source=beerblog',
            'label' => '📱 海外ビール旅用のeSIMを購入（10%還元）',
            'category' => 'utility'
        ),
        'カタール航空' => array(
            'url' => 'https://px.a8.net/svt/ejp?a8mat=24-1022&a8ejpredirect=https://www.qatarairways.com/ja-jp/destinations.html?utm_source=beerblog',
            'label' => '✈️ 海外のビール産地への航空券を探す',
            'category' => 'utility'
        )
    );
    
    /**
     * リンクを生成
     */
    public function generate_link($program_name, $location = null) {
        if (!isset($this->programs[$program_name])) {
            return null;
        }
        
        $program = $this->programs[$program_name];
        $url = $program['url'];
        $label = $program['label'];
        
        // 地域情報がある場合は置換
        if ($location) {
            $city_name = isset($location['name']) ? $location['name'] : '';
            $keyword = $city_name . ' ビール';
            
            $url = str_replace('{DESTINATION}', rawurlencode($city_name), $url);
            $url = str_replace('{KEYWORD}', rawurlencode($keyword), $url);
            $url = str_replace('{CITY}', rawurlencode($city_name), $url);
            
            $label = str_replace('{CITY}', $city_name, $label);
            $label = str_replace('{KEYWORD}', $city_name, $label);
            
            // 都市コード（必要な場合）
            if (strpos($url, '{CITY_CODE}') !== false) {
                $city_code = $this->get_city_code($city_name);
                $url = str_replace('{CITY_CODE}', $city_code, $url);
            }
            
            // 国コード（海外の場合）
            if (strpos($url, '{COUNTRY_CODE}') !== false && isset($location['country'])) {
                $country_code = $this->get_country_code($location['country']);
                $url = str_replace('{COUNTRY_CODE}', $country_code, $url);
            }
        } else {
            // 地域情報がない場合はデフォルト値
            $url = str_replace('{DESTINATION}', rawurlencode('ビール旅行'), $url);
            $url = str_replace('{KEYWORD}', rawurlencode('クラフトビール'), $url);
            $url = str_replace('{CITY}', rawurlencode('全国'), $url);
            $label = str_replace('{CITY}', '全国', $label);
            $label = str_replace('{KEYWORD}', 'クラフトビール', $label);
        }
        
        // アフィリエイトIDを適用
        $url = $this->apply_affiliate_ids($url, $program_name);
        
        return array(
            'url' => $url,
            'title' => $program_name,
            'description' => $label,
            'button_text' => '詳細を見る',
            'service' => $program_name,
            'category' => $program['category']
        );
    }
    
    /**
     * アフィリエイトIDを適用
     */
    private function apply_affiliate_ids($url, $program_name) {
        // 設定から読み込み（実際の実装では）
        $affiliate_config = $this->load_affiliate_config();
        
        if ($program_name === '楽天トラベル' && isset($affiliate_config['楽天トラベル'])) {
            $url = str_replace('{AFFILIATE_ID}', $affiliate_config['楽天トラベル']['affiliate_id'], $url);
        }
        
        // A8.netのプログラムID
        $program_ids = array(
            'トラベル・スタンダード・ジャパン' => '24-0730',
            '読売旅行' => '25-0129',
            'JTB国内旅行' => '07-0223',
            '一休.comレストラン' => '11-0412',
            'Oooh(ウー)' => '24-1129',
            'Otomoni' => '22-0209',
            'JTBショッピング' => '18-0525',
            'Saily' => '24-0806',
            'カタール航空' => '24-1022'
        );
        
        if (isset($program_ids[$program_name])) {
            $url = str_replace(array('24-0730', '25-0129', '07-0223', '11-0412', '24-1129', '22-0209', '18-0525', '24-0806', '24-1022'), 
                               $program_ids[$program_name], $url);
        }
        
        return $url;
    }
    
    /**
     * 都市コードを取得
     */
    private function get_city_code($city_name) {
        $city_codes = array(
            '東京' => 'tokyo',
            '大阪' => 'osaka',
            '京都' => 'kyoto',
            '札幌' => 'sapporo',
            '福岡' => 'fukuoka',
            '横浜' => 'yokohama',
            '名古屋' => 'nagoya'
        );
        
        return isset($city_codes[$city_name]) ? $city_codes[$city_name] : 'japan';
    }
    
    /**
     * 国コードを取得
     */
    private function get_country_code($country) {
        $country_codes = array(
            'アメリカ' => 'usa',
            'ドイツ' => 'germany',
            'ベルギー' => 'belgium',
            'チェコ' => 'czech',
            'イギリス' => 'uk'
        );
        
        return isset($country_codes[$country]) ? $country_codes[$country] : 'world';
    }
    
    /**
     * アフィリエイト設定を読み込み
     */
    private function load_affiliate_config() {
        // 実際の実装では設定ファイルから読み込み
        return array(
            '楽天トラベル' => array(
                'affiliate_id' => '20a2fc9d.5c6c02f2.20a2fc9e.541a36d0',
                'application_id' => '1013646616942500290'
            )
        );
    }
}