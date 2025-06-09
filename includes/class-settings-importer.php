<?php
/**
 * アフィリエイト設定インポートクラス
 * 
 * @package Beer_Affiliate_Engine
 */

class Beer_Affiliate_Settings_Importer {
    
    /**
     * 初期化
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_beer_affiliate_import_settings', array($this, 'handle_import'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
    }
    
    /**
     * 管理メニューを追加
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Beer Affiliate 設定インポート',
            'Beer Affiliate Import',
            'manage_options',
            'beer-affiliate-import',
            array($this, 'render_import_page')
        );
    }
    
    /**
     * インポートページを表示
     */
    public function render_import_page() {
        ?>
        <div class="wrap">
            <h1>Beer Affiliate Engine - 設定インポート</h1>
            
            <div class="card">
                <h2>JSONファイルから設定をインポート</h2>
                <p>affiliate-config.jsonファイルをアップロードして、アフィリエイトIDを一括設定できます。</p>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field('beer_affiliate_import', 'beer_affiliate_import_nonce'); ?>
                    <input type="hidden" name="action" value="beer_affiliate_import_settings">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="import_file">設定ファイル</label>
                            </th>
                            <td>
                                <input type="file" name="import_file" id="import_file" accept=".json" required>
                                <p class="description">
                                    JSONファイルを選択してください。
                                    <a href="<?php echo plugins_url('affiliate-config-sample.json', dirname(__FILE__)); ?>" download>サンプルファイルをダウンロード</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('設定をインポート', 'primary', 'submit', true); ?>
                </form>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>現在の設定</h2>
                <?php $this->display_current_settings(); ?>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>設定ファイルの作り方</h2>
                <ol>
                    <li><a href="<?php echo plugins_url('affiliate-config-sample.json', dirname(__FILE__)); ?>" download>サンプルファイル</a>をダウンロード</li>
                    <li>テキストエディタで開き、YOUR_で始まる部分を実際のアフィリエイトIDに置き換え</li>
                    <li>ファイルを保存して、上のフォームからアップロード</li>
                </ol>
                
                <h3>設定ファイルの例</h3>
                <pre style="background: #f5f5f5; padding: 10px; overflow-x: auto;">
{
  "affiliate_ids": {
    "楽天トラベル": {
      "affiliate_id": "20a2fc9d.5c6c02f2.20a2fc9e.541a36d0"
    },
    "JTB国内旅行": {
      "affiliate_id": "3UJGPC",
      "program_id": "5350"
    }
  }
}
                </pre>
            </div>
        </div>
        <?php
    }
    
    /**
     * 現在の設定を表示
     */
    private function display_current_settings() {
        $services = array(
            '楽天トラベル' => array('id'),
            'JTB国内旅行' => array('id', 'program_id'),
            'HIS' => array('id', 'program_id'),
            'トラベル・スタンダード・ジャパン' => array('id', 'program_id'),
            'JTBショッピング' => array('id', 'program_id'),
            'カタール航空' => array('id', 'program_id'),
            'Travelist' => array('id', 'program_id'),
            'Oooh(ウー)' => array('id', 'program_id'),
            'Saily' => array('id', 'program_id')
        );
        
        echo '<table class="widefat">';
        echo '<thead><tr><th>サービス</th><th>アフィリエイトID</th><th>プログラムID</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($services as $service => $fields) {
            echo '<tr>';
            echo '<td>' . esc_html($service) . '</td>';
            
            $affiliate_id = get_option('beer_affiliate_' . sanitize_title($service) . '_id', '');
            echo '<td>' . ($affiliate_id ? '設定済み' : '<span style="color: #999;">未設定</span>') . '</td>';
            
            if (in_array('program_id', $fields)) {
                $program_id = get_option('beer_affiliate_' . sanitize_title($service) . '_program_id', '');
                echo '<td>' . ($program_id ? '設定済み' : '<span style="color: #999;">未設定</span>') . '</td>';
            } else {
                echo '<td>-</td>';
            }
            
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * インポート処理
     */
    public function handle_import() {
        // 権限チェック
        if (!current_user_can('manage_options')) {
            wp_die('権限がありません');
        }
        
        // nonceチェック
        if (!isset($_POST['beer_affiliate_import_nonce']) || 
            !wp_verify_nonce($_POST['beer_affiliate_import_nonce'], 'beer_affiliate_import')) {
            wp_die('不正なリクエストです');
        }
        
        // ファイルチェック
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            set_transient('beer_affiliate_import_error', 'ファイルのアップロードに失敗しました', 30);
            wp_redirect(admin_url('options-general.php?page=beer-affiliate-import'));
            exit;
        }
        
        // JSONファイルを読み込み
        $json_content = file_get_contents($_FILES['import_file']['tmp_name']);
        $config = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            set_transient('beer_affiliate_import_error', 'JSONファイルの形式が正しくありません', 30);
            wp_redirect(admin_url('options-general.php?page=beer-affiliate-import'));
            exit;
        }
        
        // 設定をインポート
        $imported_count = 0;
        if (isset($config['affiliate_ids'])) {
            foreach ($config['affiliate_ids'] as $service => $settings) {
                $option_base = 'beer_affiliate_' . sanitize_title($service);
                
                // アフィリエイトID
                if (isset($settings['affiliate_id']) && 
                    !empty($settings['affiliate_id']) && 
                    strpos($settings['affiliate_id'], 'YOUR_') !== 0) {
                    update_option($option_base . '_id', sanitize_text_field($settings['affiliate_id']));
                    $imported_count++;
                }
                
                // プログラムID
                if (isset($settings['program_id']) && 
                    !empty($settings['program_id']) && 
                    strpos($settings['program_id'], 'YOUR_') !== 0) {
                    update_option($option_base . '_program_id', sanitize_text_field($settings['program_id']));
                    $imported_count++;
                }
            }
        }
        
        // 成功メッセージ
        set_transient('beer_affiliate_import_success', $imported_count . '個の設定をインポートしました', 30);
        wp_redirect(admin_url('options-general.php?page=beer-affiliate-import'));
        exit;
    }
    
    /**
     * 管理画面通知を表示
     */
    public function show_admin_notices() {
        // エラーメッセージ
        $error = get_transient('beer_affiliate_import_error');
        if ($error) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . esc_html($error) . '</p>';
            echo '</div>';
            delete_transient('beer_affiliate_import_error');
        }
        
        // 成功メッセージ
        $success = get_transient('beer_affiliate_import_success');
        if ($success) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html($success) . '</p>';
            echo '</div>';
            delete_transient('beer_affiliate_import_success');
        }
    }
    
    /**
     * 設定をエクスポート（将来の拡張用）
     */
    public function export_settings() {
        $services = array(
            '楽天トラベル',
            'JTB国内旅行',
            'HIS',
            'トラベル・スタンダード・ジャパン',
            'JTBショッピング',
            'カタール航空',
            'Travelist',
            'Oooh(ウー)',
            'Saily'
        );
        
        $export = array(
            'version' => '1.0',
            'exported_at' => current_time('mysql'),
            'affiliate_ids' => array()
        );
        
        foreach ($services as $service) {
            $option_base = 'beer_affiliate_' . sanitize_title($service);
            $settings = array();
            
            $affiliate_id = get_option($option_base . '_id', '');
            if (!empty($affiliate_id)) {
                $settings['affiliate_id'] = $affiliate_id;
            }
            
            $program_id = get_option($option_base . '_program_id', '');
            if (!empty($program_id)) {
                $settings['program_id'] = $program_id;
            }
            
            if (!empty($settings)) {
                $export['affiliate_ids'][$service] = $settings;
            }
        }
        
        return $export;
    }
}