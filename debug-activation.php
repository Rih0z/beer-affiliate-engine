<?php
/**
 * Plugin Name: Beer Affiliate Engine (Debug Activation)
 * Plugin URI: https://rihobeer.com/plugins/beer-affiliate-engine
 * Description: デバッグ版 - アクティベーション時のエラーを詳細確認
 * Version: 1.3.0-debug-activation
 * Author: RihoBeer
 * Text Domain: beer-affiliate-engine
 */

// エラー表示を有効化
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// 直接アクセス禁止
if (!defined('ABSPATH')) {
    exit;
}

// デバッグログ関数
function beer_debug_activation_log($message, $data = null) {
    $log_message = '[Beer Affiliate Debug] ' . $message;
    if ($data !== null) {
        $log_message .= ' | Data: ' . print_r($data, true);
    }
    error_log($log_message);
    
    // 管理画面にも表示
    if (is_admin()) {
        add_action('admin_notices', function() use ($log_message) {
            echo '<div class="notice notice-info"><p><strong>Debug:</strong> ' . esc_html($log_message) . '</p></div>';
        });
    }
}

try {
    beer_debug_activation_log('Starting plugin initialization');
    
    // プラグイン定数を定義
    if (!defined('BEER_AFFILIATE_VERSION')) {
        define('BEER_AFFILIATE_VERSION', '1.3.0-debug');
        beer_debug_activation_log('Version constant defined');
    }
    
    if (!defined('BEER_AFFILIATE_PLUGIN_DIR')) {
        define('BEER_AFFILIATE_PLUGIN_DIR', plugin_dir_path(__FILE__));
        beer_debug_activation_log('Plugin dir constant defined', BEER_AFFILIATE_PLUGIN_DIR);
    }
    
    if (!defined('BEER_AFFILIATE_PLUGIN_URL')) {
        define('BEER_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));
        beer_debug_activation_log('Plugin URL constant defined');
    }
    
    // 必要なファイルの存在チェック
    $required_files = array(
        'includes/class-data-store.php',
        'includes/interface-affiliate-module.php',
        'includes/class-base-affiliate-module.php',
        'includes/class-content-analyzer.php',
        'includes/class-core.php'
    );
    
    foreach ($required_files as $file) {
        $file_path = BEER_AFFILIATE_PLUGIN_DIR . $file;
        if (!file_exists($file_path)) {
            throw new Exception("Required file missing: {$file}");
        }
        beer_debug_activation_log("File exists: {$file}");
    }
    
    // コアファイルを段階的に読み込み
    beer_debug_activation_log('Loading data store...');
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-data-store.php';
    
    beer_debug_activation_log('Loading interface...');
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/interface-affiliate-module.php';
    
    beer_debug_activation_log('Loading base module...');
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-base-affiliate-module.php';
    
    beer_debug_activation_log('Loading content analyzer...');
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-content-analyzer.php';
    
    // プラグイン初期化関数
    function beer_affiliate_debug_init() {
        beer_debug_activation_log('Init function called');
        
        try {
            // 翻訳用テキストドメインをロード
            load_plugin_textdomain('beer-affiliate-engine', false, basename(dirname(__FILE__)) . '/languages');
            beer_debug_activation_log('Text domain loaded');
            
            // コアクラスの存在確認
            if (!file_exists(BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-core.php')) {
                throw new Exception('Core class file not found');
            }
            
            beer_debug_activation_log('Loading core class...');
            require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-core.php';
            
            // クラスの存在確認
            if (!class_exists('Beer_Affiliate_Core')) {
                throw new Exception('Beer_Affiliate_Core class not found after loading');
            }
            
            beer_debug_activation_log('Creating core instance...');
            $core = new Beer_Affiliate_Core();
            
            beer_debug_activation_log('Initializing core...');
            $core->init();
            
            beer_debug_activation_log('Core initialized successfully');
            
            // 設定画面の初期化（管理画面のみ）
            if (is_admin()) {
                if (file_exists(BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-settings.php')) {
                    beer_debug_activation_log('Loading settings class...');
                    require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-settings.php';
                    beer_debug_activation_log('Settings loaded successfully');
                } else {
                    beer_debug_activation_log('Settings file not found, skipping');
                }
            }
            
        } catch (Exception $e) {
            beer_debug_activation_log('Error in init: ' . $e->getMessage());
            throw $e;
        }
    }
    
    // initフックを登録
    add_action('plugins_loaded', 'beer_affiliate_debug_init');
    beer_debug_activation_log('Init hook registered');
    
    // 有効化フック
    function beer_affiliate_debug_activate() {
        beer_debug_activation_log('Activation hook called');
        
        try {
            // データベーステーブル作成のチェック
            if (file_exists(BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-analytics.php')) {
                require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-analytics.php';
                if (class_exists('Beer_Affiliate_Analytics')) {
                    Beer_Affiliate_Analytics::create_tables();
                    beer_debug_activation_log('Analytics tables created');
                }
            }
            
            // デフォルト設定
            add_option('beer_affiliate_version', BEER_AFFILIATE_VERSION);
            add_option('beer_affiliate_template', 'user-friendly');
            add_option('beer_affiliate_primary_module', 'travel_v2');
            
            beer_debug_activation_log('Default options set');
            
            flush_rewrite_rules();
            beer_debug_activation_log('Rewrite rules flushed');
            
            beer_debug_activation_log('Activation completed successfully');
            
        } catch (Exception $e) {
            beer_debug_activation_log('Activation error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    register_activation_hook(__FILE__, 'beer_affiliate_debug_activate');
    beer_debug_activation_log('Activation hook registered');
    
    // ショートコード登録
    function beer_affiliate_debug_shortcode($atts) {
        beer_debug_activation_log('Shortcode called', $atts);
        return '<div class="beer-affiliate-debug">Debug mode - shortcode working</div>';
    }
    add_shortcode('beer_affiliate', 'beer_affiliate_debug_shortcode');
    
    beer_debug_activation_log('Plugin file loaded successfully');
    
} catch (Exception $e) {
    beer_debug_activation_log('Fatal error during plugin load: ' . $e->getMessage());
    
    // 管理画面でのエラー表示
    add_action('admin_notices', function() use ($e) {
        echo '<div class="notice notice-error"><p><strong>Beer Affiliate Engine Error:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
    });
    
    // プラグインの無効化
    if (function_exists('deactivate_plugins')) {
        deactivate_plugins(plugin_basename(__FILE__));
    }
    
    return;
}

// デバッグ情報を管理画面に表示
add_action('admin_notices', function() {
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-success">';
        echo '<p><strong>Beer Affiliate Engine Debug Mode Active</strong></p>';
        echo '<p>Check your error log for detailed debug information.</p>';
        echo '<p>Plugin Directory: ' . esc_html(BEER_AFFILIATE_PLUGIN_DIR) . '</p>';
        echo '</div>';
    }
});