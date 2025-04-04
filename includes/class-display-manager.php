<?php
/**
 * 表示マネージャークラス
 * リンクの表示形式を管理する基底クラス
 */
class Beer_Affiliate_Display_Manager {
    /**
     * データストア
     * 
     * @var Beer_Affiliate_Data_Store
     */
    protected $data_store;
    
    /**
     * テンプレートディレクトリ
     * 
     * @var string
     */
    protected $template_dir;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // データストアをロード
        $this->data_store = new Beer_Affiliate_Data_Store();
        
        // テンプレートディレクトリを設定
        $this->template_dir = BEER_AFFILIATE_PLUGIN_DIR . 'templates/';
    }
    
    /**
     * リンクを表示用にレンダリング
     * 
     * @param array $links リンク情報の配列
     * @param string $template_type テンプレートタイプ
     * @return string HTML出力
     */
    public function render($links, $template_type = 'card') {
        if (empty($links)) {
            return '';
        }
        
        // テンプレートタイプに応じたメソッドを呼び出し
        switch ($template_type) {
            case 'button':
                return $this->render_button_template($links);
            case 'sticky':
                return $this->render_sticky_template($links);
            case 'card':
            default:
                return $this->render_card_template($links);
        }
    }
    
    /**
     * カードテンプレートでリンクをレンダリング
     * 
     * @param array $links リンク情報の配列
     * @return string HTML出力
     */
    protected function render_card_template($links) {
        $template_file = $this->template_dir . 'card.php';
        
        if (file_exists($template_file)) {
            return $this->render_template($template_file, array(
                'links' => $links,
                'heading' => $this->get_seasonal_heading()
            ));
        }
        
        // テンプレートファイルがない場合はデフォルト出力
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
                                    <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="nofollow noopener" class="beer-affiliate-button beer-affiliate-button-<?php echo sanitize_html_class(strtolower($service)); ?>">
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
    protected function render_button_template($links) {
        $template_file = $this->template_dir . 'button.php';
        
        if (file_exists($template_file)) {
            return $this->render_template($template_file, array(
                'links' => $links,
                'heading' => $this->get_seasonal_heading()
            ));
        }
        
        // テンプレートファイルがない場合はデフォルト出力
        ob_start();
        ?>
        <div class="beer-affiliate-container beer-affiliate-button-container">
            <h3 class="beer-affiliate-title"><?php echo $this->get_seasonal_heading(); ?></h3>
            
            <div class="beer-affiliate-buttons">
                <?php foreach ($links as $item): ?>
                    <div class="beer-affiliate-button-group">
                        <p class="beer-affiliate-city-name"><?php echo esc_html($item['city']['name']); ?>のビール旅</p>
                        
                        <?php foreach ($item['links'] as $service => $link): ?>
                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="nofollow noopener" class="beer-affiliate-button beer-affiliate-button-<?php echo sanitize_html_class(strtolower($service)); ?>">
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
    protected function render_sticky_template($links) {
        // 最初の地域のみ使用
        if (empty($links)) {
            return '';
        }
        
        $item = reset($links);
        $template_file = $this->template_dir . 'sticky.php';
        
        if (file_exists($template_file)) {
            return $this->render_template($template_file, array(
                'item' => $item
            ));
        }
        
        // テンプレートファイルがない場合はデフォルト出力
        ob_start();
        ?>
        <div class="beer-affiliate-sticky-container">
            <div class="beer-affiliate-sticky">
                <div class="beer-affiliate-sticky-content">
                    <h4 class="beer-affiliate-sticky-title"><?php echo esc_html($item['city']['name']); ?>のビール旅に出かけよう！</h4>
                    
                    <div class="beer-affiliate-sticky-buttons">
                        <?php foreach ($item['links'] as $service => $link): ?>
                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="nofollow noopener" class="beer-affiliate-button beer-affiliate-button-sticky beer-affiliate-button-<?php echo sanitize_html_class(strtolower($service)); ?>">
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
     * テンプレートファイルをレンダリング
     * 
     * @param string $template_file テンプレートファイルパス
     * @param array $vars テンプレート変数
     * @return string レンダリング結果
     */
    protected function render_template($template_file, $vars = array()) {
        // 変数をエクストラクト
        extract($vars);
        
        ob_start();
        include $template_file;
        return ob_get_clean();
    }
    
    /**
     * 季節に応じた見出しを取得
     * 
     * @return string 見出しテキスト
     */
    protected function get_seasonal_heading() {
        // 現在の月を取得
        $current_month = date('n');
        
        // 季節を判定して見出しを返す
        if ($current_month >= 3 && $current_month <= 5) {
            return __('春のビール旅行特集', 'beer-affiliate-engine');
        } elseif ($current_month >= 6 && $current_month <= 8) {
            return __('夏のクラフトビール聖地巡礼', 'beer-affiliate-engine');
        } elseif ($current_month >= 9 && $current_month <= 11) {
            return __('秋のビールツーリズム', 'beer-affiliate-engine');
        } else {
            return __('冬の醸造所めぐり', 'beer-affiliate-engine');
        }
    }
    
    /**
     * 心理的トリガーテキストを取得（日替わり）
     * 
     * @return string トリガーテキスト
     */
    protected function get_trigger_text() {
        $triggers = array(
            __('【期間限定】今だけのプラン', 'beer-affiliate-engine'),
            __('地元民しか知らない穴場スポット', 'beer-affiliate-engine'),
            __('予約者急増中！早めの確保がおすすめ', 'beer-affiliate-engine'),
            __('ビール好きにはたまらない特典付き', 'beer-affiliate-engine'),
            __('今が狙い目！お得なシーズン', 'beer-affiliate-engine'),
            __('数量限定の特別醸造ビール付き', 'beer-affiliate-engine'),
            __('クラフトビール巡りの旅', 'beer-affiliate-engine'),
            __('日本各地のビール文化を体験', 'beer-affiliate-engine')
        );
        
        // 日付に基づいてトリガーを選択
        $day_of_year = date('z');
        $index = $day_of_year % count($triggers);
        
        return $triggers[$index];
    }
    
    /**
     * 国際対応の表示テキストを取得
     * 
     * @param boolean $is_international 国際対応フラグ
     * @param array $city 地域情報
     * @return string 表示テキスト
     */
    protected function get_international_heading($is_international, $city) {
        if ($is_international && isset($city['country']) && !empty($city['country'])) {
            return sprintf(__('%sのクラフトビール旅（%s）', 'beer-affiliate-engine'), 
                $city['name'], 
                $city['country']
            );
        } else {
            return sprintf(__('%sのクラフトビール旅', 'beer-affiliate-engine'), 
                $city['name']
            );
        }
    }
}
