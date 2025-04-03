<?php
/**
 * 基本モジュール抽象クラス
 * すべてのアフィリエイトモジュールの基本となる抽象クラス
 */
abstract class Base_Affiliate_Module implements Affiliate_Module_Interface {
    /**
     * モジュール名
     * 
     * @var string
     */
    protected $module_name;
    
    /**
     * モジュールの優先度
     * 
     * @var int
     */
    protected $module_priority = 10;
    
    /**
     * このモジュールが指定されたコンテンツに適用可能かチェック
     * 
     * @param string $content 投稿コンテンツ
     * @return boolean このモジュールが適用可能かどうか
     */
    public function is_applicable($content) {
        // デフォルトでは常に適用可能
        // 子クラスで特定のロジックを実装するためにオーバーライドする
        return true;
    }
    
    /**
     * このモジュールの優先度を設定
     * 
     * @param int $priority 優先度
     * @return self インスタンス自身（メソッドチェーン用）
     */
    public function set_priority($priority) {
        $this->module_priority = intval($priority);
        return $this;
    }
    
    /**
     * このモジュールの優先度を取得
     * 
     * @return int 優先度
     */
    public function get_priority() {
        return $this->module_priority;
    }
    
    /**
     * モジュール名を取得
     * 
     * @return string モジュール名
     */
    public function get_module_name() {
        return $this->module_name;
    }
}
