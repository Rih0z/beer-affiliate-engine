<?php
/**
 * アフィリエイトプログラム管理クラス
 * 
 * 正しいプログラムIDとURLを一元管理
 */
class Beer_Affiliate_Programs {
    
    /**
     * A8.netプログラム情報
     */
    private static $a8_programs = array(
        'JTB国内旅行' => array(
            'program_id' => '5350',
            'mat_template' => 's00000005350001',
            'description' => '国内ホテル・旅館予約',
            'commission' => '0.8%',
            'status' => '参加中'
        ),
        'トラベル・スタンダード・ジャパン' => array(
            'program_id' => '26123',
            'mat_template' => 's00000026123001005000',
            'description' => 'オーダーメイド旅行（問い合わせ2000円+実施5000円）',
            'commission' => '最大7000円',
            'status' => '参加中'
        ),
        'JTBショッピング' => array(
            'program_id' => '18449',
            'mat_template' => 's00000018449001012000',
            'description' => '地ビール・名産品通販',
            'commission' => '5%',
            'status' => '参加中'
        ),
        '読売旅行' => array(
            'program_id' => '22598',
            'mat_template' => 's00000022598001',
            'description' => '国内外ツアー',
            'commission' => '2%',
            'status' => '参加中'
        ),
        '一休.comレストラン' => array(
            'program_id' => '218',
            'mat_template' => 's00000000218004',
            'description' => 'レストラン予約',
            'commission' => '1%',
            'status' => '参加中'
        ),
        'Otomoni' => array(
            'program_id' => '23019',
            'mat_template' => 's00000023019001',
            'description' => 'クラフトビール定期便',
            'commission' => '新規定期申込2000円',
            'status' => '参加中'
        ),
        'カタール航空' => array(
            'program_id' => '26391',
            'mat_template' => 's00000026391001',
            'description' => '国際線航空券',
            'commission' => '1.5%',
            'status' => '参加中'
        ),
        'Oooh(ウー)' => array(
            'program_id' => '26491',
            'mat_template' => 's00000026491001',
            'description' => '現地オプショナルツアー',
            'commission' => '10%',
            'status' => '参加中'
        ),
        'Saily' => array(
            'program_id' => '26058',
            'mat_template' => 's00000026058001',
            'description' => '海外用eSIM',
            'commission' => '10%',
            'status' => '参加中'
        ),
        'Travelist' => array(
            'program_id' => '23067',
            'mat_template' => 's00000023067003',
            'description' => '海外格安航空券',
            'commission' => '300円',
            'status' => '参加中'
        ),
        'ピースボート' => array(
            'program_id' => '17807',
            'mat_template' => 's00000017807001',
            'description' => '地球一周クルーズ',
            'commission' => '詳細はプログラム参照',
            'status' => '参加中'
        ),
        'TOURQUA' => array(
            'program_id' => '25850',
            'mat_template' => 's00000025850001',
            'description' => 'トルコ専門ツアー',
            'commission' => '詳細はプログラム参照',
            'status' => '参加中'
        ),
        'GigSky' => array(
            'program_id' => '26435',
            'mat_template' => 's00000026435001',
            'description' => 'グローバルeSIM',
            'commission' => '詳細はプログラム参照',
            'status' => '参加中'
        ),
        'TORA eSIM' => array(
            'program_id' => '26367',
            'mat_template' => 's00000026367001',
            'description' => '海外向けeSIM',
            'commission' => '詳細はプログラム参照',
            'status' => '参加中'
        ),
        'エアトリハワイ' => array(
            'program_id' => '13798',
            'mat_template' => 's00000013798009',
            'description' => 'ハワイ旅行専門',
            'commission' => '詳細はプログラム参照',
            'status' => '参加中'
        ),
        'アクロスWiFi' => array(
            'program_id' => '26314',
            'mat_template' => 's00000026314001',
            'description' => '海外WiFiレンタル',
            'commission' => '詳細はプログラム参照',
            'status' => '参加中'
        ),
        'J-TRIP' => array(
            'program_id' => '18767',
            'mat_template' => 's00000018767001',
            'description' => 'JAL格安国内旅行',
            'commission' => '詳細はプログラム参照',
            'status' => '参加中'
        )
    );
    
    /**
     * A8.netのURLを生成
     * 
     * @param string $program_name プログラム名
     * @param string $redirect_url リダイレクト先URL
     * @return string|false 生成されたURL、または失敗時はfalse
     */
    public static function generate_a8_url($program_name, $redirect_url) {
        if (!isset(self::$a8_programs[$program_name])) {
            return false;
        }
        
        $program = self::$a8_programs[$program_name];
        $mat_value = $program['mat_template'];
        
        // メディアIDがある場合は置換
        $options = get_option('beer_affiliate_settings', array());
        if (!empty($options['a8_media_id'])) {
            // 4530O4の部分をメディアIDに置換
            $mat_value = str_replace('4530O4', $options['a8_media_id'], $mat_value);
        }
        
        // URLエンコード
        $encoded_url = urlencode($redirect_url);
        
        return "https://px.a8.net/svt/ejp?a8mat={$mat_value}&a8ejpredirect={$encoded_url}";
    }
    
    /**
     * 楽天トラベルのURLを生成
     * 
     * @param array $params パラメータ配列
     * @return string 生成されたURL
     */
    public static function generate_rakuten_url($params) {
        $options = get_option('beer_affiliate_settings', array());
        $affiliate_id = isset($options['rakuten_affiliate_id']) ? $options['rakuten_affiliate_id'] : '';
        
        // デフォルトパラメータ
        $default_params = array(
            'f_teikei' => 'premium',
            'f_sort' => 'review_high',
            'f_points_min' => '4',
            'f_affiliate_id' => $affiliate_id
        );
        
        // パラメータをマージ
        $merged_params = array_merge($default_params, $params);
        
        return 'https://travel.rakuten.co.jp/hotel/search/?' . http_build_query($merged_params);
    }
    
    /**
     * プログラム情報を取得
     * 
     * @return array プログラム情報の配列
     */
    public static function get_all_programs() {
        $programs = array();
        
        // A8.netプログラム
        foreach (self::$a8_programs as $name => $info) {
            $programs[$name] = array(
                'type' => 'A8.net',
                'commission' => $info['commission'],
                'status' => $info['status'],
                'description' => $info['description']
            );
        }
        
        // 楽天トラベル
        $programs['楽天トラベル'] = array(
            'type' => '楽天アフィリエイト',
            'commission' => '1%',
            'status' => '参加中',
            'description' => '国内ホテル・旅館予約'
        );
        
        return $programs;
    }
    
    /**
     * 参加中のプログラムのみ取得
     * 
     * @return array アクティブなプログラムの配列
     */
    public static function get_active_programs() {
        $all_programs = self::get_all_programs();
        $active_programs = array();
        
        foreach ($all_programs as $name => $info) {
            if ($info['status'] === '参加中') {
                $active_programs[$name] = $info;
            }
        }
        
        return $active_programs;
    }
}