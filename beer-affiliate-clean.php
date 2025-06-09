<?php
/**
 * Plugin Name: Beer Affiliate Engine
 * Plugin URI: https://rihobeer.com/
 * Description: クラフトビール記事の地域情報から旅行アフィリエイトリンクを自動生成
 * Version: 1.5.0
 * Author: RihoBeer
 * Author URI: https://rihobeer.com/
 * License: GPL v2 or later
 * Text Domain: beer-affiliate-engine
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// 定数定義
define('BAE_VERSION', '1.5.0');
define('BAE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BAE_PLUGIN_URL', plugin_dir_url(__FILE__));

// メインクラス
class BeerAffiliateEngine {
    
    private static $instance = null;
    
    public static function init() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // 基本的なフックのみ
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // 管理画面
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
        }
    }
    
    public function register_shortcodes() {
        add_shortcode('beer_affiliate', array($this, 'shortcode_handler'));
    }
    
    public function shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'city' => '',
            'template' => 'card'
        ), $atts);
        
        // シンプルな出力
        $output = '<div class="beer-affiliate-container">';
        $output .= '<p>Beer Affiliate Engine - ' . esc_html($atts['city']) . '</p>';
        $output .= '</div>';
        
        return $output;
    }
    
    public function enqueue_assets() {
        if (is_singular('post')) {
            wp_enqueue_style(
                'bae-styles',
                BAE_PLUGIN_URL . 'assets/css/main.css',
                array(),
                BAE_VERSION
            );
        }
    }
    
    public function add_admin_menu() {
        add_options_page(
            'Beer Affiliate設定',
            'Beer Affiliate',
            'manage_options',
            'beer-affiliate',
            array($this, 'settings_page')
        );
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Beer Affiliate Engine 設定</h1>
            <form method="post" action="options.php">
                <?php settings_fields('beer_affiliate_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">楽天アフィリエイトID</th>
                        <td>
                            <input type="text" name="bae_rakuten_id" value="<?php echo esc_attr(get_option('bae_rakuten_id')); ?>" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

// 有効化フック
register_activation_hook(__FILE__, function() {
    // 最小限の処理のみ
    update_option('bae_version', BAE_VERSION);
    flush_rewrite_rules();
});

// 無効化フック
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// プラグイン初期化
add_action('plugins_loaded', array('BeerAffiliateEngine', 'init'));