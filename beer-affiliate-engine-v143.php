<?php
/**
 * Plugin Name: Beer Affiliate Engine
 * Plugin URI: https://rihobeer.com/plugins/beer-affiliate-engine
 * Description: クラフトビール記事の地域情報から旅行アフィリエイトリンクを自動生成するプラグイン
 * Version: 1.4.3
 * Author: RihoBeer
 * Author URI: https://rihobeer.com/
 * Text Domain: beer-affiliate-engine
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// エラーログ出力関数
if (!function_exists('beer_debug_log')) {
    function beer_debug_log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Beer Affiliate Engine] ' . $message);
        }
    }
}

beer_debug_log('Starting plugin load...');

// 直接アクセス禁止
if (!defined('ABSPATH')) {
    exit;
}

beer_debug_log('ABSPATH check passed');

// プラグイン定数を定義
define('BEER_AFFILIATE_VERSION', '1.4.3');
define('BEER_AFFILIATE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BEER_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));

beer_debug_log('Constants defined');

// 有効化時の処理（エラーハンドリング付き）
function beer_affiliate_activate() {
    try {
        beer_debug_log('Activation hook started');
        
        // バージョン情報のみ保存
        add_option('beer_affiliate_version', BEER_AFFILIATE_VERSION);
        
        // デフォルト設定
        add_option('beer_affiliate_template', 'card');
        add_option('beer_affiliate_primary_module', 'travel');
        
        // 書き換えルールをフラッシュ
        flush_rewrite_rules();
        
        beer_debug_log('Activation completed successfully');
    } catch (Exception $e) {
        beer_debug_log('Activation error: ' . $e->getMessage());
        wp_die('プラグインの有効化中にエラーが発生しました: ' . $e->getMessage());
    }
}

// 無効化時の処理
function beer_affiliate_deactivate() {
    beer_debug_log('Deactivation hook called');
    flush_rewrite_rules();
}

// 有効化・無効化フックを登録
register_activation_hook(__FILE__, 'beer_affiliate_activate');
register_deactivation_hook(__FILE__, 'beer_affiliate_deactivate');

beer_debug_log('Hooks registered');

// プラグインの初期化を遅延実行
add_action('plugins_loaded', function() {
    beer_debug_log('plugins_loaded hook fired');
    
    try {
        // 翻訳用テキストドメインをロード
        load_plugin_textdomain('beer-affiliate-engine', false, basename(dirname(__FILE__)) . '/languages');
        
        // 必要なファイルのリスト
        $required_files = array(
            'includes/class-data-store.php',
            'includes/class-analytics.php', 
            'includes/class-content-analyzer.php',
            'includes/class-link-generator.php',
            'includes/class-display-manager.php',
            'includes/interface-affiliate-module.php',
            'includes/class-base-affiliate-module.php',
            'includes/class-core.php'
        );
        
        // 各ファイルを個別に読み込み（エラーハンドリング付き）
        foreach ($required_files as $file) {
            $file_path = BEER_AFFILIATE_PLUGIN_DIR . $file;
            if (file_exists($file_path)) {
                beer_debug_log("Loading file: $file");
                try {
                    require_once $file_path;
                    beer_debug_log("Successfully loaded: $file");
                } catch (Exception $e) {
                    beer_debug_log("Error loading $file: " . $e->getMessage());
                    continue;
                } catch (ParseError $e) {
                    beer_debug_log("Parse error in $file: " . $e->getMessage());
                    continue;
                } catch (Error $e) {
                    beer_debug_log("Fatal error in $file: " . $e->getMessage());
                    continue;
                }
            } else {
                beer_debug_log("File not found: $file");
            }
        }
        
        // コアクラスの初期化
        if (class_exists('Beer_Affiliate_Core')) {
            beer_debug_log('Initializing Core class');
            try {
                $core = new Beer_Affiliate_Core();
                $core->init();
                beer_debug_log('Core initialized successfully');
            } catch (Exception $e) {
                beer_debug_log('Core initialization error: ' . $e->getMessage());
            }
        } else {
            beer_debug_log('Core class not found');
        }
        
        // 管理画面のみ設定画面を読み込み
        if (is_admin()) {
            $settings_file = BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-settings.php';
            if (file_exists($settings_file)) {
                beer_debug_log('Loading admin settings');
                try {
                    require_once $settings_file;
                } catch (Exception $e) {
                    beer_debug_log('Settings load error: ' . $e->getMessage());
                }
            }
        }
        
        beer_debug_log('Plugin initialization completed');
        
    } catch (Exception $e) {
        beer_debug_log('Critical error during initialization: ' . $e->getMessage());
    }
});

// ショートコードを安全に登録
add_action('init', function() {
    beer_debug_log('Registering shortcodes');
    
    // メインショートコード
    add_shortcode('beer_affiliate', 'beer_affiliate_shortcode');
    
    // ホテル表示ショートコード
    add_shortcode('beer_affiliate_hotels', 'beer_affiliate_hotels_shortcode');
});

// ショートコード処理関数
function beer_affiliate_shortcode($atts) {
    try {
        $args = shortcode_atts(array(
            'template' => get_option('beer_affiliate_template', 'card'),
            'max_links' => 2,
            'module' => get_option('beer_affiliate_primary_module', 'travel'),
            'show_hotels' => false,
            'hotel_count' => 3,
        ), $atts, 'beer_affiliate');
        
        if (class_exists('Beer_Affiliate_Core')) {
            $core = new Beer_Affiliate_Core();
            $core->init();
            
            global $post;
            if ($post && $post->post_content) {
                return $core->process_content($post->post_content, $args);
            }
        }
    } catch (Exception $e) {
        beer_debug_log('Shortcode error: ' . $e->getMessage());
    }
    
    return '';
}

// ホテル表示ショートコード
function beer_affiliate_hotels_shortcode($atts) {
    try {
        $args = shortcode_atts(array(
            'city' => '',
            'count' => 3,
            'sort' => 'price',
        ), $atts, 'beer_affiliate_hotels');
        
        if (empty($args['city'])) {
            global $post;
            if ($post && $post->post_content) {
                if (!class_exists('Travel_Content_Analyzer')) {
                    $analyzer_file = BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-content-analyzer.php';
                    if (file_exists($analyzer_file)) {
                        require_once $analyzer_file;
                    }
                }
                if (class_exists('Travel_Content_Analyzer')) {
                    $analyzer = new Travel_Content_Analyzer();
                    $cities = $analyzer->analyze($post->post_content);
                    if (!empty($cities)) {
                        $args['city'] = $cities[0]['name'];
                    }
                }
            }
        }
        
        if (empty($args['city'])) {
            return '';
        }
        
        // APIクライアントの読み込み
        $api_files = array(
            'modules/travel/class-travel-link-generator.php',
            'modules/travel/class-travel-api-client.php'
        );
        
        foreach ($api_files as $file) {
            $file_path = BEER_AFFILIATE_PLUGIN_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
        
        if (class_exists('Travel_API_Client')) {
            $api_client = new Travel_API_Client();
            $options = array(
                'hits' => intval($args['count']),
                'sort' => $args['sort'] === 'rating' ? '-reviewAverage' : '+roomCharge'
            );
            
            $hotels = $api_client->search_hotels($args['city'], $options);
            
            if (!empty($hotels)) {
                return $api_client->render_hotels($hotels);
            }
        }
    } catch (Exception $e) {
        beer_debug_log('Hotels shortcode error: ' . $e->getMessage());
    }
    
    return '';
}

// CSSとJSを安全に読み込み
add_action('wp_enqueue_scripts', function() {
    try {
        if (is_singular('post')) {
            $css_file = BEER_AFFILIATE_PLUGIN_URL . 'assets/css/main.css';
            if (file_exists(BEER_AFFILIATE_PLUGIN_DIR . 'assets/css/main.css')) {
                wp_enqueue_style('beer-affiliate-styles', $css_file, array(), BEER_AFFILIATE_VERSION);
            }
        }
    } catch (Exception $e) {
        beer_debug_log('Enqueue scripts error: ' . $e->getMessage());
    }
});

// プラグインページに設定リンクを追加
$plugin_file = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin_file", function($links) {
    try {
        $settings_link = '<a href="' . admin_url('customize.php?autofocus[section]=beer_affiliate_options') . '">' . __('設定', 'beer-affiliate-engine') . '</a>';
        array_unshift($links, $settings_link);
    } catch (Exception $e) {
        beer_debug_log('Settings link error: ' . $e->getMessage());
    }
    return $links;
});

// カスタマイザー設定を安全に読み込み  
add_action('customize_register', function() {
    $customizer_file = BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-customizer.php';
    if (file_exists($customizer_file)) {
        try {
            require_once $customizer_file;
        } catch (Exception $e) {
            beer_debug_log('Customizer load error: ' . $e->getMessage());
        }
    }
});

beer_debug_log('Plugin file fully loaded');