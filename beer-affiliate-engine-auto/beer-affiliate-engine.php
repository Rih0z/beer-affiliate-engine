<?php
/**
 * Plugin Name: Beer Affiliate Engine
 * Plugin URI: https://rihobeer.com/plugins/beer-affiliate-engine
 * Description: 記事内の地域名を自動検出して旅行アフィリエイトリンクを生成
 * Version: 2.0.0
 * Author: RihoBeer
 * Text Domain: beer-affiliate-engine
 */

// 直接アクセス禁止
if (!defined('ABSPATH')) {
    exit;
}

// プラグイン定数を定義
define('BEER_AFFILIATE_VERSION', '2.0.0');
define('BEER_AFFILIATE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BEER_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));

class Beer_Affiliate_Engine {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_filter('the_content', array($this, 'process_content'), 20);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        if (is_admin()) {
            require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-admin-settings.php';
            new Beer_Affiliate_Admin_Settings();
        }
    }
    
    public function process_content($content) {
        // ビール関連の記事でない場合はスキップ
        if (!$this->is_beer_related($content)) {
            return $content;
        }
        
        // 地域名を抽出
        $locations = $this->extract_locations($content);
        
        if (empty($locations)) {
            return $content;
        }
        
        // アフィリエイトプログラムを取得
        $programs = $this->get_affiliate_programs();
        
        if (empty($programs)) {
            return $content;
        }
        
        // リンクを生成
        $links_html = $this->generate_links($locations, $programs);
        
        // コンテンツの最後に追加
        return $content . $links_html;
    }
    
    private function is_beer_related($content) {
        $beer_keywords = array('ビール', 'beer', 'IPA', 'エール', 'スタウト', 'ラガー', 'ブルワリー', '醸造所', 'クラフトビール');
        
        foreach ($beer_keywords as $keyword) {
            if (mb_stripos($content, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function extract_locations($content) {
        $locations = array();
        
        // 日本の主要都市
        $cities = array(
            '東京' => array('type' => 'domestic', 'area' => '関東'),
            '大阪' => array('type' => 'domestic', 'area' => '関西'),
            '京都' => array('type' => 'domestic', 'area' => '関西'),
            '札幌' => array('type' => 'domestic', 'area' => '北海道'),
            '福岡' => array('type' => 'domestic', 'area' => '九州'),
            '横浜' => array('type' => 'domestic', 'area' => '関東'),
            '名古屋' => array('type' => 'domestic', 'area' => '中部'),
            '神戸' => array('type' => 'domestic', 'area' => '関西'),
            '仙台' => array('type' => 'domestic', 'area' => '東北'),
            '金沢' => array('type' => 'domestic', 'area' => '北陸'),
            '広島' => array('type' => 'domestic', 'area' => '中国'),
            '那覇' => array('type' => 'domestic', 'area' => '沖縄'),
        );
        
        // 海外都市（ビール関連）
        $international_cities = array(
            'シアトル' => array('type' => 'international', 'country' => 'アメリカ'),
            'ポートランド' => array('type' => 'international', 'country' => 'アメリカ'),
            'サンディエゴ' => array('type' => 'international', 'country' => 'アメリカ'),
            'ミュンヘン' => array('type' => 'international', 'country' => 'ドイツ'),
            'ベルリン' => array('type' => 'international', 'country' => 'ドイツ'),
            'プラハ' => array('type' => 'international', 'country' => 'チェコ'),
            'ブリュッセル' => array('type' => 'international', 'country' => 'ベルギー'),
            'ダブリン' => array('type' => 'international', 'country' => 'アイルランド'),
            'アムステルダム' => array('type' => 'international', 'country' => 'オランダ'),
        );
        
        $all_cities = array_merge($cities, $international_cities);
        
        foreach ($all_cities as $city => $info) {
            if (mb_strpos($content, $city) !== false) {
                $locations[$city] = $info;
            }
        }
        
        return $locations;
    }
    
    private function get_affiliate_programs() {
        $programs = get_option('beer_affiliate_programs', array());
        
        // デフォルトプログラム（楽天）
        if (empty($programs['rakuten_travel'])) {
            $programs['rakuten_travel'] = array(
                'name' => '楽天トラベル',
                'type' => 'rakuten',
                'url_template' => 'https://travel.rakuten.co.jp/hotel/search/?f_area={CITY}&f_keyword={CITY}+クラフトビール&f_teikei=premium&f_sort=review_high&f_affiliate_id={AFFILIATE_ID}',
                'affiliate_id' => '20a2fc9d.5c6c02f2.20a2fc9e.541a36d0',
                'application_id' => '1013646616942500290',
                'label' => '楽天トラベルで{CITY}のホテルを探す',
                'enabled' => true
            );
        }
        
        return array_filter($programs, function($program) {
            return isset($program['enabled']) && $program['enabled'];
        });
    }
    
    private function generate_links($locations, $programs) {
        $primary_location = key($locations);
        $location_info = current($locations);
        
        ob_start();
        ?>
        <div class="beer-affiliate-container">
            <h3 class="beer-affiliate-title">🍺 <?php echo esc_html($primary_location); ?>のビール旅情報</h3>
            
            <div class="beer-affiliate-links">
                <?php foreach ($programs as $program_key => $program) : ?>
                    <?php
                    $url = $this->build_url($program, $primary_location, $location_info);
                    if ($url) :
                    ?>
                    <div class="beer-affiliate-link-item">
                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="beer-affiliate-link">
                            <span class="link-label"><?php echo esc_html(str_replace('{CITY}', $primary_location, $program['label'])); ?></span>
                            <span class="link-arrow">→</span>
                        </a>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($locations) > 1) : ?>
            <div class="beer-affiliate-other-locations">
                <p>その他の地域: 
                <?php 
                $other_cities = array_slice(array_keys($locations), 1);
                echo esc_html(implode('、', $other_cities));
                ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    private function build_url($program, $city, $location_info) {
        $url = $program['url_template'];
        
        // 基本置換
        $url = str_replace('{CITY}', rawurlencode($city), $url);
        
        // プログラムタイプ別の処理
        switch ($program['type']) {
            case 'rakuten':
                $url = str_replace('{AFFILIATE_ID}', $program['affiliate_id'], $url);
                if (isset($program['application_id'])) {
                    $url = str_replace('{APPLICATION_ID}', $program['application_id'], $url);
                }
                break;
                
            case 'a8':
                if (isset($program['program_id'])) {
                    $url = str_replace('{PROGRAM_ID}', $program['program_id'], $url);
                }
                if (isset($program['media_id'])) {
                    $url = str_replace('{MEDIA_ID}', $program['media_id'], $url);
                }
                break;
                
            default:
                // カスタムプログラムの場合
                if (isset($program['custom_params'])) {
                    foreach ($program['custom_params'] as $key => $value) {
                        $url = str_replace('{' . strtoupper($key) . '}', rawurlencode($value), $url);
                    }
                }
                break;
        }
        
        // 地域情報の置換
        if ($location_info['type'] === 'international' && isset($location_info['country'])) {
            $url = str_replace('{COUNTRY}', rawurlencode($location_info['country']), $url);
        }
        
        return $url;
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style(
            'beer-affiliate-style',
            BEER_AFFILIATE_PLUGIN_URL . 'assets/css/style.css',
            array(),
            BEER_AFFILIATE_VERSION
        );
    }
}

// プラグインを初期化
add_action('plugins_loaded', function() {
    Beer_Affiliate_Engine::get_instance();
});

// 有効化フック
register_activation_hook(__FILE__, function() {
    // デフォルト設定を追加
    $default_programs = array(
        'rakuten_travel' => array(
            'name' => '楽天トラベル',
            'type' => 'rakuten',
            'url_template' => 'https://travel.rakuten.co.jp/hotel/search/?f_area={CITY}&f_keyword={CITY}+クラフトビール&f_teikei=premium&f_sort=review_high&f_affiliate_id={AFFILIATE_ID}',
            'affiliate_id' => '20a2fc9d.5c6c02f2.20a2fc9e.541a36d0',
            'application_id' => '1013646616942500290',
            'label' => '楽天トラベルで{CITY}のホテルを探す',
            'enabled' => true
        )
    );
    
    add_option('beer_affiliate_programs', $default_programs);
    add_option('beer_affiliate_version', BEER_AFFILIATE_VERSION);
});