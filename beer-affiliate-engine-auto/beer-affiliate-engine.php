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
        
        // リンク検証クラスを読み込み（必要時のみ）
        if (is_admin() || (defined('WP_DEBUG') && WP_DEBUG)) {
            require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-link-validator.php';
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
        
        // 有効なプログラムのみフィルタリング
        $valid_programs = $this->filter_valid_programs($programs);
        
        if (empty($valid_programs)) {
            return $content;
        }
        
        // リンクを生成
        $links_html = $this->generate_links($locations, $valid_programs);
        
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
            // 関東
            '東京' => array('type' => 'domestic', 'area' => '関東'),
            '横浜' => array('type' => 'domestic', 'area' => '関東'),
            '川崎' => array('type' => 'domestic', 'area' => '関東'),
            '千葉' => array('type' => 'domestic', 'area' => '関東'),
            'さいたま' => array('type' => 'domestic', 'area' => '関東'),
            '水戸' => array('type' => 'domestic', 'area' => '関東'),
            '宇都宮' => array('type' => 'domestic', 'area' => '関東'),
            '前橋' => array('type' => 'domestic', 'area' => '関東'),
            '高崎' => array('type' => 'domestic', 'area' => '関東'),
            
            // 関西
            '大阪' => array('type' => 'domestic', 'area' => '関西'),
            '京都' => array('type' => 'domestic', 'area' => '関西'),
            '神戸' => array('type' => 'domestic', 'area' => '関西'),
            '奈良' => array('type' => 'domestic', 'area' => '関西'),
            '和歌山' => array('type' => 'domestic', 'area' => '関西'),
            '大津' => array('type' => 'domestic', 'area' => '関西'),
            
            // 中部
            '名古屋' => array('type' => 'domestic', 'area' => '中部'),
            '静岡' => array('type' => 'domestic', 'area' => '中部'),
            '浜松' => array('type' => 'domestic', 'area' => '中部'),
            '岐阜' => array('type' => 'domestic', 'area' => '中部'),
            '長野' => array('type' => 'domestic', 'area' => '中部'),
            '松本' => array('type' => 'domestic', 'area' => '中部'),
            '新潟' => array('type' => 'domestic', 'area' => '中部'),
            '富山' => array('type' => 'domestic', 'area' => '北陸'),
            '金沢' => array('type' => 'domestic', 'area' => '北陸'),
            '福井' => array('type' => 'domestic', 'area' => '北陸'),
            
            // 北海道・東北
            '札幌' => array('type' => 'domestic', 'area' => '北海道'),
            '函館' => array('type' => 'domestic', 'area' => '北海道'),
            '旭川' => array('type' => 'domestic', 'area' => '北海道'),
            '小樽' => array('type' => 'domestic', 'area' => '北海道'),
            '仙台' => array('type' => 'domestic', 'area' => '東北'),
            '青森' => array('type' => 'domestic', 'area' => '東北'),
            '盛岡' => array('type' => 'domestic', 'area' => '東北'),
            '秋田' => array('type' => 'domestic', 'area' => '東北'),
            '山形' => array('type' => 'domestic', 'area' => '東北'),
            '福島' => array('type' => 'domestic', 'area' => '東北'),
            '郡山' => array('type' => 'domestic', 'area' => '東北'),
            
            // 中国・四国
            '広島' => array('type' => 'domestic', 'area' => '中国'),
            '岡山' => array('type' => 'domestic', 'area' => '中国'),
            '山口' => array('type' => 'domestic', 'area' => '中国'),
            '鳥取' => array('type' => 'domestic', 'area' => '中国'),
            '松江' => array('type' => 'domestic', 'area' => '中国'),
            '高松' => array('type' => 'domestic', 'area' => '四国'),
            '松山' => array('type' => 'domestic', 'area' => '四国'),
            '高知' => array('type' => 'domestic', 'area' => '四国'),
            '徳島' => array('type' => 'domestic', 'area' => '四国'),
            
            // 九州・沖縄
            '福岡' => array('type' => 'domestic', 'area' => '九州'),
            '北九州' => array('type' => 'domestic', 'area' => '九州'),
            '熊本' => array('type' => 'domestic', 'area' => '九州'),
            '鹿児島' => array('type' => 'domestic', 'area' => '九州'),
            '長崎' => array('type' => 'domestic', 'area' => '九州'),
            '大分' => array('type' => 'domestic', 'area' => '九州'),
            '宮崎' => array('type' => 'domestic', 'area' => '九州'),
            '佐賀' => array('type' => 'domestic', 'area' => '九州'),
            '那覇' => array('type' => 'domestic', 'area' => '沖縄'),
            '沖縄' => array('type' => 'domestic', 'area' => '沖縄'),
        );
        
        // 海外都市（ビール関連）
        $international_cities = array(
            // アメリカ
            'シアトル' => array('type' => 'international', 'country' => 'アメリカ'),
            'ポートランド' => array('type' => 'international', 'country' => 'アメリカ'),
            'サンディエゴ' => array('type' => 'international', 'country' => 'アメリカ'),
            'サンフランシスコ' => array('type' => 'international', 'country' => 'アメリカ'),
            'ロサンゼルス' => array('type' => 'international', 'country' => 'アメリカ'),
            'ニューヨーク' => array('type' => 'international', 'country' => 'アメリカ'),
            'ボストン' => array('type' => 'international', 'country' => 'アメリカ'),
            'シカゴ' => array('type' => 'international', 'country' => 'アメリカ'),
            'デンバー' => array('type' => 'international', 'country' => 'アメリカ'),
            
            // ヨーロッパ
            'ミュンヘン' => array('type' => 'international', 'country' => 'ドイツ'),
            'ベルリン' => array('type' => 'international', 'country' => 'ドイツ'),
            'フランクフルト' => array('type' => 'international', 'country' => 'ドイツ'),
            'ケルン' => array('type' => 'international', 'country' => 'ドイツ'),
            'プラハ' => array('type' => 'international', 'country' => 'チェコ'),
            'ブリュッセル' => array('type' => 'international', 'country' => 'ベルギー'),
            'ダブリン' => array('type' => 'international', 'country' => 'アイルランド'),
            'アムステルダム' => array('type' => 'international', 'country' => 'オランダ'),
            'ロンドン' => array('type' => 'international', 'country' => 'イギリス'),
            'エディンバラ' => array('type' => 'international', 'country' => 'イギリス'),
            'コペンハーゲン' => array('type' => 'international', 'country' => 'デンマーク'),
            'ウィーン' => array('type' => 'international', 'country' => 'オーストリア'),
            
            // アジア・オセアニア
            'バンコク' => array('type' => 'international', 'country' => 'タイ'),
            'シンガポール' => array('type' => 'international', 'country' => 'シンガポール'),
            '香港' => array('type' => 'international', 'country' => '香港'),
            '台北' => array('type' => 'international', 'country' => '台湾'),
            'ソウル' => array('type' => 'international', 'country' => '韓国'),
            'メルボルン' => array('type' => 'international', 'country' => 'オーストラリア'),
            'シドニー' => array('type' => 'international', 'country' => 'オーストラリア'),
            'オークランド' => array('type' => 'international', 'country' => 'ニュージーランド'),
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
        
        // デフォルトプログラム
        $default_programs = array(
            'rakuten_travel' => array(
                'name' => '楽天トラベル',
                'type' => 'rakuten',
                'url_template' => 'https://hb.afl.rakuten.co.jp/hgc/{AFFILIATE_ID}/?pc=https%3A%2F%2Ftravel.rakuten.co.jp%2F&m=https%3A%2F%2Ftravel.rakuten.co.jp%2F',
                'affiliate_id' => '20a2fc9d.5c6c02f2.20a2fc9e.541a36d0',
                'application_id' => '1013646616942500290',
                'label' => '楽天トラベルで{CITY}のホテルを探す',
                'enabled' => true
            ),
            'jtb' => array(
                'name' => 'JTB国内旅行',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}',
                'a8mat_code' => '4530O4+61B8KY+15A4+63WO2',
                'label' => 'JTBで{CITY}のホテルを探す',
                'enabled' => true
            ),
            'ikyu_restaurant' => array(
                'name' => '一休.comレストラン',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}',
                'a8mat_code' => '3NJ1WF+CEJ4HE+1OK+NX736',
                'label' => '一休で{CITY}のレストランを探す',
                'enabled' => true
            ),
            'jalan' => array(
                'name' => 'じゃらんnet',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Fwww.jalan.net%2F',
                'program_id' => '5011',
                'media_id' => 'a17092772583',
                'label' => 'じゃらんで{CITY}の宿を探す',
                'enabled' => true
            ),
            'rakuten_travel_a8' => array(
                'name' => '楽天トラベル(A8)',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Ftravel.rakuten.co.jp%2F',
                'program_id' => '4196',
                'media_id' => 'a17092772583',
                'label' => '楽天トラベル(A8)で探す',
                'enabled' => false
            ),
            'relux' => array(
                'name' => 'Relux',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Frlx.jp%2F',
                'program_id' => '15359',
                'media_id' => 'a17092772583',
                'label' => 'Reluxで{CITY}の高級ホテルを探す',
                'enabled' => true
            ),
            'yahoo_travel' => array(
                'name' => 'Yahoo!トラベル',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Ftravel.yahoo.co.jp%2F',
                'program_id' => '23814',
                'media_id' => 'a17092772583',
                'label' => 'Yahoo!トラベルで{CITY}を探す',
                'enabled' => true
            ),
            'yomiuri_travel' => array(
                'name' => '読売旅行',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}',
                'a8mat_code' => '4530O4+5VYC4Y+5KLE+5YRHE',
                'label' => '読売旅行でツアーを探す',
                'enabled' => true
            ),
            'otomoni' => array(
                'name' => 'Otomoni',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}',
                'a8mat_code' => '3NJ1WF+D1R12Q+4XM6+5YJRM',
                'label' => 'Otomoniでクラフトビール定期便',
                'enabled' => true
            ),
            'fast_fi' => array(
                'name' => '海外Wi-FiレンタルのFAST-Fi',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Ffast-fi.net%2F',
                'program_id' => '23641',
                'media_id' => 'a17092772583',
                'label' => '海外WiFiレンタル(FAST-Fi)',
                'enabled' => true
            ),
            'nissan_rental' => array(
                'name' => '日産レンタカー',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Fnissan-rentacar.com%2F',
                'program_id' => '2221',
                'media_id' => 'a17092772583',
                'label' => '日産レンタカーで車を借りる',
                'enabled' => true
            ),
            'jal' => array(
                'name' => 'JAL 日本航空',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Fwww.jal.co.jp%2F',
                'program_id' => '4940',
                'media_id' => 'a17092772583',
                'label' => 'JALで航空券を予約',
                'enabled' => true
            ),
            'ana' => array(
                'name' => 'ANA（全日空）',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Fwww.ana.co.jp%2F',
                'program_id' => '16314',
                'media_id' => 'a17092772583',
                'label' => 'ANAで航空券を予約',
                'enabled' => true
            ),
            'travel_standard' => array(
                'name' => 'TRAVEL STANDARD JAPAN',
                'type' => 'a8',
                'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}',
                'a8mat_code' => '4530O4+61WO6Q+5LKE+5YJRM',
                'label' => 'TRAVEL STANDARDで海外旅行を探す',
                'enabled' => true
            )
        );
        
        // デフォルトプログラムを追加（既存のものは上書きしない）
        foreach ($default_programs as $key => $program) {
            if (empty($programs[$key])) {
                $programs[$key] = $program;
            }
        }
        
        return array_filter($programs, function($program) {
            return isset($program['enabled']) && $program['enabled'];
        });
    }
    
    private function generate_links($locations, $programs) {
        $primary_location = key($locations);
        $location_info = current($locations);
        
        // 有効なリンクのみ収集
        $valid_links = array();
        foreach ($programs as $program_key => $program) {
            $url = $this->build_url($program, $primary_location, $location_info);
            if ($url && $this->is_valid_affiliate_url($url, $program)) {
                $valid_links[] = array(
                    'url' => $url,
                    'label' => str_replace('{CITY}', $primary_location, $program['label']),
                    'program' => $program
                );
            }
        }
        
        // 有効なリンクがない場合は何も表示しない
        if (empty($valid_links)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Beer Affiliate: No valid links generated for location: ' . $primary_location);
            }
            return '';
        }
        
        ob_start();
        ?>
        <div class="beer-affiliate-container">
            <h3 class="beer-affiliate-title">🍺 <?php echo esc_html($primary_location); ?>のビール旅情報</h3>
            
            <div class="beer-affiliate-links">
                <?php foreach ($valid_links as $link) : ?>
                    <div class="beer-affiliate-link-item">
                        <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer" class="beer-affiliate-link" data-program="<?php echo esc_attr($link['program']['name']); ?>">
                            <span class="link-label"><?php echo esc_html($link['label']); ?></span>
                            <span class="link-arrow">→</span>
                        </a>
                    </div>
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
        
        // プログラムタイプ別の処理
        switch ($program['type']) {
            case 'rakuten':
                // 楽天の場合、URLテンプレート内の{CITY}はエンコード済みなので、そのまま置換
                $encoded_city = rawurlencode($city);
                $url = str_replace('{CITY}', $encoded_city, $url);
                $url = str_replace('{AFFILIATE_ID}', $program['affiliate_id'], $url);
                if (isset($program['application_id'])) {
                    $url = str_replace('{APPLICATION_ID}', $program['application_id'], $url);
                }
                break;
                
            case 'a8':
                // A8の場合、a8mat_codeが設定されていればそれを使用
                if (!empty($program['a8mat_code'])) {
                    $url = str_replace('{A8MAT}', $program['a8mat_code'], $url);
                } else {
                    // 従来の方式（後方互換性のため）
                    $media_id = isset($program['media_id']) ? $program['media_id'] : 'a17092772583';
                    $media_id_clean = ltrim($media_id, 'a');
                    
                    // A8のa8matパラメータフォーマット
                    $site_id = $this->get_a8_site_id($program['program_id']);
                    $a8mat = $media_id_clean . '+s00000' . $site_id;
                    $url = str_replace('{A8MAT}', $a8mat, $url);
                }
                
                // その他の置換
                $encoded_city = rawurlencode($city);
                $url = str_replace('{CITY}', $encoded_city, $url);
                if (isset($program['media_id'])) {
                    $media_id_clean = ltrim($program['media_id'], 'a');
                    $url = str_replace('{MEDIA_ID}', $media_id_clean, $url);
                }
                if (isset($program['program_id'])) {
                    $url = str_replace('{PROGRAM_ID}', $program['program_id'], $url);
                }
                break;
                
            default:
                // カスタムプログラムの場合
                $url = str_replace('{CITY}', rawurlencode($city), $url);
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
        
        // デバッグ用ログ（本番環境では削除またはコメントアウト）
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Beer Affiliate URL Generated: ' . $url);
            error_log('Program: ' . print_r($program, true));
        }
        
        return $url;
    }
    
    private function get_a8_site_id($program_id) {
        // A8.netのプログラムIDからサイトIDを取得（s00000000000000形式の数字部分）
        $site_ids = array(
            '5350' => '05350001',      // JTB国内旅行
            '23449' => '23449001',    // 一休.comレストラン
            '5011' => '05011001',      // じゃらんnet
            '4196' => '04196001',      // 楽天トラベル(A8)
            '15359' => '15359001',    // Relux
            '23814' => '23814001',    // Yahoo!トラベル
            '22834' => '22834001',    // 読売旅行
            '22658' => '22658001',    // Otomoni
            '23641' => '23641001',    // FAST-Fi
            '2221' => '02221001',      // 日産レンタカー
            '4940' => '04940001',      // JAL
            '16314' => '16314001',    // ANA
            '22763' => '22763001',    // TRAVEL STANDARD
        );
        
        return isset($site_ids[$program_id]) ? $site_ids[$program_id] : '00000001';
    }
    
    private function filter_valid_programs($programs) {
        $valid_programs = array();
        
        foreach ($programs as $key => $program) {
            // 必須パラメータのチェック
            if ($program['type'] === 'rakuten') {
                // 楽天の場合、アフィリエイトIDが必須
                if (empty($program['affiliate_id'])) {
                    continue;
                }
            } elseif ($program['type'] === 'a8') {
                // A8の場合、メディアIDが必須
                if (empty($program['media_id'])) {
                    continue;
                }
            }
            
            // URLテンプレートが存在するかチェック
            if (empty($program['url_template'])) {
                continue;
            }
            
            $valid_programs[$key] = $program;
        }
        
        return $valid_programs;
    }
    
    private function is_valid_affiliate_url($url, $program) {
        // URLが空の場合は無効
        if (empty($url)) {
            return false;
        }
        
        // プレースホルダーが残っている場合は無効
        if (strpos($url, '{') !== false && strpos($url, '}') !== false) {
            return false;
        }
        
        // 楽天アフィリエイトの検証
        if ($program['type'] === 'rakuten') {
            // 楽天のアフィリエイトURLは必ず hb.afl.rakuten.co.jp で始まる
            if (strpos($url, 'https://hb.afl.rakuten.co.jp/hgc/') !== 0) {
                return false;
            }
            // アフィリエイトIDが含まれているか確認
            if (strpos($url, $program['affiliate_id']) === false) {
                return false;
            }
        }
        
        // A8.netの検証
        if ($program['type'] === 'a8') {
            // A8のアフィリエイトURLは必ず px.a8.net で始まる
            if (strpos($url, 'https://px.a8.net/svt/ejp?') !== 0) {
                return false;
            }
            // a8matパラメータが含まれているか確認
            if (strpos($url, 'a8mat=') === false) {
                return false;
            }
            // a8ejpredirectパラメータが含まれているか確認
            if (strpos($url, 'a8ejpredirect=') === false) {
                return false;
            }
            // メディアIDが含まれているか確認（a8matパラメータ内）
            $media_id_clean = ltrim($program['media_id'], 'a');
            if (strpos($url, $media_id_clean) === false) {
                return false;
            }
        }
        
        return true;
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
    // デフォルト設定を追加（get_affiliate_programsと同じ内容）
    $default_programs = array(
        'rakuten_travel' => array(
            'name' => '楽天トラベル',
            'type' => 'rakuten',
            'url_template' => 'https://hb.afl.rakuten.co.jp/hgc/{AFFILIATE_ID}/?pc=https%3A%2F%2Ftravel.rakuten.co.jp%2F&m=https%3A%2F%2Ftravel.rakuten.co.jp%2F',
            'affiliate_id' => '20a2fc9d.5c6c02f2.20a2fc9e.541a36d0',
            'application_id' => '1013646616942500290',
            'label' => '楽天トラベルで{CITY}のホテルを探す',
            'enabled' => true
        ),
        'jtb' => array(
            'name' => 'JTB国内旅行',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}',
            'a8mat_code' => '4530O4+61B8KY+15A4+63WO2',
            'label' => 'JTBで{CITY}のホテルを探す',
            'enabled' => true
        ),
        'ikyu_restaurant' => array(
            'name' => '一休.comレストラン',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}',
            'a8mat_code' => '3NJ1WF+CEJ4HE+1OK+NX736',
            'label' => '一休で{CITY}のレストランを探す',
            'enabled' => true
        ),
        'jalan' => array(
            'name' => 'じゃらんnet',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Fwww.jalan.net%2F',
            'program_id' => '5011',
            'media_id' => 'a17092772583',
            'label' => 'じゃらんで{CITY}の宿を探す',
            'enabled' => true
        ),
        'relux' => array(
            'name' => 'Relux',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Frlx.jp%2F',
            'program_id' => '15359',
            'media_id' => 'a17092772583',
            'label' => 'Reluxで{CITY}の高級ホテルを探す',
            'enabled' => true
        ),
        'yahoo_travel' => array(
            'name' => 'Yahoo!トラベル',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Ftravel.yahoo.co.jp%2F',
            'program_id' => '23814',
            'media_id' => 'a17092772583',
            'label' => 'Yahoo!トラベルで{CITY}を探す',
            'enabled' => true
        ),
        'yomiuri_travel' => array(
            'name' => '読売旅行',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}',
            'a8mat_code' => '4530O4+5VYC4Y+5KLE+5YRHE',
            'label' => '読売旅行でツアーを探す',
            'enabled' => true
        ),
        'otomoni' => array(
            'name' => 'Otomoni',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}',
            'a8mat_code' => '3NJ1WF+D1R12Q+4XM6+5YJRM',
            'label' => 'Otomoniでクラフトビール定期便',
            'enabled' => true
        ),
        'fast_fi' => array(
            'name' => '海外Wi-FiレンタルのFAST-Fi',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Ffast-fi.net%2F',
            'program_id' => '23641',
            'media_id' => 'a17092772583',
            'label' => '海外WiFiレンタル(FAST-Fi)',
            'enabled' => true
        ),
        'nissan_rental' => array(
            'name' => '日産レンタカー',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Fnissan-rentacar.com%2F',
            'program_id' => '2221',
            'media_id' => 'a17092772583',
            'label' => '日産レンタカーで車を借りる',
            'enabled' => true
        ),
        'jal' => array(
            'name' => 'JAL 日本航空',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Fwww.jal.co.jp%2F',
            'program_id' => '4940',
            'media_id' => 'a17092772583',
            'label' => 'JALで航空券を予約',
            'enabled' => true
        ),
        'ana' => array(
            'name' => 'ANA（全日空）',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}&a8ejpredirect=https%3A%2F%2Fwww.ana.co.jp%2F',
            'program_id' => '16314',
            'media_id' => 'a17092772583',
            'label' => 'ANAで航空券を予約',
            'enabled' => true
        ),
        'travel_standard' => array(
            'name' => 'TRAVEL STANDARD JAPAN',
            'type' => 'a8',
            'url_template' => 'https://px.a8.net/svt/ejp?a8mat={A8MAT}',
            'a8mat_code' => '4530O4+61WO6Q+5LKE+5YJRM',
            'label' => 'TRAVEL STANDARDで海外旅行を探す',
            'enabled' => true
        )
    );
    
    add_option('beer_affiliate_programs', $default_programs);
    add_option('beer_affiliate_version', BEER_AFFILIATE_VERSION);
});