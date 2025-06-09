<?php
/**
 * 収益最適化モジュール
 * 
 * @package Beer_Affiliate_Engine
 */

class Revenue_Optimizer_Module extends Base_Affiliate_Module {
    
    /**
     * モジュール名
     */
    protected $module_name = 'revenue_optimizer';
    
    /**
     * 表示名
     */
    protected $display_name = '収益最適化';
    
    /**
     * 優先度（高いほど優先）
     */
    protected $module_priority = 100;
    
    /**
     * プログラムの収益性データ
     */
    private $program_revenue_data = array(
        'travel' => array(
            'トラベル・スタンダード・ジャパン' => array(
                'potential_revenue' => 7000, // 問い合わせ2000円 + 実施5000円
                'conversion_rate' => 0.75,    // 確定率75.32%
                'priority' => 10
            ),
            '読売旅行' => array(
                'potential_revenue' => 2000,  // 平均客単価10万円の2%
                'conversion_rate' => 0.16,    // 確定率15.78%
                'priority' => 8
            ),
            'JTB国内旅行' => array(
                'potential_revenue' => 800,
                'conversion_rate' => 1.0,     // 確定率100%
                'priority' => 9
            ),
            '楽天トラベル' => array(
                'potential_revenue' => 500,   // 推定
                'conversion_rate' => 0.8,
                'priority' => 7
            )
        ),
        'experience' => array(
            '一休.comレストラン' => array(
                'potential_revenue' => 100,   // 1万円予約の1%
                'conversion_rate' => 0.77,    // 確定率76.90%
                'priority' => 6
            ),
            'Oooh(ウー)' => array(
                'potential_revenue' => 750,
                'conversion_rate' => 1.0,     // 確定率100%
                'priority' => 7
            )
        ),
        'shopping' => array(
            'Otomoni' => array(
                'potential_revenue' => 2000,  // 定期申込
                'conversion_rate' => 1.0,     // 確定率100%
                'priority' => 9,
                'recurring' => true           // 継続収益
            ),
            'JTBショッピング' => array(
                'potential_revenue' => 250,   // 5000円購入の5%
                'conversion_rate' => 1.0,     // 確定率100%
                'priority' => 5
            )
        ),
        'utility' => array(
            'Saily' => array(
                'potential_revenue' => 300,   // 3000円購入の10%
                'conversion_rate' => 1.0,     // 確定率100%
                'priority' => 4
            ),
            'カタール航空' => array(
                'potential_revenue' => 1500,  // 10万円航空券の1.5%
                'conversion_rate' => 0.67,    // 確定率66.66%
                'priority' => 6
            )
        )
    );
    
    /**
     * キーワードを抽出
     */
    public function extract_keywords($content) {
        $keywords = array();
        
        // ビール関連キーワード
        $beer_keywords = array(
            'ビール', 'クラフトビール', 'IPA', 'ペールエール', 'スタウト',
            'ブルワリー', '醸造所', 'ビアバー', 'ビアレストラン', 'ビアガーデン'
        );
        
        // 地域キーワード（コンテンツアナライザーを使用）
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'includes/class-content-analyzer.php';
        $analyzer = new Beer_Affiliate_Content_Analyzer();
        $location_keywords = $analyzer->extract_locations($content);
        
        // ビールキーワードをチェック
        foreach ($beer_keywords as $keyword) {
            if (mb_strpos($content, $keyword) !== false) {
                $keywords['beer_type'][] = $keyword;
            }
        }
        
        // 文脈を分析
        $keywords['context'] = $this->analyze_context($content);
        $keywords['locations'] = $location_keywords;
        
        return $keywords;
    }
    
    /**
     * 文脈を分析して最適なカテゴリーを判定
     */
    private function analyze_context($content) {
        $context = array();
        
        // 旅行関連の文脈
        if (preg_match('/旅行|訪問|行く|行った|訪れ|滞在|ツアー|観光/u', $content)) {
            $context[] = 'travel';
        }
        
        // レストラン・飲食関連の文脈
        if (preg_match('/レストラン|ランチ|ディナー|食事|料理|グルメ|飲み/u', $content)) {
            $context[] = 'dining';
        }
        
        // 購入・お取り寄せ関連の文脈
        if (preg_match('/購入|買う|お取り寄せ|通販|定期|サブスク/u', $content)) {
            $context[] = 'shopping';
        }
        
        // デフォルトは旅行（最も高単価）
        if (empty($context)) {
            $context[] = 'travel';
        }
        
        return $context;
    }
    
    /**
     * リンクを生成
     */
    public function generate_links($keywords) {
        $all_links = array();
        
        // 文脈に基づいて適切なカテゴリーを選択
        $contexts = isset($keywords['context']) ? $keywords['context'] : array('travel');
        $locations = isset($keywords['locations']) ? $keywords['locations'] : array();
        
        // 各カテゴリーごとにリンクを生成
        $categories = $this->get_categories_for_contexts($contexts);
        
        foreach ($categories as $category => $programs) {
            $category_links = array();
            
            // 収益性の高い順にソート
            $sorted_programs = $this->sort_programs_by_revenue($programs);
            
            foreach ($sorted_programs as $program_name => $program_data) {
                $link = $this->generate_program_link($program_name, $program_data, $locations);
                if ($link) {
                    $category_links[] = $link;
                    
                    // カテゴリーごとに最大3つまで
                    if (count($category_links) >= 3) {
                        break;
                    }
                }
            }
            
            if (!empty($category_links)) {
                $all_links[$category] = array(
                    'title' => $this->get_category_title($category),
                    'links' => $category_links
                );
            }
        }
        
        return $all_links;
    }
    
    /**
     * 文脈に基づいてカテゴリーを選択
     */
    private function get_categories_for_contexts($contexts) {
        $categories = array();
        
        foreach ($contexts as $context) {
            switch ($context) {
                case 'travel':
                    $categories['travel'] = $this->program_revenue_data['travel'];
                    $categories['utility'] = $this->program_revenue_data['utility'];
                    break;
                case 'dining':
                    $categories['experience'] = $this->program_revenue_data['experience'];
                    $categories['shopping'] = $this->program_revenue_data['shopping'];
                    break;
                case 'shopping':
                    $categories['shopping'] = $this->program_revenue_data['shopping'];
                    break;
            }
        }
        
        // デフォルトカテゴリー
        if (empty($categories)) {
            $categories['travel'] = $this->program_revenue_data['travel'];
            $categories['shopping'] = $this->program_revenue_data['shopping'];
        }
        
        return $categories;
    }
    
    /**
     * プログラムを収益性でソート
     */
    private function sort_programs_by_revenue($programs) {
        uasort($programs, function($a, $b) {
            $revenue_a = $a['potential_revenue'] * $a['conversion_rate'];
            $revenue_b = $b['potential_revenue'] * $b['conversion_rate'];
            return $revenue_b <=> $revenue_a;
        });
        
        return $programs;
    }
    
    /**
     * プログラムリンクを生成
     */
    private function generate_program_link($program_name, $program_data, $locations) {
        // リンクジェネレータを使用
        require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/revenue-optimizer/class-revenue-link-generator.php';
        $generator = new Revenue_Link_Generator();
        
        // 地域情報がある場合は地域に応じたリンクを生成
        $location = !empty($locations) ? reset($locations) : null;
        
        $link_data = $generator->generate_link($program_name, $location);
        if (!$link_data) {
            return null;
        }
        
        // 収益情報を追加
        $link_data['potential_revenue'] = $program_data['potential_revenue'];
        $link_data['is_recurring'] = isset($program_data['recurring']) ? $program_data['recurring'] : false;
        
        return $link_data;
    }
    
    /**
     * カテゴリータイトルを取得
     */
    private function get_category_title($category) {
        $titles = array(
            'travel' => '🍺 ビール旅に出かける',
            'experience' => '🍻 ビール体験を予約',
            'shopping' => '📦 クラフトビールをお取り寄せ',
            'utility' => '✈️ 旅の準備'
        );
        
        return isset($titles[$category]) ? $titles[$category] : $category;
    }
    
    /**
     * 表示テンプレートを取得
     */
    public function get_display_template($links, $template_type = 'card') {
        // リンクが空の場合は空文字を返す
        if (empty($links)) {
            return '';
        }
        
        $output = '<div class="beer-affiliate-revenue-optimizer">';
        
        foreach ($links as $category => $category_data) {
            $output .= '<div class="revenue-category">';
            $output .= '<h3 class="revenue-category-title">' . esc_html($category_data['title']) . '</h3>';
            $output .= '<div class="revenue-links">';
            
            foreach ($category_data['links'] as $link) {
                // カードテンプレートまたはボタンテンプレートを使用
                if ($template_type === 'card') {
                    $output .= $this->get_card_template($link);
                } else {
                    $output .= $this->get_button_template($link);
                }
            }
            
            $output .= '</div></div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * カードテンプレートを取得
     */
    private function get_card_template($link) {
        $revenue_badge = '';
        if ($link['is_recurring']) {
            $revenue_badge = '<span class="revenue-badge recurring">継続収益</span>';
        } elseif ($link['potential_revenue'] >= 2000) {
            $revenue_badge = '<span class="revenue-badge high-value">高単価</span>';
        }
        
        return sprintf(
            '<div class="affiliate-card revenue-optimized">
                <div class="card-header">
                    <h4>%s</h4>
                    %s
                </div>
                <div class="card-content">
                    <p>%s</p>
                </div>
                <a href="%s" class="card-button" target="_blank" rel="noopener">%s</a>
            </div>',
            esc_html($link['title']),
            $revenue_badge,
            esc_html($link['description']),
            esc_url($link['url']),
            esc_html($link['button_text'])
        );
    }
    
    /**
     * ボタンテンプレートを取得
     */
    private function get_button_template($link) {
        return sprintf(
            '<a href="%s" class="affiliate-button revenue-optimized" target="_blank" rel="noopener">%s</a>',
            esc_url($link['url']),
            esc_html($link['button_text'])
        );
    }
    
    /**
     * このモジュールが適用可能かチェック
     */
    public function is_applicable($content) {
        // ビール関連キーワードが含まれているかチェック
        $beer_keywords = array('ビール', 'beer', 'IPA', 'エール', 'スタウト', 'ブルワリー');
        
        foreach ($beer_keywords as $keyword) {
            if (mb_stripos($content, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
}