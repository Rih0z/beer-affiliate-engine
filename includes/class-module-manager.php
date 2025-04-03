<?php
/**
 * モジュールマネージャークラス
 * モジュールの登録や選択を管理
 */
class Affiliate_Module_Manager {
    /**
     * 登録済みモジュール
     * 
     * @var array
     */
    private $modules = array();
    
    /**
     * 新しいモジュールを登録
     * 
     * @param Affiliate_Module_Interface $module モジュールインスタンス
     * @return boolean モジュールが登録されたかどうか
     */
    public function register_module($module) {
        if ($module instanceof Affiliate_Module_Interface) {
            $this->modules[] = $module;
            return true;
        }
        return false;
    }
    
    /**
     * 指定されたコンテンツに適用可能なモジュールを取得
     * 
     * @param string $content 投稿コンテンツ
     * @return array 適用可能なモジュールの配列
     */
    public function get_applicable_modules($content) {
        $applicable = array();
        
        foreach ($this->modules as $module) {
            if ($module->is_applicable($content)) {
                $applicable[] = $module;
            }
        }
        
        // 優先度順にソート（高い順）
        usort($applicable, function($a, $b) {
            return $b->get_priority() - $a->get_priority();
        });
        
        return $applicable;
    }
    
    /**
     * 登録済みのすべてのモジュールを取得
     * 
     * @return array すべての登録済みモジュール
     */
    public function get_all_modules() {
        return $this->modules;
    }
    
    /**
     * 名前でモジュールを取得
     * 
     * @param string $name モジュール名
     * @return Affiliate_Module_Interface|null モジュールインスタンスまたは見つからない場合はnull
     */
    public function get_module_by_name($name) {
        foreach ($this->modules as $module) {
            if ($module->get_module_name() === $name) {
                return $module;
            }
        }
        return null;
    }
}
