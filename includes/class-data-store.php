<?php
/**
 * データストアクラス
 * キャッシュやデータの永続化を管理
 */
class Beer_Affiliate_Data_Store {
    /**
     * キャッシュプレフィックス
     * 
     * @var string
     */
    private $cache_prefix = 'beer_affiliate_';
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // 必要な初期化処理があればここに
    }
    
    /**
     * キャッシュからデータを取得
     * 
     * @param string $key キャッシュキー
     * @return mixed キャッシュされたデータまたはfalse
     */
    public function get_cache($key) {
        return get_transient($this->cache_prefix . $key);
    }
    
    /**
     * データをキャッシュに保存
     * 
     * @param string $key キャッシュキー
     * @param mixed $data 保存するデータ
     * @param int $expiration 有効期限（秒）
     * @return boolean 保存が成功したかどうか
     */
    public function set_cache($key, $data, $expiration = 3600) {
        return set_transient($this->cache_prefix . $key, $data, $expiration);
    }
    
    /**
     * キャッシュからデータを削除
     * 
     * @param string $key キャッシュキー
     * @return boolean 削除が成功したかどうか
     */
    public function delete_cache($key) {
        return delete_transient($this->cache_prefix . $key);
    }
    
    /**
     * 投稿IDに関連するすべてのキャッシュを削除
     * 
     * @param int $post_id 投稿ID
     */
    public function clear_post_cache($post_id) {
        global $wpdb;
        
        // 投稿IDに関連するキャッシュを検索して削除
        $like = $wpdb->esc_like($this->cache_prefix . 'post_' . $post_id . '_') . '%';
        $sql = $wpdb->prepare(
            "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
            $like
        );
        
        $transients = $wpdb->get_col($sql);
        
        foreach ($transients as $transient) {
            // _transientプレフィックスを削除
            $key = str_replace('_transient_', '', $transient);
            delete_transient($key);
        }
    }
    
    /**
     * WordPressオプションにデータを保存
     * 
     * @param string $key オプションキー
     * @param mixed $value 保存する値
     * @param boolean $autoload 自動読み込みするかどうか
     * @return boolean 保存が成功したかどうか
     */
    public function save_option($key, $value, $autoload = true) {
        return update_option($this->cache_prefix . $key, $value, $autoload);
    }
    
    /**
     * WordPressオプションからデータを取得
     * 
     * @param string $key オプションキー
     * @param mixed $default デフォルト値
     * @return mixed オプション値
     */
    public function get_option($key, $default = false) {
        return get_option($this->cache_prefix . $key, $default);
    }
    
    /**
     * WordPressオプションからデータを削除
     * 
     * @param string $key オプションキー
     * @return boolean 削除が成功したかどうか
     */
    public function delete_option($key) {
        return delete_option($this->cache_prefix . $key);
    }
    
    /**
     * クリック統計を記録（将来実装用）
     * 
     * @param array $data 記録するデータ
     * @return boolean|int 成功した場合はIDまたはfalse
     */
    public function log_click($data) {
        global $wpdb;
        
        // テーブル名を取得
        $table_name = $wpdb->prefix . 'beer_affiliate_clicks';
        
        // データを挿入
        $result = $wpdb->insert(
            $table_name,
            array(
                'post_id' => isset($data['post_id']) ? intval($data['post_id']) : 0,
                'module' => isset($data['module']) ? sanitize_text_field($data['module']) : '',
                'keyword' => isset($data['keyword']) ? sanitize_text_field($data['keyword']) : '',
                'affiliate' => isset($data['affiliate']) ? sanitize_text_field($data['affiliate']) : '',
                'ip_address' => isset($data['ip_address']) ? sanitize_text_field($data['ip_address']) : '',
                'user_agent' => isset($data['user_agent']) ? sanitize_text_field($data['user_agent']) : '',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if (false !== $result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * データベーステーブルを作成（アクティベーション時に使用）
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // クリック統計テーブル
        $table_name = $wpdb->prefix . 'beer_affiliate_clicks';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            module varchar(50) NOT NULL,
            keyword varchar(255) NOT NULL,
            affiliate varchar(100) NOT NULL,
            ip_address varchar(100) NOT NULL,
            user_agent varchar(255) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
