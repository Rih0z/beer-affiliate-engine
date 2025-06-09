<?php
/**
 * Beer Affiliate Engine 設定管理クラス
 */

class Beer_Affiliate_Settings {
    
    /**
     * 設定ページのスラッグ
     */
    const MENU_SLUG = 'beer-affiliate-settings';
    
    /**
     * オプション名
     */
    const OPTION_NAME = 'beer_affiliate_settings';
    
    /**
     * インスタンス
     */
    private static $instance = null;
    
    /**
     * インスタンスを取得
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * コンストラクタ
     */
    private function __construct() {
        // WordPressが読み込まれている場合のみフックを追加
        if (function_exists('add_action')) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'register_settings'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        }
    }
    
    /**
     * 管理メニューを追加
     */
    public function add_admin_menu() {
        add_menu_page(
            'Beer Affiliate Engine 設定',
            'Beer Affiliate',
            'manage_options',
            self::MENU_SLUG,
            array($this, 'settings_page'),
            'dashicons-beer',
            30
        );
        
        // サブメニュー
        add_submenu_page(
            self::MENU_SLUG,
            'アフィリエイト設定',
            'アフィリエイト設定',
            'manage_options',
            self::MENU_SLUG,
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            self::MENU_SLUG,
            'クリック分析',
            'クリック分析',
            'manage_options',
            'beer-affiliate-analytics',
            array($this, 'analytics_page')
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
    
    /**
     * 設定を登録
     */
    public function register_settings() {
        register_setting(
            'beer_affiliate_settings_group',
            self::OPTION_NAME,
            array($this, 'sanitize_settings')
        );
        
        // 楽天設定セクション
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
        
        add_settings_field(
            'rakuten_application_id',
            'アプリケーションID',
            array($this, 'rakuten_application_id_callback'),
            self::MENU_SLUG,
            'rakuten_section'
        );
        
        // A8.net設定セクション
        add_settings_section(
            'a8_section',
            'A8.net設定',
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
        
        add_settings_field(
            'a8_fixed_urls',
            'A8固定URL設定',
            array($this, 'a8_fixed_urls_callback'),
            self::MENU_SLUG,
            'a8_section'
        );
        
        // 表示設定セクション
        add_settings_section(
            'display_section',
            '表示設定',
            array($this, 'display_section_callback'),
            self::MENU_SLUG
        );
        
        add_settings_field(
            'default_template',
            'デフォルトテンプレート',
            array($this, 'default_template_callback'),
            self::MENU_SLUG,
            'display_section'
        );
        
        add_settings_field(
            'max_links_per_post',
            '記事あたりの最大リンク数',
            array($this, 'max_links_callback'),
            self::MENU_SLUG,
            'display_section'
        );
        
        add_settings_field(
            'revenue_mode',
            '収益最適化モード',
            array($this, 'revenue_mode_callback'),
            self::MENU_SLUG,
            'display_section'
        );
    }
    
    /**
     * 楽天セクションの説明
     */
    public function rakuten_section_callback() {
        echo '<p>楽天アフィリエイトの認証情報を入力してください。<a href="https://affiliate.rakuten.co.jp/" target="_blank">楽天アフィリエイト管理画面</a>から取得できます。</p>';
    }
    
    /**
     * A8セクションの説明
     */
    public function a8_section_callback() {
        echo '<p>A8.netの認証情報を入力してください。<a href="https://www.a8.net/" target="_blank">A8.net管理画面</a>から取得できます。</p>';
    }
    
    /**
     * 表示セクションの説明
     */
    public function display_section_callback() {
        echo '<p>アフィリエイトリンクの表示方法を設定します。</p>';
    }
    
    /**
     * 楽天アフィリエイトID入力フィールド
     */
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
    
    /**
     * 楽天アプリケーションID入力フィールド
     */
    public function rakuten_application_id_callback() {
        $options = get_option(self::OPTION_NAME);
        $value = isset($options['rakuten_application_id']) ? $options['rakuten_application_id'] : '';
        ?>
        <input type="text" 
               name="<?php echo self::OPTION_NAME; ?>[rakuten_application_id]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text" 
               placeholder="例: 1013646616942500290" />
        <p class="description">楽天APIのアプリケーションIDを入力してください。</p>
        <?php
    }
    
    /**
     * A8メディアID入力フィールド
     */
    public function a8_media_id_callback() {
        $options = get_option(self::OPTION_NAME);
        $value = isset($options['a8_media_id']) ? $options['a8_media_id'] : '';
        ?>
        <input type="text" 
               name="<?php echo self::OPTION_NAME; ?>[a8_media_id]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text" 
               placeholder="例: 3UJGPC" />
        <p class="description">A8.netのメディアIDを入力してください。</p>
        <?php
    }
    
    /**
     * A8固定URL設定フィールド
     */
    public function a8_fixed_urls_callback() {
        $options = get_option(self::OPTION_NAME);
        $fixed_urls = isset($options['a8_fixed_urls']) ? $options['a8_fixed_urls'] : array();
        
        // デフォルトのA8プログラムリスト
        $default_programs = array(
            'JTB国内旅行' => 'https://px.a8.net/svt/ejp?a8mat=4530O4+61B8KY+15A4+63WO2',
            '一休.comレストラン' => 'https://px.a8.net/svt/ejp?a8mat=3NJ1WF+CEJ4HE+1OK+NX736',
            '読売旅行' => 'https://px.a8.net/svt/ejp?a8mat=4530O4+5VYC4Y+5KLE+5YRHE',
            'Otomoni クラフトビール定期便' => 'https://px.a8.net/svt/ejp?a8mat=3NJ1WF+D1R12Q+4XM6+5YJRM',
            'トラベル・スタンダード・ジャパン' => 'https://px.a8.net/svt/ejp?a8mat=4530O4+61WO6Q+5LKE+5YJRM',
            'カタール航空' => 'https://px.a8.net/svt/ejp?a8mat=4530O4+64AELU+5NMU+5YJRM',
            'Travelist' => 'https://px.a8.net/svt/ejp?a8mat=4530O4+63OZ02+4XZI+HVFKY',
            'Oooh(ウー)' => 'https://px.a8.net/svt/ejp?a8mat=4530O4+7VZSC2+5OEM+5YRHE',
            'Saily' => 'https://px.a8.net/svt/ejp?a8mat=4530O4+5WJRQQ+5L2C+5YRHE'
        );
        ?>
        <div class="a8-fixed-urls-section">
            <p class="description" style="margin-bottom: 15px;">A8.netプログラムの固定URLを設定します。都市検索を無効にして、設定したURLをそのまま使用します。</p>
            
            <?php foreach ($default_programs as $program_name => $default_url) : 
                $current_url = isset($fixed_urls[$program_name]) ? $fixed_urls[$program_name] : $default_url;
            ?>
            <div class="a8-program-setting" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;"><?php echo esc_html($program_name); ?></label>
                <input type="url" 
                       name="<?php echo self::OPTION_NAME; ?>[a8_fixed_urls][<?php echo esc_attr($program_name); ?>]" 
                       value="<?php echo esc_attr($current_url); ?>" 
                       class="large-text" 
                       style="width: 100%;" />
                <p class="description" style="margin-top: 5px;">このプログラムの固定URLを入力してください。</p>
            </div>
            <?php endforeach; ?>
            
            <div style="background: #f0f8ff; padding: 15px; border-left: 4px solid #0073aa; margin-top: 20px;">
                <h4 style="margin-top: 0;">💡 A8固定URL機能について</h4>
                <ul style="margin-bottom: 0;">
                    <li>都市名による動的検索をせず、設定したURLをそのまま使用します</li>
                    <li>表示ラベルからも「{CITY}」が自動的に除去されます</li>
                    <li>無効なリダイレクト（coreda.jpなど）を防ぐことができます</li>
                    <li>URLを空にすると、そのプログラムは表示されません</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * デフォルトテンプレート選択
     */
    public function default_template_callback() {
        $options = get_option(self::OPTION_NAME);
        $value = isset($options['default_template']) ? $options['default_template'] : 'card';
        ?>
        <select name="<?php echo self::OPTION_NAME; ?>[default_template]">
            <option value="user-friendly" <?php selected($value, 'user-friendly'); ?>>ユーザーフレンドリー（推奨）</option>
            <option value="card" <?php selected($value, 'card'); ?>>カード</option>
            <option value="button" <?php selected($value, 'button'); ?>>ボタン</option>
        </select>
        <p class="description">デフォルトの表示形式を選択してください。</p>
        <?php
    }
    
    /**
     * 最大リンク数
     */
    public function max_links_callback() {
        $options = get_option(self::OPTION_NAME);
        $value = isset($options['max_links_per_post']) ? $options['max_links_per_post'] : 5;
        ?>
        <input type="number" 
               name="<?php echo self::OPTION_NAME; ?>[max_links_per_post]" 
               value="<?php echo esc_attr($value); ?>" 
               min="1" 
               max="10" 
               step="1" />
        <p class="description">1記事あたりに表示する最大リンク数（1-10）</p>
        <?php
    }
    
    /**
     * 収益最適化モード
     */
    public function revenue_mode_callback() {
        $options = get_option(self::OPTION_NAME);
        $value = isset($options['revenue_mode']) ? $options['revenue_mode'] : 'enabled';
        ?>
        <label>
            <input type="checkbox" 
                   name="<?php echo self::OPTION_NAME; ?>[revenue_mode]" 
                   value="enabled" 
                   <?php checked($value, 'enabled'); ?> />
            収益最適化モードを有効にする
        </label>
        <p class="description">チェックすると、高収益が期待できるプログラムを優先的に表示します。</p>
        <?php
    }
    
    /**
     * 設定をサニタイズ
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // テキストフィールド
        $text_fields = array('rakuten_affiliate_id', 'rakuten_application_id', 'a8_media_id');
        foreach ($text_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_text_field($input[$field]);
            }
        }
        
        // A8固定URL設定
        if (isset($input['a8_fixed_urls']) && is_array($input['a8_fixed_urls'])) {
            $sanitized['a8_fixed_urls'] = array();
            foreach ($input['a8_fixed_urls'] as $program_name => $url) {
                if (!empty($url)) {
                    $sanitized_url = sanitize_url($url);
                    // A8.netのURLかチェック
                    if (strpos($sanitized_url, 'px.a8.net') !== false || strpos($sanitized_url, 'rpx.a8.net') !== false) {
                        $sanitized['a8_fixed_urls'][sanitize_text_field($program_name)] = $sanitized_url;
                    }
                }
            }
        }
        
        // 選択フィールド
        if (isset($input['default_template'])) {
            $valid_templates = array('button', 'card', 'revenue-optimized');
            if (in_array($input['default_template'], $valid_templates)) {
                $sanitized['default_template'] = $input['default_template'];
            }
        }
        
        // 数値フィールド
        if (isset($input['max_links_per_post'])) {
            $sanitized['max_links_per_post'] = min(10, max(1, intval($input['max_links_per_post'])));
        }
        
        // チェックボックス
        $sanitized['revenue_mode'] = isset($input['revenue_mode']) ? 'enabled' : 'disabled';
        
        // 設定をファイルに保存（GitHubには公開しない）
        $this->save_affiliate_config($sanitized);
        
        return $sanitized;
    }
    
    /**
     * アフィリエイト設定をファイルに保存
     */
    private function save_affiliate_config($settings) {
        $config_file = BEER_AFFILIATE_PLUGIN_DIR . 'affiliate-config.json';
        
        $config = array(
            '楽天トラベル' => array(
                'affiliate_id' => isset($settings['rakuten_affiliate_id']) ? $settings['rakuten_affiliate_id'] : '',
                'application_id' => isset($settings['rakuten_application_id']) ? $settings['rakuten_application_id'] : ''
            ),
            'a8' => array(
                'media_id' => isset($settings['a8_media_id']) ? $settings['a8_media_id'] : ''
            )
        );
        
        file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 管理画面スクリプトを読み込み
     */
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
    }
    
    /**
     * 設定ページ
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Beer Affiliate Engine 設定</h1>
            
            <?php settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('beer_affiliate_settings_group');
                do_settings_sections(self::MENU_SLUG);
                submit_button();
                ?>
            </form>
            
            <div class="beer-affiliate-info">
                <h2>プログラム参加状況</h2>
                <?php $this->display_program_status(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * プログラム参加状況を表示
     */
    private function display_program_status() {
        $programs = array(
            '楽天トラベル' => array('status' => '参加中', 'type' => '楽天', 'commission' => '1%'),
            'トラベル・スタンダード・ジャパン' => array('status' => '参加中', 'type' => 'A8', 'commission' => '問い合わせ2000円+実施5000円'),
            '読売旅行' => array('status' => '参加中', 'type' => 'A8', 'commission' => '2%'),
            'JTB国内旅行' => array('status' => '参加中', 'type' => 'A8', 'commission' => '0.8%'),
            'JTBショッピング' => array('status' => '参加中', 'type' => 'A8', 'commission' => '5%'),
            '一休.comレストラン' => array('status' => '参加中', 'type' => 'A8', 'commission' => '1%'),
            'Otomoni' => array('status' => '参加中', 'type' => 'A8', 'commission' => '新規定期申込2000円'),
            'カタール航空' => array('status' => '参加中', 'type' => 'A8', 'commission' => '1.5%'),
            'Oooh(ウー)' => array('status' => '参加中', 'type' => 'A8', 'commission' => '10%'),
            'Saily' => array('status' => '参加中', 'type' => 'A8', 'commission' => '10%'),
            'Travelist' => array('status' => '参加中', 'type' => 'A8', 'commission' => '300円')
        );
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>プログラム名</th>
                    <th>タイプ</th>
                    <th>報酬</th>
                    <th>状態</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($programs as $name => $info) : ?>
                <tr>
                    <td><?php echo esc_html($name); ?></td>
                    <td><?php echo esc_html($info['type']); ?></td>
                    <td><?php echo esc_html($info['commission']); ?></td>
                    <td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php echo esc_html($info['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * 分析ページ
     */
    public function analytics_page() {
        if (class_exists('Beer_Affiliate_Analytics')) {
            Beer_Affiliate_Analytics::display_page();
        }
    }
    
    /**
     * ヘルプページ
     */
    public function help_page() {
        ?>
        <div class="wrap">
            <h1>Beer Affiliate Engine 使い方ガイド</h1>
            
            <div class="beer-affiliate-help">
                <h2>🍺 プラグインの概要</h2>
                <p>Beer Affiliate Engineは、クラフトビールブログの記事内に自動的にアフィリエイトリンクを生成するWordPressプラグインです。</p>
                
                <h2>📝 基本的な使い方</h2>
                <ol>
                    <li><strong>記事を書く</strong>：通常通りビールに関する記事を作成します</li>
                    <li><strong>地名を含める</strong>：「シアトル」「東京」などの地名を記事に含めます</li>
                    <li><strong>自動生成</strong>：プラグインが自動的に関連するアフィリエイトリンクを生成します</li>
                </ol>
                
                <h2>🎯 収益最適化のヒント</h2>
                <div class="tips">
                    <h3>1. 高収益プログラムを活用</h3>
                    <ul>
                        <li><strong>トラベル・スタンダード・ジャパン</strong>：問い合わせで2000円、実施で追加5000円（最大7000円）</li>
                        <li><strong>Otomoni</strong>：クラフトビール定期便の新規申込で2000円</li>
                        <li><strong>読売旅行</strong>：旅行代金の2%（10万円なら2000円）</li>
                    </ul>
                    
                    <h3>2. 文脈に応じたリンク生成</h3>
                    <ul>
                        <li><strong>旅行記事</strong>：「訪問」「旅行」「ツアー」などのキーワードで旅行系リンクを優先</li>
                        <li><strong>グルメ記事</strong>：「レストラン」「ランチ」などで飲食予約系リンクを表示</li>
                        <li><strong>商品紹介</strong>：「購入」「お取り寄せ」でショッピング系リンクを生成</li>
                    </ul>
                    
                    <h3>3. 効果的なキーワード</h3>
                    <ul>
                        <li>地名：「シアトル」「ポートランド」「東京」「京都」など</li>
                        <li>ビール用語：「IPA」「ブルワリー」「クラフトビール」など</li>
                        <li>行動：「訪れる」「飲む」「購入する」「予約する」など</li>
                    </ul>
                </div>
                
                <h2>⚙️ 設定のポイント</h2>
                <ul>
                    <li><strong>アフィリエイトID</strong>：必ず正しいIDを入力してください</li>
                    <li><strong>最大リンク数</strong>：記事が長い場合は5-7個、短い場合は3個程度が適切です</li>
                    <li><strong>収益最適化モード</strong>：ONにすると高収益プログラムを優先表示します</li>
                </ul>
                
                <h2>📊 分析機能</h2>
                <p>「クリック分析」ページで以下の情報を確認できます：</p>
                <ul>
                    <li>どのサービスがクリックされているか</li>
                    <li>どの記事からのクリックが多いか</li>
                    <li>どの地域への関心が高いか</li>
                </ul>
                
                <h2>🚀 収益を最大化するコツ</h2>
                <div class="revenue-tips">
                    <p><strong>1. 季節性を意識する</strong></p>
                    <ul>
                        <li>夏：ビアガーデン、夏祭り関連の記事</li>
                        <li>秋：オクトーバーフェスト、収穫祭</li>
                        <li>冬：クリスマスマーケット、温泉旅行</li>
                    </ul>
                    
                    <p><strong>2. 具体的な行動を促す</strong></p>
                    <ul>
                        <li>「予約する」「申し込む」などの行動喚起</li>
                        <li>期間限定キャンペーンの紹介</li>
                        <li>実体験に基づくレビュー</li>
                    </ul>
                    
                    <p><strong>3. 継続収益を狙う</strong></p>
                    <ul>
                        <li>Otomoniの定期便は継続的な収益源</li>
                        <li>リピート率の高い旅行予約</li>
                    </ul>
                </div>
                
                <h2>❓ よくある質問</h2>
                <dl>
                    <dt>Q: リンクが表示されない</dt>
                    <dd>A: アフィリエイトIDが正しく設定されているか確認してください。また、記事内に地名やビール関連キーワードが含まれているか確認してください。</dd>
                    
                    <dt>Q: どのテンプレートを使うべき？</dt>
                    <dd>A: 「収益最適化」テンプレートが最も効果的です。高収益プログラムを優先的に表示します。</dd>
                    
                    <dt>Q: クリックされているか確認したい</dt>
                    <dd>A: 「クリック分析」ページで詳細な統計を確認できます。</dd>
                </dl>
                
                <h2>📞 サポート</h2>
                <p>問題が発生した場合は、以下を確認してください：</p>
                <ul>
                    <li>WordPressとPHPのバージョンが要件を満たしているか</li>
                    <li>他のプラグインとの競合がないか</li>
                    <li>エラーログに詳細情報が記録されているか</li>
                </ul>
            </div>
        </div>
        
        <style>
        .beer-affiliate-help {
            max-width: 800px;
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .beer-affiliate-help h2 {
            color: #23282d;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        .beer-affiliate-help h3 {
            color: #0073aa;
            margin-top: 20px;
        }
        .tips, .revenue-tips {
            background: #f0f8ff;
            padding: 15px;
            border-left: 4px solid #0073aa;
            margin: 15px 0;
        }
        .beer-affiliate-help dt {
            font-weight: bold;
            margin-top: 15px;
        }
        .beer-affiliate-help dd {
            margin-left: 20px;
            margin-bottom: 10px;
        }
        </style>
        <?php
    }
}

// 設定クラスを初期化
Beer_Affiliate_Settings::get_instance();