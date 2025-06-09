<?php
/**
 * カスタマイザークラス
 * WordPressカスタマイザーとの連携
 */
class Beer_Affiliate_Customizer {
    /**
     * コンストラクタ
     */
    public function __construct() {
        // カスタマイザー登録フックを追加
        add_action('customize_register', array($this, 'register_customizer_settings'));
    }
    
    /**
     * カスタマイザー設定を登録
     * 
     * @param WP_Customize_Manager $wp_customize カスタマイザーマネージャー
     */
    public function register_customizer_settings($wp_customize) {
        // セクションを追加
        $wp_customize->add_section('beer_affiliate_options', array(
            'title' => __('ビールアフィリエイト設定', 'beer-affiliate-engine'),
            'priority' => 120,
            'description' => __('クラフトビールアフィリエイト自動最適化プラグインの設定', 'beer-affiliate-engine')
        ));
        
        // 表示テンプレート設定
        $wp_customize->add_setting('beer_affiliate_template', array(
            'default' => 'card',
            'transport' => 'refresh',
            'sanitize_callback' => array($this, 'sanitize_template')
        ));
        
        $wp_customize->add_control('beer_affiliate_template', array(
            'label' => __('表示テンプレート', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_template',
            'type' => 'select',
            'choices' => array(
                'card' => __('カード表示（画像付き）', 'beer-affiliate-engine'),
                'button' => __('ボタン表示（テキストのみ）', 'beer-affiliate-engine'),
                'sticky' => __('スクロール追従表示', 'beer-affiliate-engine')
            )
        ));
        
        // 優先モジュール設定
        $wp_customize->add_setting('beer_affiliate_primary_module', array(
            'default' => 'travel',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        $wp_customize->add_control('beer_affiliate_primary_module', array(
            'label' => __('優先モジュール', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_primary_module',
            'type' => 'radio',
            'choices' => array(
                'travel' => __('旅行アフィリエイト', 'beer-affiliate-engine')
                // 将来的に他のモジュールが追加される
            )
        ));
        
        // 記事末尾に自動挿入するかどうか
        $wp_customize->add_setting('beer_affiliate_auto_insert', array(
            'default' => true,
            'transport' => 'refresh',
            'sanitize_callback' => array($this, 'sanitize_checkbox')
        ));
        
        $wp_customize->add_control('beer_affiliate_auto_insert', array(
            'label' => __('記事末尾に自動挿入', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_auto_insert',
            'type' => 'checkbox'
        ));
        
        // 最大表示リンク数
        $wp_customize->add_setting('beer_affiliate_max_links', array(
            'default' => 2,
            'transport' => 'refresh',
            'sanitize_callback' => 'absint'
        ));
        
        $wp_customize->add_control('beer_affiliate_max_links', array(
            'label' => __('最大表示リンク数', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_max_links',
            'type' => 'number',
            'input_attrs' => array(
                'min' => 1,
                'max' => 5,
                'step' => 1
            )
        ));
        
        // 海外対応設定
        $wp_customize->add_setting('beer_affiliate_enable_international', array(
            'default' => true,
            'transport' => 'refresh',
            'sanitize_callback' => array($this, 'sanitize_checkbox')
        ));
        
        $wp_customize->add_control('beer_affiliate_enable_international', array(
            'label' => __('海外都市への対応を有効化', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_enable_international',
            'type' => 'checkbox'
        ));
        
        // 優先国際サービス設定
        $wp_customize->add_setting('beer_affiliate_primary_intl_service', array(
            'default' => 'travelist',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        $wp_customize->add_control('beer_affiliate_primary_intl_service', array(
            'label' => __('優先国際旅行サービス', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_primary_intl_service',
            'type' => 'select',
            'choices' => array(
                'travelist' => __('Travelist (格安航空券)', 'beer-affiliate-engine'),
                'travel-standard' => __('トラベル・スタンダード・ジャパン', 'beer-affiliate-engine'),
                'oooh' => __('Oooh（ウー）- 現地ツアー', 'beer-affiliate-engine'),
                'qatar' => __('カタール航空', 'beer-affiliate-engine')
            )
        ));
        
        // A8.netメディアID
        $wp_customize->add_setting('beer_affiliate_a8_media_id', array(
            'default' => '3UJGPC',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        $wp_customize->add_control('beer_affiliate_a8_media_id', array(
            'label' => __('A8.netメディアID', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_a8_media_id',
            'type' => 'text'
        ));
        
        // カテゴリーフィルター設定
        $wp_customize->add_setting('beer_affiliate_category_filter', array(
            'default' => 'travel',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        $wp_customize->add_control('beer_affiliate_category_filter', array(
            'label' => __('表示するリンクカテゴリー', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_category_filter',
            'type' => 'select',
            'choices' => array(
                'travel' => __('旅行のみ', 'beer-affiliate-engine'),
                'shopping' => __('買い物のみ', 'beer-affiliate-engine'),
                'all' => __('すべて表示', 'beer-affiliate-engine')
            )
        ));

        // 楽天トラベルアフィリエイトID
        $wp_customize->add_setting('beer_affiliate_rakuten_travel_id', array(
            'default' => '20a2fc9d.5c6c02f2.20a2fc9e.541a36d0',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        $wp_customize->add_control('beer_affiliate_rakuten_travel_id', array(
            'label' => __('楽天トラベルアフィリエイトID', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_rakuten_travel_id',
            'type' => 'text'
        ));
        
        // JTBアフィリエイトID
        $wp_customize->add_setting('beer_affiliate_jtb_id', array(
            'default' => '',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        $wp_customize->add_control('beer_affiliate_jtb_id', array(
            'label' => __('JTBアフィリエイトID', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_jtb_id',
            'type' => 'text'
        ));
        
        // HISアフィリエイトID
        $wp_customize->add_setting('beer_affiliate_his_id', array(
            'default' => '',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        $wp_customize->add_control('beer_affiliate_his_id', array(
            'label' => __('HISアフィリエイトID', 'beer-affiliate-engine'),
            'section' => 'beer_affiliate_options',
            'settings' => 'beer_affiliate_his_id',
            'type' => 'text'
        ));
    }
    
    /**
     * テンプレート設定のサニタイズ
     * 
     * @param string $input 入力値
     * @return string サニタイズされた値
     */
    public function sanitize_template($input) {
        $valid = array('card', 'button', 'sticky');
        
        if (in_array($input, $valid)) {
            return $input;
        }
        
        return 'card';
    }
    
    /**
     * チェックボックス設定のサニタイズ
     * 
     * @param boolean $checked チェック状態
     * @return boolean サニタイズされた値
     */
    public function sanitize_checkbox($checked) {
        return (isset($checked) && true == $checked) ? true : false;
    }
}

// カスタマイザークラスをインスタンス化
new Beer_Affiliate_Customizer();
