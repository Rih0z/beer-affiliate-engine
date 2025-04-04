<?php
/**
 * リンク生成クラス
 * アフィリエイトリンクを生成する基底クラス
 */
class Beer_Affiliate_Link_Generator {
    /**
     * リンクテンプレート
     * 
     * @var array
     */
    protected $link_templates = array();
    
    /**
     * データストア
     * 
     * @var Beer_Affiliate_Data_Store
     */
    protected $data_store;
    
    /**
     * キャッシュキープレフィックス
     * 
     * @var string
     */
    protected $cache_prefix = 'link_template_';
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // データストアをロード
        $this->data_store = new Beer_Affiliate_Data_Store();
    }
    
    /**
     * リンクテンプレートをロード
     * 
     * @param string $template_path テンプレートファイルのパス
     * @param string $cache_key キャッシュキー
     * @return array リンクテンプレート
     */
    protected function load_templates($template_path, $cache_key) {
        // キャッシュからテンプレートを取得
        $templates = $this->data_store->get_cache($cache_key);
        
        if (false === $templates) {
            // キャッシュがない場合はJSONから読み込み
            if (file_exists($template_path)) {
                $templates = json_decode(file_get_contents($template_path), true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('Beer Affiliate: JSON parse error in templates: ' . json_last_error_msg());
                    return array();
                }
                
                // カスタマイザー設定で上書き
                $templates = $this->apply_customizer_settings($templates);
                
                // キャッシュに保存（1日間）
                $this->data_store->set_cache($cache_key, $templates, DAY_IN_SECONDS);
            } else {
                error_log('Beer Affiliate: Template file not found: ' . $template_path);
                return array();
            }
        }
        
        return $templates;
    }
    
    /**
     * カスタマイザー設定を適用
     * 
     * @param array $templates テンプレート配列
     * @return array 設定適用後のテンプレート
     */
    protected function apply_customizer_settings($templates) {
        // 楽天トラベルID
        $rakuten_id = get_option('beer_affiliate_rakuten_travel_id');
        if (!empty($rakuten_id) && isset($templates['楽天トラベル'])) {
            $templates['楽天トラベル']['affiliate_id'] = $rakuten_id;
        }
        
        // JTB国内旅行ID
        $jtb_id = get_option('beer_affiliate_jtb_id');
        if (!empty($jtb_id) && isset($templates['JTB国内旅行'])) {
            $templates['JTB国内旅行']['program_id'] = $jtb_id;
        }
        
        // HISアフィリエイトID
        $his_id = get_option('beer_affiliate_his_id');
        if (!empty($his_id) && isset($templates['HIS'])) {
            $templates['HIS']['program_id'] = $his_id;
        }
        
        // 国際対応設定
        $enable_international = get_option('beer_affiliate_enable_international', true);
        if (!$enable_international) {
            // 国際対応を無効化
            foreach ($templates as $key => $template) {
                if (isset($template['international_only']) && $template['international_only']) {
                    $templates[$key]['disabled'] = true;
                }
            }
        }
        
        return $templates;
    }
    
    /**
     * 地域情報からアフィリエイトリンクを生成
     * 
     * @param array $keyword キーワード情報
     * @return array 生成されたリンク
     */
    public function generate($keyword) {
        // 基本実装は空の配列を返す
        // 子クラスで実際のリンク生成ロジックを実装
        return array();
    }
    
    /**
     * テンプレートを国内/国際でフィルタリング
     * 
     * @param array $templates テンプレート配列
     * @param boolean $is_international 国際対応フラグ
     * @return array フィルタリングされたテンプレート
     */
    protected function filter_templates_by_region($templates, $is_international) {
        $filtered = array();
        
        foreach ($templates as $service => $template) {
            // 無効化されたサービスをスキップ
            if (isset($template['disabled']) && $template['disabled']) {
                continue;
            }
            
            // 国内/海外のフィルタリング
            if ($is_international && !isset($template['international_support'])) {
                // 海外対応していないサービスはスキップ
                continue;
            } else if (!$is_international && isset($template['international_only']) && $template['international_only']) {
                // 海外専用サービスはスキップ
                continue;
            }
            
            $filtered[$service] = $template;
        }
        
        return $filtered;
    }
    
    /**
     * テンプレートをカテゴリーでフィルタリング
     * 
     * @param array $templates テンプレート配列
     * @param string $category カテゴリー
     * @return array フィルタリングされたテンプレート
     */
    protected function filter_templates_by_category($templates, $category = 'travel') {
        if ($category === 'all') {
            return $templates;
        }
        
        $filtered = array();
        
        foreach ($templates as $service => $template) {
            $template_category = isset($template['category']) ? $template['category'] : 'travel';
            
            if ($template_category === $category) {
                $filtered[$service] = $template;
            }
        }
        
        return $filtered;
    }
    
    /**
     * アフィリエイトIDを適用
     * 
     * @param string $url URL
     * @param string $service サービス名
     * @param array $template テンプレート情報
     * @return string 更新されたURL
     */
    protected function apply_affiliate_id($url, $service, $template) {
        // テンプレートに含まれるアフィリエイトIDを適用
        if (isset($template['affiliate_id'])) {
            $url = str_replace('{AFFILIATE_ID}', $template['affiliate_id'], $url);
        }
        
        // カスタマイズ設定のアフィリエイトIDを優先適用
        $option_key = 'beer_affiliate_' . sanitize_title($service) . '_id';
        $custom_id = get_option($option_key);
        if (!empty($custom_id)) {
            $url = str_replace('{AFFILIATE_ID}', $custom_id, $url);
        }
        
        return $url;
    }
    
    /**
     * トラッキングパラメータを追加
     * 
     * @param string $url URL
     * @param array $params トラッキングパラメータ
     * @return string 更新されたURL
     */
    protected function add_tracking_params($url, $params = array()) {
        // UTMパラメータなどのトラッキング情報を追加
        $default_params = array(
            'utm_source' => 'beer_affiliate',
            'utm_medium' => 'plugin',
            'utm_campaign' => 'affiliate'
        );
        
        // パラメータをマージ
        $tracking_params = array_merge($default_params, $params);
        
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
    protected function build_url($parts) {
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
