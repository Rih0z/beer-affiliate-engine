<?php
/**
 * Plugin Name: Beer Affiliate Engine (Safe Mode)
 * Plugin URI: https://rihobeer.com/plugins/beer-affiliate-engine
 * Description: セーフモード版 - プログラムID個別設定対応
 * Version: 1.3.0-safe-improved
 * Author: RihoBeer
 * Text Domain: beer-affiliate-engine
 */

// 直接アクセス禁止
if (!defined('ABSPATH')) {
    exit;
}

// プラグイン定数を定義
define('BEER_AFFILIATE_VERSION', '1.3.0-safe-improved');
define('BEER_AFFILIATE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BEER_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));

// セーフモード用の改良クラス
class Beer_Affiliate_Safe_Core {
    
    public function init() {
        // ショートコードのみ登録
        add_shortcode('beer_affiliate', array($this, 'render_shortcode'));
        
        // 管理画面のみで設定を読み込み
        if (is_admin() && file_exists(BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-safe-settings.php')) {
            require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-safe-settings.php';
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
        
        return $this->generate_advanced_links($atts['city']);
    }
    
    private function generate_advanced_links($city) {
        $options = get_option('beer_affiliate_safe_settings', array());
        $program_ids = get_option('beer_affiliate_program_ids', array());
        $rakuten_id = isset($options['rakuten_affiliate_id']) ? $options['rakuten_affiliate_id'] : '';
        $a8_media_id = isset($options['a8_media_id']) ? $options['a8_media_id'] : '';
        
        $links = array();
        
        // 楽天トラベル
        if (!empty($rakuten_id)) {
            $links[] = array(
                'url' => $this->get_rakuten_url($city, $rakuten_id),
                'title' => "楽天トラベルで{$city}のホテルを探す",
                'description' => '口コミ評価の高いホテルを表示',
                'category' => 'hotel'
            );
        }
        
        // JTB国内旅行
        if (!empty($a8_media_id) && !empty($program_ids['jtb'])) {
            $links[] = array(
                'url' => $this->get_a8_url($program_ids['jtb'], "https://www.jtb.co.jp/kokunai/hotel/{$city}/"),
                'title' => "JTBで{$city}のホテルを予約",
                'description' => '安心の大手旅行会社',
                'category' => 'hotel'
            );
        }
        
        // 読売旅行
        if (!empty($a8_media_id) && !empty($program_ids['yomiuri'])) {
            $links[] = array(
                'url' => $this->get_a8_url($program_ids['yomiuri'], "https://www.yomiuri-ryokou.co.jp/search/?keyword={$city}"),
                'title' => "読売旅行で{$city}ツアーを探す",
                'description' => 'お得なパッケージツアー',
                'category' => 'travel'
            );
        }
        
        // 一休.comレストラン
        if (!empty($a8_media_id) && !empty($program_ids['ikyu'])) {
            $links[] = array(
                'url' => $this->get_a8_url($program_ids['ikyu'], "https://restaurant.ikyu.com/search/?keyword={$city}+ビール"),
                'title' => "{$city}のビアレストランを予約",
                'description' => '人気のビアレストランを厳選',
                'category' => 'experience'
            );
        }
        
        // Otomoni（クラフトビール定期便）
        if (!empty($a8_media_id) && !empty($program_ids['otomoni'])) {
            $links[] = array(
                'url' => $this->get_a8_url($program_ids['otomoni'], 'https://otomoni.jp/'),
                'title' => 'クラフトビール定期便を始める',
                'description' => '全国のブルワリーから毎月お届け',
                'category' => 'experience'
            );
        }
        
        // JTBショッピング
        if (!empty($a8_media_id) && !empty($program_ids['jtb_shopping'])) {
            $links[] = array(
                'url' => $this->get_a8_url($program_ids['jtb_shopping'], "https://shopping.jtb.co.jp/search/?q={$city}+地ビール"),
                'title' => "{$city}の地ビールをお取り寄せ",
                'description' => '現地の味を自宅で楽しむ',
                'category' => 'experience'
            );
        }
        
        // 海外都市の場合の追加リンク
        $international_cities = array('シアトル', 'ポートランド', 'ミュンヘン', 'ブリュッセル', 'プラハ', 'ロサンゼルス', 'ニューヨーク');
        $is_international = in_array($city, $international_cities);
        
        if ($is_international) {
            // カタール航空
            if (!empty($a8_media_id) && !empty($program_ids['qatar'])) {
                $links[] = array(
                    'url' => $this->get_a8_url($program_ids['qatar'], 'https://www.qatarairways.com/ja-jp/destinations.html'),
                    'title' => "カタール航空で{$city}へ",
                    'description' => '快適な空の旅',
                    'category' => 'travel'
                );
            }
            
            // Saily（海外eSIM）
            if (!empty($a8_media_id) && !empty($program_ids['saily'])) {
                $links[] = array(
                    'url' => $this->get_a8_url($program_ids['saily'], 'https://saily.app/'),
                    'title' => '海外で使えるeSIMを購入',
                    'description' => 'データ通信の心配なし',
                    'category' => 'travel'
                );
            }
            
            // Oooh（現地ツアー）
            if (!empty($a8_media_id) && !empty($program_ids['oooh'])) {
                $links[] = array(
                    'url' => $this->get_a8_url($program_ids['oooh'], "https://oooh.io/search?q={$city}+brewery+tour"),
                    'title' => "{$city}のブルワリーツアー",
                    'description' => '現地のビール文化を体験',
                    'category' => 'experience'
                );
            }
        }
        
        // カテゴリー別に整理
        $categorized_links = array(
            'hotel' => array('title' => "🏨 {$city}で泊まる", 'links' => array()),
            'travel' => array('title' => "✈️ {$city}への旅行プラン", 'links' => array()),
            'experience' => array('title' => "🍺 {$city}のビール体験", 'links' => array())
        );
        
        foreach ($links as $link) {
            $categorized_links[$link['category']]['links'][] = $link;
        }
        
        // 空のカテゴリーを削除
        $categorized_links = array_filter($categorized_links, function($category) {
            return !empty($category['links']);
        });
        
        ob_start();
        ?>
        <div class="beer-affiliate-container user-friendly">
            <?php foreach ($categorized_links as $category) : ?>
                <div class="beer-category-section">
                    <h3 class="category-title"><?php echo esc_html($category['title']); ?></h3>
                    
                    <div class="beer-links-grid">
                        <?php foreach ($category['links'] as $link) : ?>
                        <div class="beer-link-card">
                            <a href="<?php echo esc_url($link['url']); ?>" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="beer-link-wrapper">
                                <div class="link-content">
                                    <h4 class="link-title"><?php echo esc_html($link['title']); ?></h4>
                                    <p class="link-description"><?php echo esc_html($link['description']); ?></p>
                                </div>
                                <div class="link-arrow">→</div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($categorized_links)) : ?>
                <div class="beer-notice">
                    <p>アフィリエイトリンクを表示するには、<a href="<?php echo admin_url('admin.php?page=beer-affiliate-safe-settings'); ?>">設定ページ</a>でアフィリエイトIDを設定してください。</p>
                </div>
            <?php endif; ?>
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
        .beer-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .beer-notice p {
            margin: 0;
            color: #856404;
        }
        .beer-notice a {
            color: #0073aa;
            text-decoration: none;
        }
        .beer-notice a:hover {
            text-decoration: underline;
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
    
    private function get_a8_url($program_id, $redirect_url) {
        return "https://px.a8.net/svt/ejp?a8mat={$program_id}&a8ejpredirect=" . urlencode($redirect_url);
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
    
    // デフォルトのプログラムIDを設定
    $default_program_ids = array(
        'jtb' => 's00000005350001',
        'yomiuri' => 's00000025997001',
        'ikyu' => 's00000000218004',
        'jtb_shopping' => 's00000018449001',
        'otomoni' => 's00000023019001',
        'qatar' => 's00000026391001',
        'oooh' => 's00000026491001',
        'saily' => 's00000026058001',
        'travelist' => 's00000023067003'
    );
    
    add_option('beer_affiliate_program_ids', $default_program_ids);
    
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'beer_affiliate_safe_activate');

// 無効化時の処理
function beer_affiliate_safe_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'beer_affiliate_safe_deactivate');