<?php
/**
 * Plugin Name: Beer Affiliate Engine (Safe Mode)
 * Plugin URI: https://rihobeer.com/plugins/beer-affiliate-engine
 * Description: セーフモード版 - 最小限の機能で安全に動作
 * Version: 1.3.0-safe
 * Author: RihoBeer
 * Text Domain: beer-affiliate-engine
 */

// 直接アクセス禁止
if (!defined('ABSPATH')) {
    exit;
}

// プラグイン定数を定義
define('BEER_AFFILIATE_VERSION', '1.3.0-safe');
define('BEER_AFFILIATE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BEER_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));

// セーフモード用の簡単なクラス
class Beer_Affiliate_Safe_Core {
    
    public function init() {
        // ショートコードのみ登録
        add_shortcode('beer_affiliate', array($this, 'render_shortcode'));
        
        // 管理画面のみで設定を読み込み
        if (is_admin() && file_exists(BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-settings.php')) {
            require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-settings.php';
        }
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'city' => '',
            'template' => 'user-friendly'
        ), $atts);
        
        if (empty($atts['city'])) {
            return '';
        }
        
        return $this->generate_simple_links($atts['city']);
    }
    
    private function generate_simple_links($city) {
        $options = get_option('beer_affiliate_settings', array());
        $rakuten_id = isset($options['rakuten_affiliate_id']) ? $options['rakuten_affiliate_id'] : '';
        $a8_media_id = isset($options['a8_media_id']) ? $options['a8_media_id'] : '';
        
        ob_start();
        ?>
        <div class="beer-affiliate-container user-friendly">
            <div class="beer-category-section">
                <h3 class="category-title">🏨 <?php echo esc_html($city); ?>で泊まる</h3>
                
                <div class="beer-links-grid">
                    <?php if (!empty($rakuten_id)) : ?>
                    <div class="beer-link-card">
                        <a href="<?php echo esc_url($this->get_rakuten_url($city, $rakuten_id)); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="beer-link-wrapper">
                            <div class="link-content">
                                <h4 class="link-title">楽天トラベルで<?php echo esc_html($city); ?>のホテルを探す</h4>
                                <p class="link-description">口コミ評価の高いホテルを表示</p>
                            </div>
                            <div class="link-arrow">→</div>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($a8_media_id)) : ?>
                    <div class="beer-link-card">
                        <a href="<?php echo esc_url($this->get_jtb_url($city, $a8_media_id)); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="beer-link-wrapper">
                            <div class="link-content">
                                <h4 class="link-title">JTBで<?php echo esc_html($city); ?>のホテルを予約</h4>
                                <p class="link-description">安心の大手旅行会社</p>
                            </div>
                            <div class="link-arrow">→</div>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
        .beer-affiliate-container.user-friendly {
            margin: 30px 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .beer-category-section {
            margin-bottom: 35px;
        }
        .category-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f0a500;
        }
        .beer-links-grid {
            display: grid;
            gap: 15px;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
        .beer-link-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .beer-link-card:hover {
            border-color: #f0a500;
            box-shadow: 0 4px 12px rgba(240, 165, 0, 0.15);
            transform: translateY(-2px);
        }
        .beer-link-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            text-decoration: none;
            color: inherit;
            height: 100%;
        }
        .link-content {
            flex: 1;
            padding-right: 15px;
        }
        .link-title {
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }
        .link-description {
            font-size: 0.9em;
            color: #666;
            margin: 0;
            line-height: 1.4;
        }
        .link-arrow {
            flex-shrink: 0;
            color: #f0a500;
            font-size: 1.2em;
            transition: transform 0.3s ease;
        }
        .beer-link-card:hover .link-arrow {
            transform: translateX(4px);
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    private function get_rakuten_url($city, $affiliate_id) {
        return 'https://travel.rakuten.co.jp/hotel/search/?' . http_build_query(array(
            'f_area' => $city,
            'f_teikei' => 'premium',
            'f_sort' => 'review_high',
            'f_points_min' => '4',
            'f_keyword' => $city . ' クラフトビール',
            'f_affiliate_id' => $affiliate_id
        ));
    }
    
    private function get_jtb_url($city, $media_id) {
        $redirect_url = "https://www.jtb.co.jp/kokunai/hotel/list/{$city}/";
        return "https://px.a8.net/svt/ejp?a8mat=s00000005350001&a8ejpredirect=" . urlencode($redirect_url);
    }
}

// プラグインを初期化
function beer_affiliate_safe_init() {
    $core = new Beer_Affiliate_Safe_Core();
    $core->init();
}
add_action('plugins_loaded', 'beer_affiliate_safe_init');

// 有効化時の処理
function beer_affiliate_safe_activate() {
    add_option('beer_affiliate_version', BEER_AFFILIATE_VERSION);
    add_option('beer_affiliate_template', 'user-friendly');
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'beer_affiliate_safe_activate');

// 無効化時の処理
function beer_affiliate_safe_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'beer_affiliate_safe_deactivate');