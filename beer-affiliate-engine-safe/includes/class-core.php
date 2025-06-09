<?php
/**
 * プラグインのコア機能を管理するクラス
 */
class Beer_Affiliate_Core {
    /**
     * モジュールマネージャーインスタンス
     * 
     * @var Affiliate_Module_Manager
     */
    private $module_manager;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // 必要な初期化をここに追加
    }
    
    /**
     * プラグインを初期化
     */
    public function init() {
        // モジュールマネージャーをロード
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-module-manager.php';
        $this->module_manager = new Affiliate_Module_Manager();
        
        // 旅行モジュールを登録
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-module.php';
        $travel_module = new Travel_Module();
        $this->module_manager->register_module($travel_module);
        
        // ユーザーフレンドリー版の旅行モジュールを優先的に登録
        if (file_exists(BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-module-v2.php')) {
            require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-module-v2.php';
            $travel_v2_module = new Travel_Module_V2();
            $this->module_manager->register_module($travel_v2_module);
        }
        
        // 他のモジュールが登録できるようにするフック
        do_action('beer_affiliate_register_modules', $this->module_manager);
        
        // 設定インポート機能をロード（管理画面のみ）
        if (is_admin()) {
            require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-settings-importer.php';
            new Beer_Affiliate_Settings_Importer();
        }
        
        // 分析機能を初期化
        new Beer_Affiliate_Analytics();
    }
    
    /**
     * コンテンツを処理してアフィリエイトリンクを生成
     * 
     * @param string $content 投稿コンテンツ
     * @param array $args 引数
     * @return string HTML出力
     */
    public function process_content($content, $args = array()) {
        // モジュールマネージャーが初期化されていることを確認
        if (null === $this->module_manager) {
            $this->init();
        }
        
        // 処理前にコンテンツをフィルタリング
        $content = apply_filters('beer_affiliate_before_analysis', $content, get_the_ID());
        
        // このコンテンツに適用可能なモジュールを取得
        $modules = $this->module_manager->get_applicable_modules($content);
        
        if (empty($modules)) {
            return '';
        }
        
        // 表示するリンクの最大数を設定
        $max_links = isset($args['max_links']) ? intval($args['max_links']) : 2;
        
        // テンプレートタイプを設定
        $template_type = isset($args['template']) ? $args['template'] : 'card';
        
        // 各モジュールを処理して出力を収集
        $module_outputs = array();
        
        foreach ($modules as $module) {
            // キーワードを抽出
            $keywords = $module->extract_keywords($content);
            
            // キーワードをフィルタリング
            $keywords = apply_filters('beer_affiliate_keywords', $keywords, get_the_ID(), $module->get_module_name());
            
            if (empty($keywords)) {
                continue;
            }
            
            // 最大リンク数に制限
            $keywords = array_slice($keywords, 0, $max_links);
            
            // リンクを生成
            $links = $module->generate_links($keywords);
            
            // リンクをフィルタリング
            $links = apply_filters('beer_affiliate_links', $links, $keywords, $module->get_module_name());
            
            if (empty($links)) {
                continue;
            }
            
            // 表示テンプレートを取得
            $output = $module->get_display_template($links, $template_type);
            
            // 出力をフィルタリング
            $output = apply_filters('beer_affiliate_before_display', $output, $links, $template_type);
            
            $module_outputs[] = array(
                'priority' => $module->get_priority(),
                'output' => $output
            );
        }
        
        // 優先度順に出力をソート
        usort($module_outputs, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        // 出力を結合
        $final_output = '';
        foreach ($module_outputs as $output) {
            $final_output .= $output['output'];
        }
        
        return $final_output;
    }
}
