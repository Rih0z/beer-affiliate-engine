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
            'default' => 'a17092772583',
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
