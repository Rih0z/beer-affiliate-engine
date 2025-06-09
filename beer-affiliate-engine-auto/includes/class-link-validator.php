<?php
/**
 * リンク検証クラス
 */

class Beer_Affiliate_Link_Validator {
    
    // 信頼できるドメインのリスト
    private static $trusted_domains = array(
        // 楽天系
        'travel.rakuten.co.jp',
        'hb.afl.rakuten.co.jp',
        
        // A8.net経由で信頼できるサイト
        'www.jtb.co.jp',
        'restaurant.ikyu.com',
        'www.jalan.net',
        'rlx.jp',
        'travel.yahoo.co.jp',
        'www.yomiuri-ryokou.co.jp',
        'otomoni.net',
        'fast-fi.net',
        'nissan-rentacar.com',
        'www.jal.co.jp',
        'www.ana.co.jp',
        'www.t-standard.com',
        
        // A8.netのドメイン
        'px.a8.net',
    );
    
    /**
     * URLの最終的なリダイレクト先を取得
     */
    public static function get_final_url($url, $max_redirects = 5) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Beer Affiliate Engine Validator)');
        
        $redirects = 0;
        $final_url = $url;
        
        while ($redirects < $max_redirects) {
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($http_code == 301 || $http_code == 302 || $http_code == 303 || $http_code == 307 || $http_code == 308) {
                preg_match('/Location: (.+)/i', $response, $matches);
                if (isset($matches[1])) {
                    $new_url = trim($matches[1]);
                    
                    // 相対URLの場合は絶対URLに変換
                    if (!preg_match('/^https?:\/\//', $new_url)) {
                        $parsed = parse_url($final_url);
                        $new_url = $parsed['scheme'] . '://' . $parsed['host'] . $new_url;
                    }
                    
                    $final_url = $new_url;
                    curl_setopt($ch, CURLOPT_URL, $final_url);
                    $redirects++;
                } else {
                    break;
                }
            } else {
                break;
            }
        }
        
        curl_close($ch);
        return $final_url;
    }
    
    /**
     * URLが信頼できるドメインかチェック
     */
    public static function is_trusted_domain($url) {
        $parsed = parse_url($url);
        if (!isset($parsed['host'])) {
            return false;
        }
        
        $host = strtolower($parsed['host']);
        
        foreach (self::$trusted_domains as $trusted) {
            if ($host === $trusted || substr($host, -strlen('.' . $trusted)) === '.' . $trusted) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * アフィリエイトリンクを検証
     */
    public static function validate_affiliate_link($url, $program) {
        // 基本的な検証
        if (empty($url)) {
            return array('valid' => false, 'reason' => 'URLが空です');
        }
        
        // A8.netの場合、リダイレクト先URLを確認
        if ($program['type'] === 'a8' && strpos($url, 'a8ejpredirect=') !== false) {
            preg_match('/a8ejpredirect=([^&]+)/', $url, $matches);
            if (isset($matches[1])) {
                $redirect_url = urldecode($matches[1]);
                if (!self::is_trusted_domain($redirect_url)) {
                    return array(
                        'valid' => false, 
                        'reason' => 'リダイレクト先が信頼できないドメインです: ' . $redirect_url
                    );
                }
            }
        }
        
        // 楽天の場合、pcとmパラメータのURLを確認
        if ($program['type'] === 'rakuten' && strpos($url, 'hb.afl.rakuten.co.jp') !== false) {
            preg_match('/pc=([^&]+)/', $url, $pc_matches);
            if (isset($pc_matches[1])) {
                $pc_url = urldecode($pc_matches[1]);
                if (!self::is_trusted_domain($pc_url)) {
                    return array(
                        'valid' => false,
                        'reason' => '楽天のリダイレクト先が信頼できないドメインです: ' . $pc_url
                    );
                }
            }
        }
        
        return array('valid' => true);
    }
    
    /**
     * バッチでリンクを検証（管理画面用）
     */
    public static function validate_all_links($programs) {
        $results = array();
        
        foreach ($programs as $key => $program) {
            if (!$program['enabled']) {
                continue;
            }
            
            // テスト用に東京でURL生成
            $test_url = self::generate_test_url($program, '東京');
            $validation = self::validate_affiliate_link($test_url, $program);
            
            $results[$key] = array(
                'program_name' => $program['name'],
                'test_url' => $test_url,
                'validation' => $validation
            );
        }
        
        return $results;
    }
    
    /**
     * テスト用URL生成
     */
    private static function generate_test_url($program, $city = '東京') {
        $url = $program['url_template'];
        
        // 楽天の場合
        if ($program['type'] === 'rakuten') {
            $url = str_replace('{CITY}', rawurlencode($city), $url);
            $url = str_replace('{AFFILIATE_ID}', $program['affiliate_id'], $url);
            if (isset($program['application_id'])) {
                $url = str_replace('{APPLICATION_ID}', $program['application_id'], $url);
            }
        }
        // A8の場合
        elseif ($program['type'] === 'a8') {
            $media_id = isset($program['media_id']) ? $program['media_id'] : 'a17092772583';
            $media_id_clean = ltrim($media_id, 'a');
            
            // サイトIDを取得
            $site_ids = array(
                '5350' => '5350001',
                '23449' => '23449001',
                '5011' => '5011001',
                '4196' => '4196001',
                '15359' => '15359001',
                '23814' => '23814001',
                '22834' => '22834001',
                '22658' => '22658001',
                '23641' => '23641001',
                '2221' => '2221001',
                '4940' => '4940001',
                '16314' => '16314001',
                '22763' => '22763001',
            );
            
            $site_id = isset($site_ids[$program['program_id']]) ? $site_ids[$program['program_id']] : '0000001';
            $a8mat = $media_id_clean . '+s00000' . $site_id;
            
            $url = str_replace('{A8MAT}', $a8mat, $url);
            $url = str_replace('{CITY}', rawurlencode($city), $url);
            $url = str_replace('{MEDIA_ID}', $media_id_clean, $url);
        }
        
        return $url;
    }
}