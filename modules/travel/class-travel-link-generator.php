<?php
/**
 * 旅行リンクジェネレータクラス
 * 地域名に基づいてアフィリエイトリンクを生成
 */
class Travel_Link_Generator {
    /**
     * リンクテンプレート
     * 
     * @var array
     */
    private $link_templates;
    
    /**
     * データストア
     * 
     * @var Beer_Affiliate_Data_Store
     */
    private $data_store;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // データストアをロード
        $this->data_store = new Beer_Affiliate_Data_Store();
        
        // リンクテンプレートをロード
        $this->link_templates = $this->load_templates();
    }
    
    /**
     * リンクテンプレートをロード
     * 
     * @return array リンクテンプレート
     */
    private function load_templates() {
        // キャッシュからテンプレートを取得
        $templates = $this->data_store->get_cache('link_templates');
        
        if (false === $templates) {
            // キャッシュがない場合はJSONから読み込み
            $json_file = BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/link-templates.json';
            $templates = json_decode(file_get_contents($json_file), true);
            
            // キャッシュに保存（1日間）
            $this->data_store->set_cache('link_templates', $templates, DAY_IN_SECONDS);
        }
        
        return $templates;
    }
    
    /**
     * 地域情報に基づいてアフィリエイトリンクを生成
     * 
     * @param array $city 地域情報
     * @return array 生成されたリンクの配列
     */
    public function generate($city) {
        $links = array();
        $city_name = isset($city['name']) ? $city['name'] : '';
        $region = isset($city['region']) ? $city['region'] : '';
        $prefecture = isset($city['prefecture']) ? $city['prefecture'] : '';
        $country = isset($city['country']) ? $city['country'] : '日本';
        
        if (empty($city_name)) {
            return $links;
        }
        
        // 国内/海外でフィルタリング
        $is_international = ($region === '海外' || !empty($country) && $country !== '日本');
        
        // 各サービスのリンクを生成
        foreach ($this->link_templates as $service => $template) {
            // 国内/海外のフィルタリング
            if ($is_international && !isset($template['international_support'])) {
                // 海外対応していないサービスはスキップ
                continue;
            } else if (!$is_international && isset($template['international_only']) && $template['international_only']) {
                // 国内専用サービスはスキップ
                continue;
            }
            
            // 無効化されたサービスをスキップ
            if (isset($template['disabled']) && $template['disabled']) {
                continue;
            }
            
            // A8プログラムIDがある場合は適用
            if (isset($template['program_id'])) {
                $url = str_replace('{PROGRAM_ID}', $template['program_id'], $template['url']);
            } else {
                $url = $template['url'];
            }
            
            // URLテンプレートに地域名を適用
            $url = str_replace('{CITY}', urlencode($city_name), $url);
            
            // 都市コードがある場合は適用
            if (isset($template['city_codes']) && isset($template['city_codes'][$city_name])) {
                $city_code = $template['city_codes'][$city_name];
                $url = str_replace('{CITY_CODE}', $city_code, $url);
            }
            
            // 国コードがある場合は適用（海外用）
            if ($is_international && !empty($country) && isset($template['country_codes']) && isset($template['country_codes'][$country])) {
                $country_code = $template['country_codes'][$country];
                $url = str_replace('{COUNTRY_CODE}', $country_code, $url);
            }
            
            // 国名パラメータが必要な場合は適用（海外用）
            if ($is_international && !empty($country) && isset($template['country_params'])) {
                $country_param = isset($template['country_params'][$country]) 
                    ? $template['country_params'][$country] 
                    : '';
                
                if (!empty($country_param)) {
                    $url = str_replace('{COUNTRY}', $country_param, $url);
                }
            }
            
            // 都道府県パラメータがある場合は適用（国内用）
            if (!$is_international && !empty($prefecture) && isset($template['prefecture_params'])) {
                $prefecture_param = isset($template['prefecture_params'][$prefecture]) 
                    ? $template['prefecture_params'][$prefecture] 
                    : '';
                
                if (!empty($prefecture_param)) {
                    $url = str_replace('{PREFECTURE}', $prefecture_param, $url);
                }
            }
            
            // 地域パラメータがある場合は適用
            if (!empty($region) && isset($template['region_params'])) {
                $region_param = isset($template['region_params'][$region]) 
                    ? $template['region_params'][$region] 
                    : '';
                
                if (!empty($region_param)) {
                    $url = str_replace('{REGION}', $region_param, $url);
                }
            }
            
            // ラベルを生成
            $label = str_replace('{CITY}', $city_name, $template['label']);
            
            // 季節に応じたパラメータを適用（将来拡張用）
            $url = $this->apply_seasonal_params($url, $template);
            
            // アフィリエイトIDを適用
            $url = $this->apply_affiliate_id($url, $service, $template);
            
            // トラッキングパラメータを追加
            $url = $this->add_tracking_params($url, $city);
            
            // サービスのカテゴリーを設定（デフォルトは'travel'）
            $category = isset($template['category']) ? $template['category'] : 'travel';
            
            $links[$service] = array(
                'url' => $url,
                'label' => $label,
                'service' => $service,
                'image' => isset($template['image']) ? $template['image'] : '',
                'priority' => isset($template['priority']) ? intval($template['priority']) : 10,
                'category' => $category
            );
        }
        
        // 優先度順にソート（高い順）
        uasort($links, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        // カテゴリーでフィルタリング（現在はtravel固定）
        $category_filter = apply_filters('beer_affiliate_category_filter', 'travel');
        if ($category_filter !== 'all') {
            $links = array_filter($links, function($link) use ($category_filter) {
                return $link['category'] === $category_filter;
            });
        }
        
        return $links;
    }
    
    /**
     * 季節に応じたパラメータを適用
     * 
     * @param string $url URL
     * @param array $template テンプレート情報
     * @return string 更新されたURL
     */
    private function apply_seasonal_params($url, $template) {
        // 現在の月を取得
        $current_month = date('n');
        
        // 季節を判定
        $season = '';
        if ($current_month >= 3 && $current_month <= 5) {
            $season = 'spring';
        } elseif ($current_month >= 6 && $current_month <= 8) {
            $season = 'summer';
        } elseif ($current_month >= 9 && $current_month <= 11) {
            $season = 'autumn';
        } else {
            $season = 'winter';
        }
        
        // 季節パラメータがある場合は適用
        if (!empty($season) && isset($template['seasonal_params']) && isset($template['seasonal_params'][$season])) {
            $url .= $template['seasonal_params'][$season];
        }
        
        return $url;
    }
    
    /**
     * アフィリエイトIDを適用
     * 
     * @param string $url URL
     * @param string $service サービス名
     * @param array $template テンプレート情報
     * @return string 更新されたURL
     */
    private function apply_affiliate_id($url, $service, $template) {
        // テンプレートに含まれるアフィリエイトIDを適用
        if (isset($template['affiliate_id'])) {
            $url = str_replace('{AFFILIATE_ID}', $template['affiliate_id'], $url);
        }
        
        // カスタマイズ設定のアフィリエイトIDを優先適用
        $custom_id = get_option('beer_affiliate_' . sanitize_title($service) . '_id');
        if (!empty($custom_id)) {
            $url = str_replace('{AFFILIATE_ID}', $custom_id, $url);
        }
        
        return $url;
    }
    
    /**
     * トラッキングパラメータを追加
     * 
     * @param string $url URL
     * @param array $city 地域情報
     * @return string 更新されたURL
     */
    private function add_tracking_params($url, $city) {
        // UTMパラメータなどのトラッキング情報を追加
        $tracking_params = array(
            'utm_source' => 'beer_affiliate',
            'utm_medium' => 'plugin',
            'utm_campaign' => 'travel',
            'utm_content' => sanitize_title($city['name'])
        );
        
        // すでにUTMパラメータが含まれている場合はスキップ
        if (strpos($url, 'utm_source=') !== false) {
            return $url;
        }
        
        // URLにパラメータを追加
        $url_parts = parse_url($url);
        $query = array();
        
        if (isset($url_parts['query'])) {
            parse_str($url_parts['query'], $query);
        }
        
        // トラッキングパラメータをマージ
        $query = array_merge($query, $tracking_params);
        
        // URLを再構築
        $url_parts['query'] = http_build_query($query);
        
        // URLを生成
        return $this->build_url($url_parts);
    }
    
    /**
     * URLパーツからURLを構築
     * 
     * @param array $parts URLパーツ
     * @return string 構築されたURL
     */
    private function build_url($parts) {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $user = isset($parts['user']) ? $parts['user'] : '';
        $pass = isset($parts['pass']) ? ':' . $parts['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parts['path']) ? $parts['path'] : '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
