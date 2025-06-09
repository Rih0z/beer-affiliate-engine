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
     * デバッグ情報を出力
     * 
     * @param string $message メッセージ
     * @param mixed $data データ
     */
    private function debug_log($message, $data = null) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Beer Affiliate Travel] ' . $message);
            if ($data !== null) {
                error_log(print_r($data, true));
            }
        }
    }
    
    /**
     * リンクテンプレートをロード
     * 
     * @return array リンクテンプレート
     */
    private function load_templates() {
        // 開発時またはデバッグモードではキャッシュを使用しない
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $json_file = BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/link-templates.json';
            return json_decode(file_get_contents($json_file), true);
        }
        
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
     * テンプレートキャッシュをクリア
     */
    public function clear_template_cache() {
        $this->data_store->delete_cache('link_templates');
    }
    
    /**
     * A8.netプログラムの固定URLを取得
     * 
     * @param string $service サービス名
     * @param array $template テンプレート情報
     * @return string 固定URL
     */
    private function get_a8_fixed_url($service, $template) {
        // 1. 設定画面から取得を試みる
        $settings = get_option('beer_affiliate_settings', array());
        if (isset($settings['a8_fixed_urls']) && isset($settings['a8_fixed_urls'][$service])) {
            $url = $settings['a8_fixed_urls'][$service];
            if (!empty($url)) {
                return $url;
            }
        }
        
        // 2. テンプレートのfixed_urlから取得
        if (isset($template['fixed_url']) && !empty($template['fixed_url'])) {
            return $template['fixed_url'];
        }
        
        // 3. 見つからない場合は空文字を返す
        return '';
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
        
        $this->debug_log('Generating links for city', $city);
        
        if (empty($city_name)) {
            $this->debug_log('City name is empty, returning empty links');
            return $links;
        }
        
        // 国内/海外でフィルタリング
        $is_international = ($region === '海外' || !empty($country) && $country !== '日本');
        
        // 各サービスのリンクを生成
        foreach ($this->link_templates as $service => $template) {
            // A8.netのリダイレクトURLを一時保存
            $redirect_url = '';
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
            
            // A8.netの場合、設定画面またはテンプレートから固定URLを取得
            if (isset($template['type']) && $template['type'] === 'a8') {
                $fixed_url = $this->get_a8_fixed_url($service, $template);
                
                if (!empty($fixed_url)) {
                    // 固定URLを使用（都市名検索なし）
                    $url = $fixed_url;
                    
                    // ラベルからも{CITY}を除去
                    $label = isset($template['fixed_label']) && !empty($template['fixed_label']) 
                        ? $template['fixed_label'] 
                        : str_replace('{CITY}', '', $template['label']);
                    $label = trim(str_replace(array('の', 'で', 'を'), '', $label));
                    
                    $links[$service] = array(
                        'url' => $url,
                        'label' => $label,
                        'service' => $service,
                        'image' => isset($template['image']) ? $template['image'] : '',
                        'priority' => isset($template['priority']) ? intval($template['priority']) : 10,
                        'category' => isset($template['category']) ? $template['category'] : 'travel'
                    );
                    
                    $this->debug_log("Using fixed URL for A8 service $service: $url");
                    continue;
                }
            }
            
            // URLを初期化
            $url = $template['url'];
            
            // A8.netのリダイレクトURLの場合、リダイレクト先を抽出
            if (strpos($url, 'a8ejpredirect=') !== false) {
                // リダイレクトURL部分を抽出（デコードされている場合）
                if (preg_match('/a8ejpredirect=([^&]+)/', $url, $matches)) {
                    $redirect_url = urldecode($matches[1]);
                    // 一旦リダイレクトURLを除去
                    $url = str_replace('a8ejpredirect=' . $matches[1], 'a8ejpredirect=REDIRECT_URL_PLACEHOLDER', $url);
                }
            }
            
            // プログラムIDを取得（設定優先、なければテンプレートから）
            $program_id = $this->get_program_id($service, $template);
            
            // A8プログラムIDがある場合は適用
            if (isset($template['program_id']) || !empty($program_id)) {
                // プログラムIDが未設定の場合はスキップ
                if (empty($program_id)) {
                    $this->debug_log("Skipping $service - program ID not configured");
                    continue;
                }
                $url = str_replace('{PROGRAM_ID}', $program_id, $url);
            }
            
            // A8.netのメディアIDを適用（A8.netの場合）
            if (strpos($url, 'px.a8.net') !== false) {
                $media_id = get_option('beer_affiliate_a8_media_id', '3UJGPC');
                if (!empty($media_id)) {
                    // メディアIDから'a'プレフィックスを削除（もしあれば）
                    $media_id = ltrim($media_id, 'a');
                    $url = str_replace('{MEDIA_ID}', $media_id, $url);
                }
            }
            
            // リダイレクトURLがある場合は先に処理
            if (!empty($redirect_url)) {
                // リダイレクトURL内の変数を置換
                $redirect_url = str_replace('{CITY}', rawurlencode($city_name), $redirect_url);
            }
            
            $this->debug_log("Processing $service - initial URL: $url");
            
            // URLテンプレートに地域名を適用
            if (empty($redirect_url)) {
                // 通常のURLの場合
                $url = str_replace('{CITY}', rawurlencode($city_name), $url);
            }
            
            // 都市コードがある場合は適用
            if (isset($template['city_codes']) && isset($template['city_codes'][$city_name])) {
                $city_code = $template['city_codes'][$city_name];
                if (!empty($redirect_url)) {
                    $redirect_url = str_replace('{CITY_CODE}', $city_code, $redirect_url);
                } else {
                    $url = str_replace('{CITY_CODE}', $city_code, $url);
                }
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
                    if (!empty($redirect_url)) {
                        // リダイレクトURLにパラメータを追加
                        $redirect_url .= $prefecture_param;
                    } else {
                        // 通常のURLにパラメータを追加
                        $url .= $prefecture_param;
                    }
                    $this->debug_log("Added prefecture param for $service: $prefecture_param");
                }
            }
            
            // 地域パラメータがある場合は適用
            if (!empty($region) && isset($template['region_params'])) {
                $region_param = isset($template['region_params'][$region]) 
                    ? $template['region_params'][$region] 
                    : '';
                
                if (!empty($region_param)) {
                    if (!empty($redirect_url)) {
                        // リダイレクトURLにパラメータを追加
                        $redirect_url .= $region_param;
                    } else {
                        // 通常のURLにパラメータを追加
                        $url .= $region_param;
                    }
                    $this->debug_log("Added region param for $service: $region_param");
                }
            }
            
            // ラベルを生成
            $label = str_replace('{CITY}', $city_name, $template['label']);
            
            // 季節に応じたパラメータを適用（将来拡張用）
            $url = $this->apply_seasonal_params($url, $template);
            
            // アフィリエイトIDを適用
            $url = $this->apply_affiliate_id($url, $service, $template);
            
            // リダイレクトURLがある場合はトラッキングパラメータを追加
            if (!empty($redirect_url)) {
                // リダイレクトURLにパラメータを追加
                $redirect_url = $this->add_tracking_params($redirect_url, $city);
                // 適切にエンコードしてURLに戻す
                $encoded_redirect_url = rawurlencode($redirect_url);
                $url = str_replace('a8ejpredirect=REDIRECT_URL_PLACEHOLDER', 'a8ejpredirect=' . $encoded_redirect_url, $url);
            } else {
                // 通常のURLの場合
                $url = $this->add_tracking_params($url, $city);
            }
            
            // 未置換の変数がないかチェック
            $check_url = !empty($redirect_url) ? $redirect_url : $url;
            if (preg_match('/\{[A-Z_]+\}/', $check_url, $matches)) {
                $this->debug_log("Warning: Unreplaced variables in $service URL", array(
                    'url' => $check_url,
                    'unreplaced' => $matches
                ));
                // 必須変数が置換されていない場合はスキップ
                if (in_array($matches[0], array('{CITY_CODE}', '{COUNTRY_CODE}', '{AFFILIATE_ID}'))) {
                    $this->debug_log("Skipping $service - required variable not replaced: " . $matches[0]);
                    continue;
                }
            }
            
            $this->debug_log("Final URL for $service: $url");
            
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
     * プログラムIDを取得
     * 
     * @param string $service サービス名
     * @param array $template テンプレート情報
     * @return string プログラムID
     */
    private function get_program_id($service, $template) {
        // 1. wp-config.phpの定数から取得を試みる
        $constant_name = 'BEER_AFFILIATE_' . strtoupper(str_replace(array('-', ' ', '（', '）', '(', ')'), '_', $service)) . '_PROGRAM_ID';
        if (defined($constant_name)) {
            return constant($constant_name);
        }
        
        // 2. WordPressオプションから取得
        $option_name = 'beer_affiliate_' . sanitize_title($service) . '_program_id';
        $option_value = get_option($option_name);
        if (!empty($option_value)) {
            return $option_value;
        }
        
        // 3. テンプレートから取得
        if (isset($template['program_id']) && !empty($template['program_id'])) {
            return $template['program_id'];
        }
        
        return '';
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
        // アフィリエイトIDを取得（優先順位：定数 > オプション > テンプレート）
        $affiliate_id = '';
        
        // 1. wp-config.phpの定数から取得
        if ($service === '楽天トラベル' && defined('BEER_AFFILIATE_RAKUTEN_ID')) {
            $affiliate_id = BEER_AFFILIATE_RAKUTEN_ID;
        } elseif (defined('BEER_AFFILIATE_A8_ID')) {
            $affiliate_id = BEER_AFFILIATE_A8_ID;
        }
        
        // 2. WordPressオプションから取得
        if (empty($affiliate_id)) {
            // 楽天トラベルの場合は設定画面のオプションから取得
            if ($service === '楽天トラベル') {
                $settings = get_option('beer_affiliate_settings', array());
                $affiliate_id = isset($settings['rakuten_affiliate_id']) ? $settings['rakuten_affiliate_id'] : '';
            } else {
                $option_name = 'beer_affiliate_' . sanitize_title($service) . '_id';
                $option_value = get_option($option_name);
                if (!empty($option_value)) {
                    $affiliate_id = $option_value;
                }
            }
        }
        
        // 3. テンプレートから取得
        if (empty($affiliate_id) && isset($template['affiliate_id']) && !empty($template['affiliate_id'])) {
            $affiliate_id = $template['affiliate_id'];
        }
        
        // URLに適用
        if (!empty($affiliate_id)) {
            $url = str_replace('{AFFILIATE_ID}', $affiliate_id, $url);
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
