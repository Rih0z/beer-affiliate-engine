<?php
/**
 * セーフモード版設定管理クラス
 */

class Beer_Affiliate_Safe_Settings {
    
    const MENU_SLUG = 'beer-affiliate-safe-settings';
    const OPTION_NAME = 'beer_affiliate_safe_settings';
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Beer Affiliate 設定',
            'Beer Affiliate',
            'manage_options',
            self::MENU_SLUG,
            array($this, 'settings_page'),
            'dashicons-beer',
            30
        );
        
        add_submenu_page(
            self::MENU_SLUG,
            '基本設定',
            '基本設定',
            'manage_options',
            self::MENU_SLUG,
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            self::MENU_SLUG,
            'プログラムID設定',
            'プログラムID設定',
            'manage_options',
            'beer-affiliate-program-ids',
            array($this, 'program_ids_page')
        );
        
        add_submenu_page(
            self::MENU_SLUG,
            '使い方ガイド',
            '使い方ガイド',
            'manage_options',
            'beer-affiliate-guide',
            array($this, 'guide_page')
        );
    }
    
    public function register_settings() {
        // 基本設定
        register_setting(
            'beer_affiliate_safe_settings_group',
            self::OPTION_NAME,
            array($this, 'sanitize_settings')
        );
        
        // プログラムID設定
        register_setting(
            'beer_affiliate_program_ids_group',
            'beer_affiliate_program_ids',
            array($this, 'sanitize_program_ids')
        );
        
        // 楽天設定
        add_settings_section(
            'rakuten_section',
            '楽天アフィリエイト設定',
            array($this, 'rakuten_section_callback'),
            self::MENU_SLUG
        );
        
        add_settings_field(
            'rakuten_affiliate_id',
            'アフィリエイトID',
            array($this, 'rakuten_affiliate_id_callback'),
            self::MENU_SLUG,
            'rakuten_section'
        );
        
        // A8.net設定
        add_settings_section(
            'a8_section',
            'A8.net基本設定',
            array($this, 'a8_section_callback'),
            self::MENU_SLUG
        );
        
        add_settings_field(
            'a8_media_id',
            'メディアID',
            array($this, 'a8_media_id_callback'),
            self::MENU_SLUG,
            'a8_section'
        );
        
        // 個別プログラムID設定
        add_settings_section(
            'program_ids_section',
            '個別プログラムID設定',
            array($this, 'program_ids_section_callback'),
            'beer-affiliate-program-ids'
        );
        
        $programs = array(
            'jtb' => 'JTB国内旅行',
            'yomiuri' => '読売旅行',
            'ikyu' => '一休.comレストラン',
            'jtb_shopping' => 'JTBショッピング',
            'otomoni' => 'Otomoni（クラフトビール定期便）',
            'qatar' => 'カタール航空',
            'oooh' => 'Oooh（ウー）',
            'saily' => 'Saily（海外eSIM）',
            'travelist' => 'Travelist（海外航空券）'
        );
        
        foreach ($programs as $key => $name) {
            add_settings_field(
                $key . '_program_id',
                $name,
                array($this, 'program_id_callback'),
                'beer-affiliate-program-ids',
                'program_ids_section',
                array('key' => $key, 'name' => $name)
            );
        }
    }
    
    public function rakuten_section_callback() {
        echo '<p>楽天アフィリエイトの認証情報を入力してください。<a href="https://affiliate.rakuten.co.jp/" target="_blank">楽天アフィリエイト管理画面</a>から取得できます。</p>';
    }
    
    public function a8_section_callback() {
        echo '<p>A8.netの基本情報を入力してください。<a href="https://www.a8.net/" target="_blank">A8.net管理画面</a>から取得できます。</p>';
    }
    
    public function program_ids_section_callback() {
        echo '<p>各プログラムの個別IDを設定してください。A8.netの管理画面で「プログラム管理」→「参加中プログラム」から各プログラムIDを確認できます。</p>';
        echo '<div class="notice notice-info"><p><strong>プログラムIDの確認方法:</strong><br>';
        echo '1. A8.net管理画面にログイン<br>';
        echo '2. 「プログラム管理」→「参加中プログラム」をクリック<br>';
        echo '3. 各プログラムの「広告リンク」をクリック<br>';
        echo '4. URLに含まれる「s00000xxxxxxx」の部分がプログラムIDです</p></div>';
    }
    
    public function rakuten_affiliate_id_callback() {
        $options = get_option(self::OPTION_NAME);
        $value = isset($options['rakuten_affiliate_id']) ? $options['rakuten_affiliate_id'] : '';
        ?>
        <input type="text" 
               name="<?php echo self::OPTION_NAME; ?>[rakuten_affiliate_id]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text" 
               placeholder="例: 20a2fc9d.5c6c02f2.20a2fc9e.541a36d0" />
        <p class="description">楽天アフィリエイトIDを入力してください。</p>
        <?php
    }
    
    public function a8_media_id_callback() {
        $options = get_option(self::OPTION_NAME);
        $value = isset($options['a8_media_id']) ? $options['a8_media_id'] : '';
        ?>
        <input type="text" 
               name="<?php echo self::OPTION_NAME; ?>[a8_media_id]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text" 
               placeholder="例: a17092772583" />
        <p class="description">A8.netのメディアIDを入力してください。</p>
        <?php
    }
    
    public function program_id_callback($args) {
        $options = get_option('beer_affiliate_program_ids');
        $value = isset($options[$args['key']]) ? $options[$args['key']] : '';
        $default_ids = $this->get_default_program_ids();
        $default_value = isset($default_ids[$args['key']]) ? $default_ids[$args['key']] : '';
        ?>
        <input type="text" 
               name="beer_affiliate_program_ids[<?php echo esc_attr($args['key']); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text" 
               placeholder="<?php echo esc_attr($default_value); ?>" />
        <p class="description">例: <?php echo esc_html($default_value); ?></p>
        <?php
    }
    
    private function get_default_program_ids() {
        return array(
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
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['rakuten_affiliate_id'])) {
            $sanitized['rakuten_affiliate_id'] = sanitize_text_field($input['rakuten_affiliate_id']);
        }
        
        if (isset($input['a8_media_id'])) {
            $sanitized['a8_media_id'] = sanitize_text_field($input['a8_media_id']);
        }
        
        return $sanitized;
    }
    
    public function sanitize_program_ids($input) {
        $sanitized = array();
        
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }
        
        return $sanitized;
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'beer-affiliate') === false) {
            return;
        }
        
        if (file_exists(BEER_AFFILIATE_PLUGIN_DIR . 'assets/css/admin.css')) {
            wp_enqueue_style(
                'beer-affiliate-admin',
                BEER_AFFILIATE_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                BEER_AFFILIATE_VERSION
            );
        }
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Beer Affiliate Engine 基本設定</h1>
            
            <?php settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('beer_affiliate_safe_settings_group');
                do_settings_sections(self::MENU_SLUG);
                submit_button('設定を保存');
                ?>
            </form>
            
            <div class="beer-affiliate-info">
                <h2>設定状況</h2>
                <?php $this->display_config_status(); ?>
            </div>
        </div>
        <?php
    }
    
    public function program_ids_page() {
        ?>
        <div class="wrap">
            <h1>プログラムID設定</h1>
            
            <?php settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('beer_affiliate_program_ids_group');
                do_settings_sections('beer-affiliate-program-ids');
                submit_button('プログラムIDを保存');
                ?>
            </form>
        </div>
        <?php
    }
    
    public function guide_page() {
        ?>
        <div class="wrap">
            <h1>Beer Affiliate Engine 使い方ガイド</h1>
            
            <div class="beer-affiliate-guide">
                <h2>🍺 基本的な使い方</h2>
                
                <h3>1. 初期設定</h3>
                <ol>
                    <li><strong>基本設定</strong>で楽天アフィリエイトIDとA8.netメディアIDを入力</li>
                    <li><strong>プログラムID設定</strong>で各サービスのプログラムIDを設定</li>
                    <li>設定を保存</li>
                </ol>
                
                <h3>2. 記事での使用方法</h3>
                <p>記事内に以下のショートコードを記述してください：</p>
                
                <div class="code-example">
                    <h4>国内都市の例:</h4>
                    <code>[beer_affiliate city="東京"]</code><br>
                    <code>[beer_affiliate city="大阪"]</code><br>
                    <code>[beer_affiliate city="京都"]</code><br>
                    <code>[beer_affiliate city="札幌"]</code>
                </div>
                
                <div class="code-example">
                    <h4>海外都市の例:</h4>
                    <code>[beer_affiliate city="シアトル"]</code><br>
                    <code>[beer_affiliate city="ポートランド"]</code><br>
                    <code>[beer_affiliate city="ミュンヘン"]</code>
                </div>
                
                <h3>3. 対応サービス</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>サービス名</th>
                            <th>種類</th>
                            <th>設定必要</th>
                            <th>説明</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>楽天トラベル</td>
                            <td>楽天</td>
                            <td>アフィリエイトID</td>
                            <td>ホテル・旅館予約</td>
                        </tr>
                        <tr>
                            <td>JTB国内旅行</td>
                            <td>A8.net</td>
                            <td>メディアID + プログラムID</td>
                            <td>国内ツアー・ホテル</td>
                        </tr>
                        <tr>
                            <td>読売旅行</td>
                            <td>A8.net</td>
                            <td>メディアID + プログラムID</td>
                            <td>国内外ツアー</td>
                        </tr>
                        <tr>
                            <td>一休.comレストラン</td>
                            <td>A8.net</td>
                            <td>メディアID + プログラムID</td>
                            <td>レストラン予約</td>
                        </tr>
                        <tr>
                            <td>Otomoni</td>
                            <td>A8.net</td>
                            <td>メディアID + プログラムID</td>
                            <td>クラフトビール定期便</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>4. プログラムIDの取得方法</h3>
                <div class="notice notice-info">
                    <p><strong>A8.netでのプログラムID確認手順:</strong></p>
                    <ol>
                        <li>A8.net管理画面にログイン</li>
                        <li>「プログラム管理」→「参加中プログラム」をクリック</li>
                        <li>対象プログラムの「広告リンク」をクリック</li>
                        <li>表示されるURLの「s00000xxxxxxx」部分がプログラムIDです</li>
                    </ol>
                    <p><strong>例:</strong> https://px.a8.net/svt/ejp?a8mat=<strong>s00000005350001</strong>&a8ejpredirect=...</p>
                </div>
                
                <h3>5. トラブルシューティング</h3>
                <dl>
                    <dt><strong>Q: リンクが表示されない</strong></dt>
                    <dd>A: 基本設定でアフィリエイトIDが正しく設定されているか確認してください。</dd>
                    
                    <dt><strong>Q: A8.netのリンクが正しく動作しない</strong></dt>
                    <dd>A: メディアIDとプログラムIDが両方とも正しく設定されているか確認してください。</dd>
                    
                    <dt><strong>Q: 特定のサービスのリンクが表示されない</strong></dt>
                    <dd>A: そのサービスのプログラムIDが設定されていない可能性があります。プログラムID設定ページで確認してください。</dd>
                </dl>
                
                <h3>6. 収益最大化のコツ</h3>
                <ul>
                    <li><strong>地域性を活用:</strong> その地域ならではのビール情報と旅行を組み合わせる</li>
                    <li><strong>季節を意識:</strong> 時期に応じたビールイベントや旅行プランを紹介</li>
                    <li><strong>体験談を追加:</strong> 実際の訪問体験を含めることで信頼性を高める</li>
                    <li><strong>複数サービス:</strong> 読者の選択肢を増やすため複数のサービスを提示</li>
                </ul>
            </div>
        </div>
        
        <style>
        .beer-affiliate-guide {
            max-width: 800px;
            background: #fff;
            padding: 30px;
            border: 1px solid #e5e5e5;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            margin-top: 20px;
        }
        .beer-affiliate-guide h2 {
            color: #23282d;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        .beer-affiliate-guide h2:first-child {
            margin-top: 0;
        }
        .beer-affiliate-guide h3 {
            color: #0073aa;
            margin-top: 25px;
        }
        .code-example {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #0073aa;
            margin: 15px 0;
        }
        .code-example code {
            background: #e8f4f8;
            padding: 3px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .beer-affiliate-guide dt {
            font-weight: bold;
            margin-top: 15px;
            color: #23282d;
        }
        .beer-affiliate-guide dd {
            margin-left: 20px;
            margin-bottom: 10px;
            color: #555;
        }
        </style>
        <?php
    }
    
    private function display_config_status() {
        $options = get_option(self::OPTION_NAME);
        $program_ids = get_option('beer_affiliate_program_ids');
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>項目</th><th>状態</th><th>説明</th></tr></thead>';
        echo '<tbody>';
        
        // 楽天設定
        $rakuten_status = !empty($options['rakuten_affiliate_id']) ? '✅ 設定済み' : '❌ 未設定';
        echo '<tr><td>楽天アフィリエイトID</td><td>' . $rakuten_status . '</td><td>楽天トラベルのリンク生成に必要</td></tr>';
        
        // A8メディアID
        $a8_status = !empty($options['a8_media_id']) ? '✅ 設定済み' : '❌ 未設定';
        echo '<tr><td>A8.netメディアID</td><td>' . $a8_status . '</td><td>A8.net系サービスのリンク生成に必要</td></tr>';
        
        // プログラムID
        $programs = array(
            'jtb' => 'JTB国内旅行',
            'yomiuri' => '読売旅行',
            'ikyu' => '一休.comレストラン',
            'jtb_shopping' => 'JTBショッピング',
            'otomoni' => 'Otomoni'
        );
        
        foreach ($programs as $key => $name) {
            $program_status = !empty($program_ids[$key]) ? '✅ 設定済み' : '⚠️ 未設定';
            echo '<tr><td>' . $name . '</td><td>' . $program_status . '</td><td>プログラムID設定ページで設定</td></tr>';
        }
        
        echo '</tbody></table>';
    }
}

// 設定クラスを初期化
Beer_Affiliate_Safe_Settings::get_instance();