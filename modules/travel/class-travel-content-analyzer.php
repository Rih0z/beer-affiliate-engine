<?php
/**
 * 旅行コンテンツアナライザクラス
 * 記事内容から地域名を抽出する
 */
class Travel_Content_Analyzer {
    /**
     * 地域名辞書
     * 
     * @var array
     */
    private $city_dictionary;
    
    /**
     * データストア
     * 
     * @var Beer_Affiliate_Data_Store
     */
    private $data_store;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // データストアをロード
        $this->data_store = new Beer_Affiliate_Data_Store();
        
        // 地域名辞書をロード
        $this->city_dictionary = $this->load_dictionary();
    }
    
    /**
     * 地域名辞書をロード
     * 
     * @return array 地域名辞書
     */
    private function load_dictionary() {
        // キャッシュから辞書を取得
        $dictionary = $this->data_store->get_cache('city_dictionary');
        
        if (false === $dictionary) {
            // キャッシュがない場合はJSONから読み込み
            $json_file = BEER_AFFILIATE_PLUGIN_DIR . 'modules/travel/city-dictionary.json';
            $dictionary = json_decode(file_get_contents($json_file), true);
            
            // キャッシュに保存（1日間）
            $this->data_store->set_cache('city_dictionary', $dictionary, DAY_IN_SECONDS);
        }
        
        return $dictionary;
    }
    
    /**
     * コンテンツを解析して地域名を抽出
     * 
     * @param string $content 投稿コンテンツ
     * @return array 抽出された地域名の配列
     */
    public function analyze($content) {
        // コンテンツキャッシュキーを生成
        $cache_key = 'city_analysis_' . md5($content);
        
        // キャッシュから結果を取得
        $matched_cities = $this->data_store->get_cache($cache_key);
        
        if (false !== $matched_cities) {
            return $matched_cities;
        }
        
        // キャッシュがない場合は解析を実行
        $matched_cities = array();
        
        foreach ($this->city_dictionary as $city) {
            // 正規の地域名が含まれるかチェック
            if (mb_strpos($content, $city['name']) !== false) {
                // 出現回数をカウント
                $count = mb_substr_count($content, $city['name']);
                
                // 文中での位置を取得（先頭に近いほど重要）
                $position = mb_strpos($content, $city['name']) / mb_strlen($content);
                
                // 重要度を計算（出現回数が多く、前方に出現するほど重要）
                $importance = ($count * 10) - ($position * 5);
                
                $matched_cities[] = array_merge($city, array(
                    'count' => $count,
                    'position' => $position,
                    'importance' => $importance
                ));
            } else {
                // 別名（エイリアス）もチェック
                if (isset($city['aliases']) && is_array($city['aliases'])) {
                    foreach ($city['aliases'] as $alias) {
                        if (mb_strpos($content, $alias) !== false) {
                            // 出現回数をカウント
                            $count = mb_substr_count($content, $alias);
                            
                            // 文中での位置を取得
                            $position = mb_strpos($content, $alias) / mb_strlen($content);
                            
                            // 重要度を計算（エイリアスは若干低めに）
                            $importance = ($count * 8) - ($position * 5);
                            
                            $matched_cities[] = array_merge($city, array(
                                'matched_alias' => $alias,
                                'count' => $count,
                                'position' => $position,
                                'importance' => $importance
                            ));
                            
                            // 同じ地域の別名でマッチした場合は次の地域へ
                            break;
                        }
                    }
                }
            }
        }
        
        // 重要度順にソート（高い順）
        usort($matched_cities, function($a, $b) {
            return $b['importance'] - $a['importance'];
        });
        
        // 結果をキャッシュ（1時間）
        $this->data_store->set_cache($cache_key, $matched_cities, HOUR_IN_SECONDS);
        
        return $matched_cities;
    }
    
    /**
     * 前後のコンテキストから感情分析を行う（将来実装用メソッド）
     * 
     * @param string $content 全体コンテンツ
     * @param string $keyword 対象キーワード
     * @param int $context_size 前後のコンテキストサイズ（文字数）
     * @return array 感情分析結果
     */
    public function analyze_context($content, $keyword, $context_size = 100) {
        // キーワードの位置を特定
        $position = mb_strpos($content, $keyword);
        
        if (false === $position) {
            return array(
                'positive' => 0,
                'negative' => 0,
                'sentiment' => 0
            );
        }
        
        // 前後のコンテキストを取得
        $start = max(0, $position - $context_size);
        $length = min(mb_strlen($content) - $start, $context_size * 2 + mb_strlen($keyword));
        $context = mb_substr($content, $start, $length);
        
        // 簡易感情分析
        $positive_words = array('おいしい', '美味しい', '素晴らしい', '最高', '良い', '好き', 'おすすめ');
        $negative_words = array('まずい', '不味い', '悪い', '最悪', '残念', '嫌い');
        
        $positive_score = 0;
        $negative_score = 0;
        
        foreach ($positive_words as $word) {
            $positive_score += mb_substr_count($context, $word);
        }
        
        foreach ($negative_words as $word) {
            $negative_score += mb_substr_count($context, $word);
        }
        
        return array(
            'positive' => $positive_score,
            'negative' => $negative_score,
            'sentiment' => $positive_score - $negative_score
        );
    }
}
