<?php
/**
 * Plugin Name: Beer Affiliate Engine (Debug)
 * Plugin URI: https://rihobeer.com/plugins/beer-affiliate-engine
 * Description: デバッグ版 - エラーの詳細を表示
 * Version: 1.0.0-debug
 * Author: RihoBeer
 * Text Domain: beer-affiliate-engine
 */

// エラーハンドラーを設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// デバッグ用ログ関数
function beer_debug_log($message) {
    error_log('[Beer Affiliate Debug] ' . $message);
}

// 直接アクセス禁止
if (!defined('ABSPATH')) {
    beer_debug_log('Direct access attempted');
    exit;
}

try {
    beer_debug_log('Starting plugin initialization');
    
    // プラグイン定数を定義
    define('BEER_AFFILIATE_VERSION', '1.0.0');
    define('BEER_AFFILIATE_PLUGIN_DIR', plugin_dir_path(__FILE__));
    define('BEER_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));
    
    beer_debug_log('Constants defined');
    
    // 必要なファイルの存在をチェック
    $required_files = array(
        'includes/interface-affiliate-module.php',
        'includes/class-base-affiliate-module.php',
        'includes/class-data-store.php',
        'includes/class-core.php',
        'includes/class-content-analyzer.php',
        'includes/class-link-generator.php',
        'includes/class-display-manager.php',
        'includes/class-analytics.php'
    );
    
    foreach ($required_files as $file) {
        $full_path = BEER_AFFILIATE_PLUGIN_DIR . $file;
        if (!file_exists($full_path)) {
            beer_debug_log("Missing required file: $file");
            wp_die("Beer Affiliate Engine: Missing required file: $file");
        }
        beer_debug_log("Found file: $file");
    }
    
    // コアファイルを読み込み（順序重要）
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-data-store.php';
    beer_debug_log('Loaded data store');
    
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/interface-affiliate-module.php';
    beer_debug_log('Loaded interface');
    
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-base-affiliate-module.php';
    beer_debug_log('Loaded base module');
    
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-content-analyzer.php';
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-link-generator.php';
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-display-manager.php';
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-analytics.php';
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-core.php';
    
    beer_debug_log('All core files loaded');
    
    // プラグインを初期化
    function beer_affiliate_init_debug() {
        beer_debug_log('Init function called');
        
        try {
            // 翻訳用テキストドメインをロード
            load_plugin_textdomain('beer-affiliate-engine', false, basename(dirname(__FILE__)) . '/languages');
            
            // コアを初期化
            $core = new Beer_Affiliate_Core();
            $core->init();
            
            beer_debug_log('Core initialized successfully');
            
        } catch (Exception $e) {
            beer_debug_log('Error in init: ' . $e->getMessage());
            wp_die('Beer Affiliate Engine initialization error: ' . $e->getMessage());
        }
    }
    
    // initフックを登録
    add_action('plugins_loaded', 'beer_affiliate_init_debug');
    beer_debug_log('Init hook registered');
    
    // 有効化フック
    function beer_affiliate_activate_debug() {
        beer_debug_log('Activation hook called');
        
        try {
            // 必要なテーブルを作成
            if (class_exists('Beer_Affiliate_Analytics')) {
                Beer_Affiliate_Analytics::create_tables();
                beer_debug_log('Analytics tables created');
            }
            
            // デフォルト設定
            add_option('beer_affiliate_version', BEER_AFFILIATE_VERSION);
            add_option('beer_affiliate_template', 'card');
            add_option('beer_affiliate_primary_module', 'travel');
            
            flush_rewrite_rules();
            beer_debug_log('Activation completed');
            
        } catch (Exception $e) {
            beer_debug_log('Activation error: ' . $e->getMessage());
            wp_die('Beer Affiliate Engine activation error: ' . $e->getMessage());
        }
    }
    
    register_activation_hook(__FILE__, 'beer_affiliate_activate_debug');
    
    beer_debug_log('Plugin file loaded successfully');
    
} catch (Exception $e) {
    beer_debug_log('Fatal error: ' . $e->getMessage());
    wp_die('Beer Affiliate Engine fatal error: ' . $e->getMessage());
}

// デバッグ情報を管理画面に表示
add_action('admin_notices', function() {
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-info">';
        echo '<p><strong>Beer Affiliate Engine Debug Mode:</strong> Check error log for details.</p>';
        echo '</div>';
    }
});