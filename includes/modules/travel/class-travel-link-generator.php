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
        
        if (empty($city_name)) {
            return $links;
        }
        
        // 各サービスのリンクを生成
        foreach ($this->link_templates as $service => $template) {
            // URLテンプレートに地域名を適用
            $url = str_replace('{CITY}', urlencode($city_name), $template['url']);
            
            // 都道府県パラメータがある場合は適用
            if (!empty($prefecture) && isset($template['prefecture_params'])) {
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
            
            $links[$service] = array(
                'url' => $url,
                'label' => $label,
                'service' => $service,
                'image' => isset($template['image']) ? $template['image'] : '',
                'priority' => isset($template['priority']) ? intval($template['priority']) : 10
            );
        }
        
        // 優先度順にソート（高い順）
        uasort($links, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
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
        if (!empty($season) && isset($template['seasonal_params'][$season])) {
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
