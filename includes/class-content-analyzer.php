<?php
/**
 * コンテンツ解析クラス
 * 投稿コンテンツからキーワードを抽出する基底クラス
 */
class Beer_Affiliate_Content_Analyzer {
    /**
     * データストア
     * 
     * @var Beer_Affiliate_Data_Store
     */
    protected $data_store;
    
    /**
     * キーワード辞書
     * 
     * @var array
     */
    protected $dictionary = array();
    
    /**
     * キャッシュキープレフィックス
     * 
     * @var string
     */
    protected $cache_prefix = 'content_analysis_';
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // データストアをロード
        $this->data_store = new Beer_Affiliate_Data_Store();
    }
    
    /**
     * 辞書をロードする
     * 
     * @param string $dictionary_path 辞書ファイルのパス
     * @param string $cache_key キャッシュキー
     * @return array 辞書データ
     */
    protected function load_dictionary($dictionary_path, $cache_key) {
        // キャッシュから辞書を取得
        $dictionary = $this->data_store->get_cache($cache_key);
        
        if (false === $dictionary) {
            // キャッシュがない場合はJSONから読み込み
            if (file_exists($dictionary_path)) {
                $dictionary = json_decode(file_get_contents($dictionary_path), true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('Beer Affiliate: JSON parse error in dictionary: ' . json_last_error_msg());
                    return array();
                }
                
                // キャッシュに保存（1日間）
                $this->data_store->set_cache($cache_key, $dictionary, DAY_IN_SECONDS);
            } else {
                error_log('Beer Affiliate: Dictionary file not found: ' . $dictionary_path);
                return array();
            }
        }
        
        return $dictionary;
    }
    
    /**
     * コンテンツを解析してキーワードを抽出する
     * 
     * @param string $content コンテンツ
     * @return array 抽出されたキーワード
     */
    public function analyze($content) {
        // 空のコンテンツの場合は空配列を返す
        if (empty($content)) {
            return array();
        }
        
        // キャッシュキーを生成
        $cache_key = $this->cache_prefix . md5($content);
        
        // キャッシュからデータを取得
        $results = $this->data_store->get_cache($cache_key);
        
        if (false !== $results) {
            return $results;
        }
        
        // 実際の解析処理（子クラスでオーバーライド）
        $results = $this->extract_keywords($content);
        
        // 結果をキャッシュ（1時間）
        if (!empty($results)) {
            $this->data_store->set_cache($cache_key, $results, HOUR_IN_SECONDS);
        }
        
        return $results;
    }
    
    /**
     * コンテンツからキーワードを抽出する
     * このメソッドは子クラスでオーバーライドする
     * 
     * @param string $content コンテンツ
     * @return array 抽出されたキーワード
     */
    protected function extract_keywords($content) {
        // 基本実装は空の配列を返す
        // 子クラスで実際の抽出ロジックを実装
        return array();
    }
    
    /**
     * キーワードの周辺テキストを取得
     * 
     * @param string $content 全体コンテンツ
     * @param string $keyword キーワード
     * @param int $context_size 前後のコンテキストサイズ（文字数）
     * @return string 周辺テキスト
     */
    protected function get_surrounding_text($content, $keyword, $context_size = 50) {
        $position = mb_strpos($content, $keyword);
        
        if (false === $position) {
            return '';
        }
        
        $start = max(0, $position - $context_size);
        $length = mb_strlen($keyword) + ($context_size * 2);
        $end = min(mb_strlen($content), $position + mb_strlen($keyword) + $context_size);
        
        // 実際に取得できる文字列の長さを調整
        $length = $end - $start;
        
        return mb_substr($content, $start, $length);
    }
    
    /**
     * 辞書をフィルタリングする
     * 
     * @param callable $filter_callback フィルターコールバック
     * @return array フィルタリングされた辞書
     */
    protected function filter_dictionary($filter_callback) {
        if (empty($this->dictionary)) {
            return array();
        }
        
        return array_filter($this->dictionary, $filter_callback);
    }
    
    /**
     * キーワードの重要度を計算
     * 
     * @param string $content コンテンツ
     * @param string $keyword キーワード
     * @return float 重要度スコア
     */
    protected function calculate_importance($content, $keyword) {
        // 出現回数をカウント
        $count = mb_substr_count($content, $keyword);
        
        // 文中での位置を取得（先頭に近いほど重要）
        $position = mb_strpos($content, $keyword) / mb_strlen($content);
        
        // 重要度を計算（出現回数が多く、前方に出現するほど重要）
        return ($count * 10) - ($position * 5);
    }
}
