<?php
/**
 * モジュールインターフェース
 * すべてのアフィリエイトモジュールが実装すべきインターフェース
 */
interface Affiliate_Module_Interface {
    /**
     * コンテンツからキーワードを抽出
     * 
     * @param string $content 投稿コンテンツ
     * @return array 抽出されたキーワードの配列
     */
    public function extract_keywords($content);
    
    /**
     * 抽出されたキーワードに基づいてアフィリエイトリンクを生成
     * 
     * @param array $keywords キーワードの配列
     * @return array 生成されたリンクの配列
     */
    public function generate_links($keywords);
    
    /**
     * 生成されたリンクの表示テンプレートを取得
     * 
     * @param array $links 生成されたリンクの配列
     * @param string $template_type 使用するテンプレートタイプ
     * @return string HTML出力
     */
    public function get_display_template($links, $template_type = 'card');
    
    /**
     * このモジュールが指定されたコンテンツに適用可能かチェック
     * 
     * @param string $content 投稿コンテンツ
     * @return boolean このモジュールが適用可能かどうか
     */
    public function is_applicable($content);
    
    /**
     * このモジュールの優先度を取得
     * 
     * @return int 優先度
     */
    public function get_priority();
    
    /**
     * このモジュールの優先度を設定
     * 
     * @param int $priority 優先度
     * @return self インスタンス自身（メソッドチェーン用）
     */
    public function set_priority($priority);
}
