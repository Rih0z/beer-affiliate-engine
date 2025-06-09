<?php
/**
 * Plugin Name: Beer Affiliate Debug Step by Step
 * Description: 段階的デバッグテスト
 * Version: 1.4.3-debug
 */

// 直接アクセス禁止
if (!defined('ABSPATH')) {
    exit;
}

// エラーハンドラー設定
function beer_debug_error_handler($errno, $errstr, $errfile, $errline) {
    error_log("Beer Affiliate Debug - Error: $errstr in $errfile on line $errline");
    return false;
}
set_error_handler('beer_debug_error_handler');

// Step 1: 定数定義のテスト
error_log('Beer Affiliate Debug - Step 1: Constants');
define('BEER_AFFILIATE_VERSION', '1.4.3-debug');
define('BEER_AFFILIATE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BEER_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Step 2: 有効化フックのテスト
error_log('Beer Affiliate Debug - Step 2: Activation hook');
function beer_debug_activate() {
    error_log('Beer Affiliate Debug - Activation function called');
    add_option('beer_affiliate_debug_activated', true);
}
register_activation_hook(__FILE__, 'beer_debug_activate');

// Step 3: 初期化フックのテスト
error_log('Beer Affiliate Debug - Step 3: Init hook');
function beer_debug_init() {
    error_log('Beer Affiliate Debug - Init function called');
}
add_action('init', 'beer_debug_init');

// Step 4: クラスファイルの存在確認
error_log('Beer Affiliate Debug - Step 4: Check class files');
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
        error_log("Beer Affiliate Debug - File exists: $file");
    } else {
        error_log("Beer Affiliate Debug - File missing: $file");
    }
}

// Step 5: 各クラスを個別に読み込みテスト
function beer_debug_test_classes() {
    error_log('Beer Affiliate Debug - Step 5: Testing class loading');
    
    // Analytics クラスのテスト
    try {
        if (!class_exists('Beer_Affiliate_Analytics')) {
            $file = BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-analytics.php';
            if (file_exists($file)) {
                error_log('Beer Affiliate Debug - Loading Analytics class');
                require_once $file;
                error_log('Beer Affiliate Debug - Analytics class loaded successfully');
            }
        }
    } catch (Exception $e) {
        error_log('Beer Affiliate Debug - Analytics error: ' . $e->getMessage());
    } catch (ParseError $e) {
        error_log('Beer Affiliate Debug - Analytics parse error: ' . $e->getMessage());
    } catch (Error $e) {
        error_log('Beer Affiliate Debug - Analytics fatal error: ' . $e->getMessage());
    }
    
    // Data Store クラスのテスト
    try {
        if (!class_exists('Beer_Affiliate_Data_Store')) {
            $file = BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-data-store.php';
            if (file_exists($file)) {
                error_log('Beer Affiliate Debug - Loading Data Store class');
                require_once $file;
                error_log('Beer Affiliate Debug - Data Store class loaded successfully');
            }
        }
    } catch (Exception $e) {
        error_log('Beer Affiliate Debug - Data Store error: ' . $e->getMessage());
    } catch (ParseError $e) {
        error_log('Beer Affiliate Debug - Data Store parse error: ' . $e->getMessage());
    } catch (Error $e) {
        error_log('Beer Affiliate Debug - Data Store fatal error: ' . $e->getMessage());
    }
}

// plugins_loaded で実行
add_action('plugins_loaded', 'beer_debug_test_classes');

error_log('Beer Affiliate Debug - Main file loaded successfully');