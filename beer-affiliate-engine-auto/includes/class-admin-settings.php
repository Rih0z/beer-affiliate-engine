<?php
/**
 * 管理画面設定クラス
 */

class Beer_Affiliate_Admin_Settings {
    
    const MENU_SLUG = 'beer-affiliate-settings';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_beer_affiliate_save_program', array($this, 'ajax_save_program'));
        add_action('wp_ajax_beer_affiliate_delete_program', array($this, 'ajax_delete_program'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Beer Affiliate Engine',
            'Beer Affiliate',
            'manage_options',
            self::MENU_SLUG,
            array($this, 'settings_page'),
            'dashicons-beer',
            30
        );
        
        add_submenu_page(
            self::MENU_SLUG,
            'プログラム管理',
            'プログラム管理',
            'manage_options',
            self::MENU_SLUG,
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            self::MENU_SLUG,
            '使い方',
            '使い方',
            'manage_options',
            'beer-affiliate-help',
            array($this, 'help_page')
        );
    }
    
    public function register_settings() {
        register_setting('beer_affiliate_settings', 'beer_affiliate_programs');
    }
    
    public function settings_page() {
        $programs = get_option('beer_affiliate_programs', array());
        ?>
        <div class="wrap">
            <h1>Beer Affiliate Engine - プログラム管理</h1>
            
            <div class="beer-affiliate-admin">
                <div class="program-list">
                    <h2>登録済みプログラム</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>プログラム名</th>
                                <th>タイプ</th>
                                <th>ラベル</th>
                                <th>状態</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="program-list-body">
                            <?php foreach ($programs as $key => $program) : ?>
                            <tr data-program-key="<?php echo esc_attr($key); ?>">
                                <td><?php echo esc_html($program['name']); ?></td>
                                <td><?php echo esc_html($program['type']); ?></td>
                                <td><?php echo esc_html($program['label']); ?></td>
                                <td>
                                    <?php if ($program['enabled']) : ?>
                                        <span class="dashicons dashicons-yes" style="color: #46b450;"></span> 有効
                                    <?php else : ?>
                                        <span class="dashicons dashicons-no" style="color: #dc3232;"></span> 無効
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="button edit-program" data-program='<?php echo esc_attr(json_encode($program)); ?>' data-key="<?php echo esc_attr($key); ?>">編集</button>
                                    <button class="button delete-program" data-key="<?php echo esc_attr($key); ?>">削除</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="add-program-form">
                    <h2>新規プログラム追加</h2>
                    <form id="add-program-form">
                        <table class="form-table">
                            <tr>
                                <th><label for="program_name">プログラム名</label></th>
                                <td><input type="text" id="program_name" name="name" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th><label for="program_type">タイプ</label></th>
                                <td>
                                    <select id="program_type" name="type" required>
                                        <option value="rakuten">楽天</option>
                                        <option value="a8">A8.net</option>
                                        <option value="custom">カスタム</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="url_template">URLテンプレート</label></th>
                                <td>
                                    <textarea id="url_template" name="url_template" class="large-text" rows="3" required></textarea>
                                    <p class="description">
                                        利用可能な変数: {CITY}, {AFFILIATE_ID}, {APPLICATION_ID}, {PROGRAM_ID}, {MEDIA_ID}<br>
                                        例: https://example.com/search?city={CITY}&aid={AFFILIATE_ID}
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="label">表示ラベル</label></th>
                                <td>
                                    <input type="text" id="label" name="label" class="regular-text" required>
                                    <p class="description">{CITY}を使って都市名を挿入できます</p>
                                </td>
                            </tr>
                            <tr class="rakuten-fields">
                                <th><label for="affiliate_id">アフィリエイトID</label></th>
                                <td><input type="text" id="affiliate_id" name="affiliate_id" class="regular-text"></td>
                            </tr>
                            <tr class="rakuten-fields">
                                <th><label for="application_id">アプリケーションID</label></th>
                                <td><input type="text" id="application_id" name="application_id" class="regular-text"></td>
                            </tr>
                            <tr class="a8-fields" style="display:none;">
                                <th><label for="program_id">プログラムID</label></th>
                                <td><input type="text" id="program_id" name="program_id" class="regular-text"></td>
                            </tr>
                            <tr class="a8-fields" style="display:none;">
                                <th><label for="media_id">メディアID</label></th>
                                <td><input type="text" id="media_id" name="media_id" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="enabled">有効化</label></th>
                                <td>
                                    <input type="checkbox" id="enabled" name="enabled" value="1" checked>
                                    <label for="enabled">このプログラムを有効にする</label>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button button-primary" value="プログラムを追加">
                            <input type="hidden" id="edit_key" name="edit_key" value="">
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function help_page() {
        ?>
        <div class="wrap">
            <h1>Beer Affiliate Engine - 使い方</h1>
            
            <div class="beer-affiliate-help">
                <h2>🍺 プラグインの仕組み</h2>
                <p>このプラグインは、ビールに関する記事内の地域名を自動的に検出し、その地域に関連するアフィリエイトリンクを記事の最後に追加します。</p>
                
                <h3>自動検出される地域</h3>
                <h4>国内都市</h4>
                <p>東京、大阪、京都、札幌、福岡、横浜、名古屋、神戸、仙台、金沢、広島、那覇など</p>
                
                <h4>海外都市（ビール関連）</h4>
                <p>シアトル、ポートランド、サンディエゴ、ミュンヘン、ベルリン、プラハ、ブリュッセル、ダブリン、アムステルダムなど</p>
                
                <h3>プログラムの追加方法</h3>
                <ol>
                    <li>「プログラム管理」ページで必要事項を入力</li>
                    <li>URLテンプレートに変数を使って動的なURLを作成</li>
                    <li>有効化してプログラムを保存</li>
                </ol>
                
                <h3>URLテンプレートの変数</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>変数</th>
                            <th>説明</th>
                            <th>例</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{CITY}</td>
                            <td>検出された都市名</td>
                            <td>東京、ミュンヘンなど</td>
                        </tr>
                        <tr>
                            <td>{AFFILIATE_ID}</td>
                            <td>アフィリエイトID（楽天）</td>
                            <td>20a2fc9d.5c6c02f2...</td>
                        </tr>
                        <tr>
                            <td>{APPLICATION_ID}</td>
                            <td>アプリケーションID（楽天）</td>
                            <td>1013646616942500290</td>
                        </tr>
                        <tr>
                            <td>{PROGRAM_ID}</td>
                            <td>プログラムID（A8.net）</td>
                            <td>s00000005350001</td>
                        </tr>
                        <tr>
                            <td>{MEDIA_ID}</td>
                            <td>メディアID（A8.net）</td>
                            <td>a17092772583</td>
                        </tr>
                        <tr>
                            <td>{COUNTRY}</td>
                            <td>国名（海外都市の場合）</td>
                            <td>アメリカ、ドイツなど</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>プログラム例</h3>
                <h4>楽天トラベル</h4>
                <pre>
URL: https://travel.rakuten.co.jp/hotel/search/?f_area={CITY}&f_keyword={CITY}+クラフトビール&f_affiliate_id={AFFILIATE_ID}
ラベル: 楽天トラベルで{CITY}のホテルを探す
                </pre>
                
                <h4>A8.net（JTB）</h4>
                <pre>
URL: https://px.a8.net/svt/ejp?a8mat={PROGRAM_ID}&a8ejpredirect=https://www.jtb.co.jp/kokunai/hotel/{CITY}/
ラベル: JTBで{CITY}のホテルを予約
                </pre>
                
                <h3>トラブルシューティング</h3>
                <dl>
                    <dt>リンクが表示されない</dt>
                    <dd>記事内にビール関連のキーワードと地域名が含まれているか確認してください。</dd>
                    
                    <dt>特定のプログラムが動作しない</dt>
                    <dd>URLテンプレートが正しく設定されているか、必要なIDが入力されているか確認してください。</dd>
                </dl>
            </div>
        </div>
        <?php
    }
    
    public function ajax_save_program() {
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'beer_affiliate_admin')) {
            wp_die();
        }
        
        $program = $_POST['program'];
        $edit_key = $_POST['edit_key'];
        
        $programs = get_option('beer_affiliate_programs', array());
        
        if (empty($edit_key)) {
            // 新規追加
            $key = sanitize_title($program['name']);
            $programs[$key] = $program;
        } else {
            // 編集
            $programs[$edit_key] = $program;
        }
        
        update_option('beer_affiliate_programs', $programs);
        
        wp_send_json_success();
    }
    
    public function ajax_delete_program() {
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'beer_affiliate_admin')) {
            wp_die();
        }
        
        $key = $_POST['key'];
        
        $programs = get_option('beer_affiliate_programs', array());
        unset($programs[$key]);
        
        update_option('beer_affiliate_programs', $programs);
        
        wp_send_json_success();
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'beer-affiliate') === false) {
            return;
        }
        
        wp_enqueue_style(
            'beer-affiliate-admin',
            BEER_AFFILIATE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            BEER_AFFILIATE_VERSION
        );
        
        wp_enqueue_script(
            'beer-affiliate-admin',
            BEER_AFFILIATE_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            BEER_AFFILIATE_VERSION,
            true
        );
        
        wp_localize_script('beer-affiliate-admin', 'beer_affiliate_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('beer_affiliate_admin')
        ));
    }
}