<?php
/**
 * 旅行表示マネージャークラス
 * リンクの表示形式を管理
 */
class Travel_Display_Manager {
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
    }
    
    /**
     * リンクを指定されたテンプレートでレンダリング
     * 
     * @param array $links リンク情報の配列
     * @param string $template_type テンプレートタイプ
     * @return string HTML出力
     */
    public function render($links, $template_type = 'card') {
        if (empty($links)) {
            return '';
        }
        
        // ユーザーフレンドリーテンプレートをデフォルトに
        if ($template_type === 'card' || $template_type === 'default') {
            $template_type = 'user-friendly';
        }
        
        // 基本のディスプレイマネージャーを使用
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-display-manager.php';
        $display_manager = new Beer_Affiliate_Display_Manager();
        return $display_manager->render($links, $template_type);
    }
    
    /**
     * カードテンプレートでリンクをレンダリング
     * 
     * @param array $links リンク情報の配列
     * @return string HTML出力
     */
    private function render_card_template($links) {
        ob_start();
        ?>
        <div class="beer-affiliate-container">
            <h3 class="beer-affiliate-title"><?php echo $this->get_seasonal_heading(); ?></h3>
            
            <div class="beer-affiliate-cards">
                <?php foreach ($links as $item): ?>
                    <div class="beer-affiliate-card">
                        <?php if (isset($item['city']['image_url']) && !empty($item['city']['image_url'])): ?>
                            <div class="beer-affiliate-card-image">
                                <img src="<?php echo esc_url(BEER_AFFILIATE_PLUGIN_URL . 'modules/travel/images/' . $item['city']['image_url']); ?>" alt="<?php echo esc_attr($item['city']['name']); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="beer-affiliate-card-content">
                            <h4 class="beer-affiliate-card-title"><?php echo esc_html($item['city']['name']); ?>のビール旅</h4>
                            
                            <?php if (isset($item['city']['description'])): ?>
                                <p class="beer-affiliate-card-description"><?php echo esc_html($item['city']['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="beer-affiliate-card-links">
                                <?php foreach ($item['links'] as $service => $link): ?>
                                    <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="nofollow noopener" class="beer-affiliate-button">
                                        <?php echo esc_html($link['label']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * ボタンテンプレートでリンクをレンダリング
     * 
     * @param array $links リンク情報の配列
     * @return string HTML出力
     */
    private function render_button_template($links) {
        ob_start();
        ?>
        <div class="beer-affiliate-container beer-affiliate-button-container">
            <h3 class="beer-affiliate-title"><?php echo $this->get_seasonal_heading(); ?></h3>
            
            <div class="beer-affiliate-buttons">
                <?php foreach ($links as $item): ?>
                    <div class="beer-affiliate-button-group">
                        <p class="beer-affiliate-city-name"><?php echo esc_html($item['city']['name']); ?>のビール旅</p>
                        
                        <?php foreach ($item['links'] as $service => $link): ?>
                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="nofollow noopener" class="beer-affiliate-button beer-affiliate-button-<?php echo sanitize_html_class($service); ?>">
                                <?php echo esc_html($link['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * スクロール追従テンプレートでリンクをレンダリング
     * 
     * @param array $links リンク情報の配列
     * @return string HTML出力
     */
    private function render_sticky_template($links) {
        // 最初の地域のみ使用
        if (empty($links)) {
            return '';
        }
        
        $item = reset($links);
        
        ob_start();
        ?>
        <div class="beer-affiliate-sticky-container">
            <div class="beer-affiliate-sticky">
                <div class="beer-affiliate-sticky-content">
                    <h4 class="beer-affiliate-sticky-title"><?php echo esc_html($item['city']['name']); ?>のビール旅に出かけよう！</h4>
                    
                    <div class="beer-affiliate-sticky-buttons">
                        <?php foreach ($item['links'] as $service => $link): ?>
                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="nofollow noopener" class="beer-affiliate-button beer-affiliate-button-sticky">
                                <?php echo esc_html($link['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <button class="beer-affiliate-sticky-close" aria-label="閉じる">×</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 季節に応じた見出しを取得
     * 
     * @return string 見出しテキスト
     */
    private function get_seasonal_heading() {
        // 現在の月を取得
        $current_month = date('n');
        
        // 季節を判定して見出しを返す
        if ($current_month >= 3 && $current_month <= 5) {
            return '春のビール旅行特集';
        } elseif ($current_month >= 6 && $current_month <= 8) {
            return '夏のクラフトビール聖地巡礼';
        } elseif ($current_month >= 9 && $current_month <= 11) {
            return '秋のビールツーリズム';
        } else {
            return '冬の醸造所めぐり';
        }
    }
    
    /**
     * 心理的トリガーテキストを取得（日替わり）
     * 
     * @return string トリガーテキスト
     */
    private function get_trigger_text() {
        $triggers = array(
            '【期間限定】今だけのプラン',
            '地元民しか知らない穴場スポット',
            '予約者急増中！早めの確保がおすすめ',
            'ビール好きにはたまらない特典付き',
            '今が狙い目！お得なシーズン',
            '数量限定の特別醸造ビール付き',
            'クラフトビール巡りの旅',
            '日本各地のビール文化を体験'
        );
        
        // 日付に基づいてトリガーを選択
        $day_of_year = date('z');
        $index = $day_of_year % count($triggers);
        
        return $triggers[$index];
    }
}
