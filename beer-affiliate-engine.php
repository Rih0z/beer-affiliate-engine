<?php
/**
 * Plugin Name: Beer Affiliate Engine
 * Plugin URI: https://rihobeer.com/plugins/beer-affiliate-engine
 * Description: クラフトビール記事の地域情報から旅行アフィリエイトリンクを自動生成するプラグイン
 * Version: 1.0.0
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
define('BEER_AFFILIATE_VERSION', '1.0.0');
define('BEER_AFFILIATE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BEER_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));

// コアファイルを読み込み
require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-core.php';
require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-content-analyzer.php';
require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-link-generator.php';
require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-display-manager.php';
require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-data-store.php';

// モジュールインターフェースと基本クラスを読み込み
require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/interface-affiliate-module.php';
require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-base-affiliate-module.php';

// プラグインを初期化
function beer_affiliate_init() {
    // 翻訳用テキストドメインをロード
    load_plugin_textdomain('beer-affiliate-engine', false, basename(dirname(__FILE__)) . '/languages');
    
    // コアを初期化
    $core = new Beer_Affiliate_Core();
    $core->init();
}
add_action('plugins_loaded', 'beer_affiliate_init');

// 有効化・無効化フックを登録
register_activation_hook(__FILE__, 'beer_affiliate_activate');
register_deactivation_hook(__FILE__, 'beer_affiliate_deactivate');

// 有効化時の処理
function beer_affiliate_activate() {
    // 必要なデータベーステーブルやオプションを作成
    add_option('beer_affiliate_version', BEER_AFFILIATE_VERSION);
    
    // デフォルト設定
    if (!get_option('beer_affiliate_template')) {
        add_option('beer_affiliate_template', 'card');
    }
    
    if (!get_option('beer_affiliate_primary_module')) {
        add_option('beer_affiliate_primary_module', 'travel');
    }
    
    // 書き換えルールをフラッシュ
    flush_rewrite_rules();
}

// 無効化時の処理
function beer_affiliate_deactivate() {
    // 必要なクリーンアップ
    flush_rewrite_rules();
}

// ショートコードを登録
function beer_affiliate_shortcode($atts) {
    // 属性を解析
    $args = shortcode_atts(array(
        'template' => get_option('beer_affiliate_template', 'card'),
        'max_links' => 2,
        'module' => get_option('beer_affiliate_primary_module', 'travel'),
    ), $atts, 'beer_affiliate');
    
    // コアを初期化
    $core = new Beer_Affiliate_Core();
    
    // コンテンツを処理して出力を返す
    global $post;
    return $core->process_content($post->post_content, $args);
}
add_shortcode('beer_affiliate', 'beer_affiliate_shortcode');

// 記事末尾に自動挿入（設定で有効な場合）
function beer_affiliate_auto_insert($content) {
    // 投稿ページのみに適用
    if (is_singular('post') && get_option('beer_affiliate_auto_insert', true)) {
        $content .= do_shortcode('[beer_affiliate]');
    }
    return $content;
}
add_filter('the_content', 'beer_affiliate_auto_insert');

// 必要なスクリプトとスタイルを読み込み
function beer_affiliate_enqueue_scripts() {
    if (is_singular('post')) {
        wp_enqueue_style('beer-affiliate-styles', BEER_AFFILIATE_PLUGIN_URL . 'assets/css/main.css', array(), BEER_AFFILIATE_VERSION);
        
        // テンプレートに応じてJavaScriptを条件付きで読み込み
        $template = get_option('beer_affiliate_template', 'card');
        if ($template === 'sticky') {
            wp_enqueue_script('beer-affiliate-sticky', BEER_AFFILIATE_PLUGIN_URL . 'assets/js/sticky.js', array('jquery'), BEER_AFFILIATE_VERSION, true);
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

// カスタマイザー設定を登録
require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-customizer.php';
