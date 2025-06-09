<?php
/**
 * Plugin Name: Beer Affiliate Engine
 * Plugin URI: https://rihobeer.com/plugins/beer-affiliate-engine
 * Description: クラフトビール記事の地域情報から旅行アフィリエイトリンクを自動生成するプラグイン
 * Version: 1.4.0
 * Author: RihoBeer
 * Author URI: https://rihobeer.com/
 * Text Domain: beer-affiliate-engine
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// 直接アクセス禁止
if (!defined('ABSPATH')) {
    exit;
}

// プラグイン定数を定義
define('BEER_AFFILIATE_VERSION', '1.4.0');
define('BEER_AFFILIATE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BEER_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));

// 有効化時の処理（最小限）
function beer_affiliate_activate() {
    // バージョン情報のみ保存
    add_option('beer_affiliate_version', BEER_AFFILIATE_VERSION);
    
    // デフォルト設定
    add_option('beer_affiliate_template', 'card');
    add_option('beer_affiliate_primary_module', 'travel');
    
    // 書き換えルールをフラッシュ
    flush_rewrite_rules();
}

// 無効化時の処理
function beer_affiliate_deactivate() {
    flush_rewrite_rules();
}

// 有効化・無効化フックを登録
register_activation_hook(__FILE__, 'beer_affiliate_activate');
register_deactivation_hook(__FILE__, 'beer_affiliate_deactivate');

// プラグインの初期化（WordPress読み込み後）
function beer_affiliate_init() {
    // 翻訳用テキストドメインをロード
    load_plugin_textdomain('beer-affiliate-engine', false, basename(dirname(__FILE__)) . '/languages');
    
    // 必要なファイルを安全に読み込み
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
    
    foreach ($required_files as $file) {
        $file_path = BEER_AFFILIATE_PLUGIN_DIR . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
    
    // コアクラスが読み込まれている場合のみ初期化
    if (class_exists('Beer_Affiliate_Core')) {
        $core = new Beer_Affiliate_Core();
        $core->init();
    }
    
    // 管理画面のみ設定画面を読み込み
    if (is_admin()) {
        $settings_file = BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-settings.php';
        if (file_exists($settings_file)) {
            require_once $settings_file;
        }
    }
}
add_action('plugins_loaded', 'beer_affiliate_init');

// ショートコードを登録（シンプル版）
function beer_affiliate_shortcode($atts) {
    // 属性を解析
    $args = shortcode_atts(array(
        'template' => get_option('beer_affiliate_template', 'card'),
        'max_links' => 2,
        'module' => get_option('beer_affiliate_primary_module', 'travel'),
        'show_hotels' => false,
        'hotel_count' => 3,
    ), $atts, 'beer_affiliate');
    
    // コアクラスが利用可能な場合のみ処理
    if (class_exists('Beer_Affiliate_Core')) {
        $core = new Beer_Affiliate_Core();
        $core->init();
        
        global $post;
        if ($post && $post->post_content) {
            return $core->process_content($post->post_content, $args);
        }
    }
    
    return '';
}
add_shortcode('beer_affiliate', 'beer_affiliate_shortcode');

// 宿泊施設表示用のショートコード
function beer_affiliate_hotels_shortcode($atts) {
    $args = shortcode_atts(array(
        'city' => '',
        'count' => 3,
        'sort' => 'price', // price, rating
    ), $atts, 'beer_affiliate_hotels');
    
    if (empty($args['city'])) {
        // 記事から都市名を自動検出
        global $post;
        if ($post && $post->post_content) {
            require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-content-analyzer.php';
            $analyzer = new Travel_Content_Analyzer();
            $cities = $analyzer->analyze($post->post_content);
            if (!empty($cities)) {
                $args['city'] = $cities[0]['name'];
            }
        }
    }
    
    if (empty($args['city'])) {
        return '';
    }
    
    // 宿泊施設情報を取得
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-link-generator.php';
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-api-client.php';
    
    $link_generator = new Travel_Link_Generator();
    $api_client = new Travel_API_Client();
    
    $options = array(
        'hits' => intval($args['count']),
        'sort' => $args['sort'] === 'rating' ? '-reviewAverage' : '+roomCharge'
    );
    
    $hotels = $api_client->search_hotels($args['city'], $options);
    
    if (empty($hotels)) {
        return '';
    }
    
    return $api_client->render_hotels($hotels);
}
add_shortcode('beer_affiliate_hotels', 'beer_affiliate_hotels_shortcode');

// CSSとJSを読み込み（最小限）
function beer_affiliate_enqueue_scripts() {
    if (is_singular('post')) {
        $css_file = BEER_AFFILIATE_PLUGIN_URL . 'assets/css/main.css';
        if (file_exists(BEER_AFFILIATE_PLUGIN_DIR . 'assets/css/main.css')) {
            wp_enqueue_style('beer-affiliate-styles', $css_file, array(), BEER_AFFILIATE_VERSION);
        }
    }
}
add_action('wp_enqueue_scripts', 'beer_affiliate_enqueue_scripts');

// プラグインページに設定リンクを追加
function beer_affiliate_settings_link($links) {
    $settings_link = '<a href="' . admin_url('customize.php?autofocus[section]=beer_affiliate_options') . '">' . __('設定', 'beer-affiliate-engine') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin_file = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin_file", 'beer_affiliate_settings_link');

// カスタマイザー設定を安全に読み込み
$customizer_file = BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-customizer.php';
if (file_exists($customizer_file)) {
    require_once $customizer_file;
}