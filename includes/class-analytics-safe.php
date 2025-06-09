<?php
/**
 * アフィリエイトリンクの分析機能（安全版）
 * 
 * @package Beer_Affiliate_Engine
 */

class Beer_Affiliate_Analytics {
    
    /**
     * テーブル名
     */
    private $table_name;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // 初期化を遅延実行
        add_action('init', array($this, 'late_init'));
    }
    
    /**
     * 遅延初期化
     */
    public function late_init() {
        global $wpdb;
        
        // $wpdbが利用可能な場合のみテーブル名を設定
        if (isset($wpdb) && is_object($wpdb) && property_exists($wpdb, 'prefix')) {
            $this->table_name = $wpdb->prefix . 'beer_affiliate_clicks';
        } else {
            // フォールバック
            $this->table_name = 'wp_beer_affiliate_clicks';
        }
        
        // フックを登録（関数が存在する場合のみ）
        if (function_exists('add_action')) {
            add_action('wp_ajax_beer_affiliate_track_click', array($this, 'track_click'));
            add_action('wp_ajax_nopriv_beer_affiliate_track_click', array($this, 'track_click'));
            add_action('admin_menu', array($this, 'add_analytics_menu'));
        }
    }
    
    /**
     * データベーステーブルを作成（静的メソッド）
     */
    public static function create_tables() {
        // WordPress環境でのみ実行
        if (!defined('ABSPATH')) {
            return;
        }
        
        global $wpdb;
        
        // $wpdbが利用可能でない場合は処理をスキップ
        if (!isset($wpdb) || !is_object($wpdb) || !property_exists($wpdb, 'prefix')) {
            return;
        }
        
        $table_name = $wpdb->prefix . 'beer_affiliate_clicks';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            service varchar(100) NOT NULL,
            city varchar(100) NOT NULL,
            post_id bigint(20) DEFAULT NULL,
            post_title text,
            clicked_at datetime DEFAULT CURRENT_TIMESTAMP,
            user_ip varchar(45) DEFAULT NULL,
            user_agent text,
            referer text,
            PRIMARY KEY (id),
            KEY service (service),
            KEY city (city),
            KEY post_id (post_id),
            KEY clicked_at (clicked_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * クリックをトラッキング
     */
    public function track_click() {
        // nonceチェック
        if (!isset($_POST['nonce']) || !function_exists('wp_verify_nonce') || !wp_verify_nonce($_POST['nonce'], 'beer_affiliate_click')) {
            wp_die('Invalid request');
        }
        
        global $wpdb;
        
        if (!isset($wpdb) || !is_object($wpdb)) {
            wp_die('Database not available');
        }
        
        // データを取得
        $service = sanitize_text_field($_POST['service']);
        $city = sanitize_text_field($_POST['city']);
        $post_id = intval($_POST['post_id']);
        $post_title = function_exists('get_the_title') ? get_the_title($post_id) : '';
        $user_ip = $this->get_user_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        
        // データベースに記録
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'service' => $service,
                'city' => $city,
                'post_id' => $post_id,
                'post_title' => $post_title,
                'user_ip' => $user_ip,
                'user_agent' => $user_agent,
                'referer' => $referer
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to track click');
        }
    }
    
    /**
     * ユーザーIPを取得
     */
    private function get_user_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = trim($_SERVER[$key]);
                if (filter_var($ip, FILTER_VALIDATE_IP, 
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * 管理メニューを追加
     */
    public function add_analytics_menu() {
        if (!function_exists('add_submenu_page')) {
            return;
        }
        
        add_submenu_page(
            'options-general.php',
            'Beer Affiliate クリック分析',
            'Beer Affiliate Analytics',
            'manage_options',
            'beer-affiliate-analytics',
            array($this, 'render_analytics_page')
        );
    }
    
    /**
     * 分析ページを表示
     */
    public function render_analytics_page() {
        global $wpdb;
        
        if (!isset($wpdb) || !is_object($wpdb)) {
            echo '<div class="wrap"><h1>エラー</h1><p>データベースが利用できません。</p></div>';
            return;
        }
        
        // 期間を取得（デフォルトは30日）
        $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        ?>
        <div class="wrap">
            <h1>Beer Affiliate Engine - クリック分析</h1>
            
            <!-- 期間選択 -->
            <form method="get" style="margin: 20px 0;">
                <input type="hidden" name="page" value="beer-affiliate-analytics">
                <label>期間: 
                    <select name="days" onchange="this.form.submit()">
                        <option value="7" <?php selected($days, 7); ?>>過去7日間</option>
                        <option value="30" <?php selected($days, 30); ?>>過去30日間</option>
                        <option value="90" <?php selected($days, 90); ?>>過去90日間</option>
                        <option value="365" <?php selected($days, 365); ?>>過去1年間</option>
                    </select>
                </label>
            </form>
            
            <!-- サマリー -->
            <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                <h2>サマリー（過去<?php echo $days; ?>日間）</h2>
                <?php $this->display_summary($start_date); ?>
            </div>
            
            <!-- サービス別統計 -->
            <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                <h2>サービス別クリック数</h2>
                <?php $this->display_service_stats($start_date); ?>
            </div>
            
            <!-- 都市別統計 -->
            <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                <h2>都市別クリック数</h2>
                <?php $this->display_city_stats($start_date); ?>
            </div>
            
            <!-- 記事別統計 -->
            <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                <h2>記事別クリック数</h2>
                <?php $this->display_post_stats($start_date); ?>
            </div>
            
            <!-- 最近のクリック -->
            <div class="card" style="max-width: 100%;">
                <h2>最近のクリック（直近50件）</h2>
                <?php $this->display_recent_clicks(); ?>
            </div>
        </div>
        
        <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-box {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #0073aa;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        </style>
        <?php
    }
    
    /**
     * サマリーを表示
     */
    private function display_summary($start_date) {
        global $wpdb;
        
        if (!isset($this->table_name)) {
            echo '<p>テーブルが初期化されていません。</p>';
            return;
        }
        
        // 総クリック数
        $total_clicks = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE clicked_at >= %s",
            $start_date
        ));
        
        // ユニーククリック数（IP別）
        $unique_clicks = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_ip) FROM {$this->table_name} WHERE clicked_at >= %s",
            $start_date
        ));
        
        // 記事数
        $post_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) FROM {$this->table_name} WHERE clicked_at >= %s AND post_id > 0",
            $start_date
        ));
        
        // サービス数
        $service_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT service) FROM {$this->table_name} WHERE clicked_at >= %s",
            $start_date
        ));
        
        ?>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?php echo number_format(intval($total_clicks)); ?></div>
                <div class="stat-label">総クリック数</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo number_format(intval($unique_clicks)); ?></div>
                <div class="stat-label">ユニーククリック</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo number_format(intval($post_count)); ?></div>
                <div class="stat-label">クリックされた記事数</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo number_format(intval($service_count)); ?></div>
                <div class="stat-label">利用サービス数</div>
            </div>
        </div>
        <?php
    }
    
    /**
     * サービス別統計を表示
     */
    private function display_service_stats($start_date) {
        global $wpdb;
        
        if (!isset($this->table_name)) {
            echo '<p>テーブルが初期化されていません。</p>';
            return;
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT service, COUNT(*) as clicks, COUNT(DISTINCT user_ip) as unique_clicks
             FROM {$this->table_name} 
             WHERE clicked_at >= %s
             GROUP BY service
             ORDER BY clicks DESC",
            $start_date
        ));
        
        if ($results) {
            echo '<table class="widefat striped">';
            echo '<thead><tr><th>サービス</th><th>クリック数</th><th>ユニーククリック</th><th>CTR</th></tr></thead>';
            echo '<tbody>';
            
            $total = array_sum(array_column($results, 'clicks'));
            
            foreach ($results as $row) {
                $percentage = $total > 0 ? round(($row->clicks / $total) * 100, 1) : 0;
                echo '<tr>';
                echo '<td>' . esc_html($row->service) . '</td>';
                echo '<td>' . number_format($row->clicks) . '</td>';
                echo '<td>' . number_format($row->unique_clicks) . '</td>';
                echo '<td>' . $percentage . '%</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<p>データがありません。</p>';
        }
    }
    
    /**
     * 都市別統計を表示
     */
    private function display_city_stats($start_date) {
        global $wpdb;
        
        if (!isset($this->table_name)) {
            echo '<p>テーブルが初期化されていません。</p>';
            return;
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT city, COUNT(*) as clicks, COUNT(DISTINCT service) as services
             FROM {$this->table_name} 
             WHERE clicked_at >= %s
             GROUP BY city
             ORDER BY clicks DESC
             LIMIT 20",
            $start_date
        ));
        
        if ($results) {
            echo '<table class="widefat striped">';
            echo '<thead><tr><th>都市</th><th>クリック数</th><th>利用サービス数</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($results as $row) {
                echo '<tr>';
                echo '<td>' . esc_html($row->city) . '</td>';
                echo '<td>' . number_format($row->clicks) . '</td>';
                echo '<td>' . number_format($row->services) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<p>データがありません。</p>';
        }
    }
    
    /**
     * 記事別統計を表示
     */
    private function display_post_stats($start_date) {
        global $wpdb;
        
        if (!isset($this->table_name)) {
            echo '<p>テーブルが初期化されていません。</p>';
            return;
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, post_title, COUNT(*) as clicks, COUNT(DISTINCT service) as services
             FROM {$this->table_name} 
             WHERE clicked_at >= %s AND post_id > 0
             GROUP BY post_id, post_title
             ORDER BY clicks DESC
             LIMIT 20",
            $start_date
        ));
        
        if ($results) {
            echo '<table class="widefat striped">';
            echo '<thead><tr><th>記事タイトル</th><th>クリック数</th><th>利用サービス数</th><th>アクション</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($results as $row) {
                echo '<tr>';
                echo '<td>' . esc_html($row->post_title) . '</td>';
                echo '<td>' . number_format($row->clicks) . '</td>';
                echo '<td>' . number_format($row->services) . '</td>';
                echo '<td>';
                if (function_exists('get_permalink')) {
                    echo '<a href="' . get_permalink($row->post_id) . '" target="_blank">表示</a>';
                }
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<p>データがありません。</p>';
        }
    }
    
    /**
     * 最近のクリックを表示
     */
    private function display_recent_clicks() {
        global $wpdb;
        
        if (!isset($this->table_name)) {
            echo '<p>テーブルが初期化されていません。</p>';
            return;
        }
        
        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} 
             ORDER BY clicked_at DESC
             LIMIT 50"
        );
        
        if ($results) {
            echo '<table class="widefat striped">';
            echo '<thead><tr><th>日時</th><th>サービス</th><th>都市</th><th>記事</th><th>IPアドレス</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($results as $row) {
                echo '<tr>';
                echo '<td>' . esc_html($row->clicked_at) . '</td>';
                echo '<td>' . esc_html($row->service) . '</td>';
                echo '<td>' . esc_html($row->city) . '</td>';
                echo '<td>' . ($row->post_title ? esc_html($row->post_title) : '-') . '</td>';
                echo '<td>' . esc_html(substr($row->user_ip, 0, -3) . 'xxx') . '</td>'; // IPの一部をマスク
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<p>データがありません。</p>';
        }
    }
}