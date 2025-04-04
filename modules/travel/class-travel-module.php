<?php
/**
 * 旅行アフィリエイトモジュール
 * 記事内の地域名を検出して旅行アフィリエイトリンクを生成
 */
class Travel_Module extends Base_Affiliate_Module {
    /**
     * コンテンツアナライザ
     * 
     * @var Travel_Content_Analyzer
     */
    private $content_analyzer;
    
    /**
     * リンクジェネレータ
     * 
     * @var Travel_Link_Generator
     */
    private $link_generator;
    
    /**
     * ディスプレイマネージャ
     * 
     * @var Travel_Display_Manager
     */
    private $display_manager;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        $this->module_name = 'travel';
        $this->set_priority(10); // 旅行モジュールは高い優先度
        
        // 依存クラスの読み込み
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-content-analyzer.php';
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-link-generator.php';
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/class-travel-display-manager.php';
        
        // 依存クラスのインスタンス化
        $this->content_analyzer = new Travel_Content_Analyzer();
        $this->link_generator = new Travel_Link_Generator();
        $this->display_manager = new Travel_Display_Manager();
    }
    
    /**
     * このモジュールが指定されたコンテンツに適用可能かチェック
     * 
     * @param string $content 投稿コンテンツ
     * @return boolean このモジュールが適用可能かどうか
     */
    public function is_applicable($content) {
        // 地域名が1つ以上見つかれば適用可能と判断
        $keywords = $this->content_analyzer->analyze($content);
        return !empty($keywords);
    }
    
    /**
     * コンテンツからキーワード（地域名）を抽出
     * 
     * @param string $content 投稿コンテンツ
     * @return array 抽出された地域名の配列
     */
    public function extract_keywords($content) {
        // コンテンツ解析エンジンを使用
        return $this->content_analyzer->analyze($content);
    }
    
    /**
     * 抽出された地域名に基づいてアフィリエイトリンクを生成
     * 
     * @param array $keywords 地域名の配列
     * @return array 生成されたリンクの配列
     */
    public function generate_links($keywords) {
        // 空の場合は空配列を返す
        if (empty($keywords)) {
            return array();
        }
        
        $links = array();
        
        foreach ($keywords as $city) {
            // 各地域名に対応するリンクを生成
            $city_links = $this->link_generator->generate($city);
            
            if (!empty($city_links)) {
                $links[] = array(
                    'city' => $city,
                    'links' => $city_links
                );
            }
        }
        
        return $links;
    }
    
    /**
     * 生成されたリンクの表示テンプレートを取得
     * 
     * @param array $links 生成されたリンクの配列
     * @param string $template_type 使用するテンプレートタイプ
     * @return string HTML出力
     */
    public function get_display_template($links, $template_type = 'card') {
        // 空の場合は空文字列を返す
        if (empty($links)) {
            return '';
        }
        
        // ディスプレイマネージャを使用してHTMLを生成
        return $this->display_manager->render($links, $template_type);
    }
}
