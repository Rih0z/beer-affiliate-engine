# クラフトビールアフィリエイト自動最適化プラグイン コード規約書

## 1. プロジェクト概要

このドキュメントは、クラフトビールブログのアフィリエイト自動最適化WordPressプラグインの開発および保守に関するコード規約と設計仕様を定義します。本プラグインは、記事内の地域名を検出し、対応する旅行アフィリエイトリンクを自動的に生成・表示することで、ブログの収益化を支援するものです。

### 1.1 主な機能

- 記事内の国内外の地域名を自動検出
- 各地域に対応する旅行アフィリエイトリンクの生成
- 複数表示形式（カード、ボタン、スクロール追従）のサポート
- 国内/海外判別と適切なサービス選択
- プラグイン設定のカスタマイズ

### 1.2 コードアーキテクチャ

モジュール化された拡張可能なアーキテクチャを採用し、以下の設計原則に従います：

- インターフェースによる抽象化
- 単一責任の原則
- 依存性の注入
- 適切なキャッシング
- フックによる拡張性

## 2. ファイル構造と命名規則

### 2.1 ディレクトリ構造

```
beer-affiliate-engine/
│
├── beer-affiliate-engine.php                    # メインプラグインファイル
│
├── includes/                                    # コア機能
│   ├── class-core.php                           # プラグインコア機能
│   ├── interface-affiliate-module.php           # モジュールインターフェース
│   ├── class-base-affiliate-module.php          # 基本モジュールクラス
│   ├── class-module-manager.php                 # モジュール管理クラス
│   ├── class-data-store.php                     # データストアクラス
│   └── class-customizer.php                     # カスタマイザー連携クラス
│
├── modules/                                     # 機能モジュール
│   └── travel/                                  # 旅行アフィリエイトモジュール
│       ├── class-travel-module.php              # 旅行モジュールクラス
│       ├── class-travel-content-analyzer.php    # 地域名抽出クラス
│       ├── class-travel-link-generator.php      # リンク生成クラス
│       ├── class-travel-display-manager.php     # 表示管理クラス
│       ├── city-dictionary.json                 # 地域名辞書データ
│       ├── link-templates.json                  # リンクテンプレートデータ
│       └── images/                              # 地域画像
│
├── templates/                                   # 表示テンプレート
│
├── assets/                                      # アセットファイル
│   ├── css/
│   │   └── main.css                             # メインスタイルシート
│   └── js/
│       └── sticky.js                            # スクロール追従用JavaScript
│
└── languages/                                   # 翻訳ファイル
```

### 2.2 ファイル命名規則

#### 2.2.1 PHP ファイル
- クラスファイル: `class-{クラス名}.php`（ハイフン区切り、小文字）
- インターフェースファイル: `interface-{インターフェース名}.php`
- 抽象クラスファイル: `abstract-{クラス名}.php`
- トレイトファイル: `trait-{トレイト名}.php`

#### 2.2.2 JavaScript ファイル
- 機能別にファイル分割: `{機能名}.js`（小文字、ハイフン区切り）

#### 2.2.3 CSS ファイル
- 機能別にファイル分割: `{機能名}.css`（小文字、ハイフン区切り）

#### 2.2.4 JSON データファイル
- データ内容に基づく命名: `{データ内容}.json`（小文字、ハイフン区切り）

### 2.3 クラス命名規則

- プレフィックスなしのクラス名: `Travel_Module`（アンダースコア区切り、単語の先頭は大文字）
- インターフェース: `Affiliate_Module_Interface`
- 抽象クラス: `Base_Affiliate_Module`

## 3. モジュール依存関係

### 3.1 コア依存関係図

```
Beer_Affiliate_Core
 ├── Affiliate_Module_Manager
 │    └── Affiliate_Module_Interface
 │         └── Base_Affiliate_Module
 │              └── Travel_Module
 ├── Beer_Affiliate_Data_Store
 └── Beer_Affiliate_Customizer
```

### 3.2 旅行モジュール依存関係図

```
Travel_Module
 ├── Travel_Content_Analyzer
 │    └── Beer_Affiliate_Data_Store
 ├── Travel_Link_Generator
 │    └── Beer_Affiliate_Data_Store
 └── Travel_Display_Manager
      └── Beer_Affiliate_Data_Store
```

### 3.3 主要クラスの役割と依存関係

#### 3.3.1 Beer_Affiliate_Core
- **役割**: プラグインの初期化と全体制御
- **依存**: Affiliate_Module_Manager
- **利用箇所**: メインプラグインファイル
- **機能**: モジュールの登録、コンテンツ処理、ショートコード処理

#### 3.3.2 Affiliate_Module_Manager
- **役割**: モジュールの登録と管理
- **依存**: Affiliate_Module_Interface 実装クラス
- **利用箇所**: Beer_Affiliate_Core
- **機能**: モジュールの登録、適用可能なモジュールの特定

#### 3.3.3 Base_Affiliate_Module
- **役割**: 共通モジュール機能の提供
- **依存**: Affiliate_Module_Interface
- **利用箇所**: 各モジュールの基底クラス
- **機能**: 共通プロパティとメソッドの実装

#### 3.3.4 Travel_Module
- **役割**: 旅行アフィリエイト機能の提供
- **依存**: Base_Affiliate_Module, Travel_Content_Analyzer, Travel_Link_Generator, Travel_Display_Manager
- **利用箇所**: Affiliate_Module_Manager
- **機能**: 地域名抽出、リンク生成、表示制御

#### 3.3.5 Beer_Affiliate_Data_Store
- **役割**: データの永続化とキャッシュ
- **依存**: なし
- **利用箇所**: 各モジュールのアナライザー、ジェネレーター、ディスプレイマネージャー
- **機能**: キャッシュの保存と取得、オプションの管理

## 4. データフロー

### 4.1 基本データフロー

```
WordPress コンテンツフィルター → Beer_Affiliate_Core → Affiliate_Module_Manager
 → [適用可能なモジュール] → キーワード抽出 → リンク生成 → 表示 → WordPress 出力
```

### 4.2 詳細データフロー

1. **コンテンツ取得**:
   - WordPress の `the_content` フィルターまたはショートコードを通じてコンテンツを取得

2. **モジュール選定**:
   - モジュールマネージャーが適用可能なモジュールを選定
   - モジュールの優先度に基づいてソート

3. **キーワード抽出**:
   - 各モジュールがキーワード（地域名等）を抽出
   - データストアからキャッシュされた結果を取得または新規解析

4. **リンク生成**:
   - 抽出されたキーワードに基づきアフィリエイトリンクを生成
   - 国内/海外判別に基づいてサービスをフィルタリング

5. **表示処理**:
   - 指定テンプレートで出力を生成
   - CSSとJavaScriptによる装飾と動的機能

6. **最終出力**:
   - 生成されたHTML出力を返却

## 5. コーディング規約

### 5.1 PHP コーディング規約

- [WordPress コーディング規約](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)に準拠
- タブではなく4スペースインデント
- クラスと関数の最初に必ずドキュメンテーションブロックを記述
- 一般公開プロパティやメソッドには `@access public` を記述
- 内部利用のプロパティやメソッドには `@access private` を記述

```php
/**
 * リンクを生成するジェネレータークラス
 *
 * @since 1.0.0
 */
class Travel_Link_Generator {
    /**
     * リンクテンプレート
     *
     * @var array
     * @access private
     */
    private $link_templates;
    
    /**
     * コンストラクタ
     *
     * @access public
     */
    public function __construct() {
        // 初期化
    }
    
    /**
     * リンク生成メソッド
     *
     * @param array $city 都市情報
     * @return array 生成されたリンク
     * @access public
     */
    public function generate($city) {
        // リンク生成ロジック
    }
}
```

### 5.2 JavaScript コーディング規約

- [WordPress JavaScript コーディング規約](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)に準拠
- 関数の前にドキュメントブロックを記述
- セミコロンを省略しない
- 名前空間には即時実行関数を使用
- jQuery を使用する場合は $ の代わりに jQuery を使用

```javascript
/**
 * スクロール追従バナーの動作を制御するスクリプト
 */
(function($) {
    'use strict';
    
    /**
     * デバウンス関数
     *
     * @param {Function} func 実行する関数
     * @param {number} wait 待機時間（ミリ秒）
     * @return {Function} デバウンスされた関数
     */
    function debounce(func, wait) {
        let timeout;
        
        return function() {
            const context = this;
            const args = arguments;
            
            clearTimeout(timeout);
            
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }
    
    // DOM読み込み完了後に実行
    $(document).ready(function() {
        // バナー初期化
    });
})(jQuery);
```

### 5.3 JSON データ構造規約

- 適切なインデント（2スペース）
- キーは必ずダブルクォーテーションで囲む
- 最後のカンマは省略
- コメントは使用せず、必要な場合はドキュメントで説明

```json
{
  "name": "東京",
  "prefecture": "東京都",
  "region": "関東",
  "aliases": ["とうきょう", "Tokyo"],
  "coordinates": {
    "lat": 35.6762,
    "lng": 139.6503
  }
}
```

## 6. 拡張ガイドライン

### 6.1 新しいモジュールの追加方法

1. `modules/{モジュール名}/` ディレクトリを作成
2. 必要なクラスファイルを作成
   - `class-{モジュール名}-module.php` - メインモジュールクラス
   - 必要に応じて追加クラス
3. `Base_Affiliate_Module` を継承したクラスを実装
4. フックポイントを通じてモジュールを登録

```php
// 新モジュールの登録例
function register_my_new_module($module_manager) {
    require_once BEER_AFFILIATE_PLUGIN_DIR . 'modules/my-module/class-my-module.php';
    $my_module = new My_Module();
    $module_manager->register_module($my_module);
}
add_action('beer_affiliate_register_modules', 'register_my_new_module');
```

### 6.2 既存モジュールの拡張方法

既存のモジュールは、以下のフックを通じて機能を拡張できます：

```php
// コンテンツ分析前フィルター
$content = apply_filters('beer_affiliate_before_analysis', $content, $post_id);

// キーワード抽出後フィルター
$keywords = apply_filters('beer_affiliate_keywords', $keywords, $post_id, $module_name);

// リンク生成前フィルター
$params = apply_filters('beer_affiliate_link_params', $params, $keyword, $module_name);

// リンク生成後フィルター
$links = apply_filters('beer_affiliate_links', $links, $keywords, $module_name);

// 表示前フィルター
$output = apply_filters('beer_affiliate_before_display', $output, $links, $template);
```

### 6.3 表示テンプレートの追加方法

1. `templates/{テンプレート名}.php` ファイルを作成
2. ディスプレイマネージャーでテンプレートを登録
3. カスタマイザーオプションに追加

```php
// テンプレート追加例
$wp_customize->add_control('beer_affiliate_template', [
    'label' => __('表示テンプレート', 'beer-affiliate-engine'),
    'section' => 'beer_affiliate_options',
    'settings' => 'beer_affiliate_template',
    'type' => 'select',
    'choices' => [
        'card' => __('カード表示', 'beer-affiliate-engine'),
        'button' => __('ボタン表示', 'beer-affiliate-engine'),
        'sticky' => __('スクロール追従表示', 'beer-affiliate-engine'),
        'new_template' => __('新テンプレート', 'beer-affiliate-engine') // 追加
    ]
]);
```

## 7. エラー処理

### 7.1 エラー処理のガイドライン

- 可能な限り堅牢なエラー処理を実装
- ユーザー向けのエラーメッセージは表示せず、ログに記録
- アフィリエイトリンクが生成できない場合は何も表示しない
- WP_DEBUG モード時には開発者向けのエラー情報を表示

```php
// エラーハンドリング例
try {
    // リスクのある処理
    $result = $this->process_data($data);
    
    if (empty($result)) {
        if (WP_DEBUG) {
            error_log('Beer Affiliate: No results found for: ' . print_r($data, true));
        }
        return '';
    }
    
    return $result;
} catch (Exception $e) {
    if (WP_DEBUG) {
        error_log('Beer Affiliate Error: ' . $e->getMessage());
    }
    return '';
}
```

### 7.2 データバリデーション

- ユーザー入力データは必ず検証・サニタイズ
- JSON データのロード前に存在確認
- 配列アクセス前に必ずキーの存在確認

```php
// データバリデーション例
private function validate_city($city) {
    if (!is_array($city)) {
        return false;
    }
    
    $required_keys = ['name', 'prefecture', 'region'];
    foreach ($required_keys as $key) {
        if (!isset($city[$key]) || empty($city[$key])) {
            return false;
        }
    }
    
    return true;
}
```

## 8. パフォーマンス最適化

### 8.1 キャッシング戦略

- WordPress Transients API を使用したキャッシング
- 重い処理の結果は必ずキャッシュ
- 記事更新時にはキャッシュをクリア
- 地域辞書などの静的データは長期キャッシュ

```php
// キャッシング例
$cache_key = 'beer_affiliate_cities_' . $post_id;
$cached_data = get_transient($cache_key);

if (false !== $cached_data) {
    return $cached_data;
}

// キャッシュがない場合は新規計算
$matched_cities = calculate_city_matches($post_id);

// 24時間キャッシュする
set_transient($cache_key, $matched_cities, 24 * HOUR_IN_SECONDS);

return $matched_cities;
```

### 8.2 リソース最適化

- 必要な場合のみCSS/JSをロード
- 画像は適切にサイズ最適化
- データベースクエリは最小限に抑える
- JSONデータの読み込みはキャッシュ

```php
// 条件付きリソースロード例
function beer_affiliate_enqueue_scripts() {
    global $post;
    
    if (is_singular() && has_shortcode($post->post_content, 'beer_affiliate')) {
        wp_enqueue_style('beer-affiliate-styles', plugin_dir_url(__FILE__) . 'assets/css/main.css');
        
        if (get_option('beer_affiliate_template') === 'sticky') {
            wp_enqueue_script('beer-affiliate-sticky', plugin_dir_url(__FILE__) . 'assets/js/sticky.js', ['jquery'], '1.0', true);
        }
    }
}
add_action('wp_enqueue_scripts', 'beer_affiliate_enqueue_scripts');
```

## 9. 国際化とローカライゼーション

### 9.1 テキストの国際化

- すべてのユーザー表示テキストはローカライズ関数を使用
- テキストドメインは常に 'beer-affiliate-engine' を使用

```php
// 国際化の例
$message = __('表示テンプレート', 'beer-affiliate-engine');
$formatted = sprintf(__('%sのホテルを探す', 'beer-affiliate-engine'), $city_name);
```

### 9.2 言語ファイル

- `languages/` ディレクトリに言語ファイルを配置
- POT ファイルを用意し、翻訳プラットフォームと連携

## 10. ドキュメンテーション

### 10.1 コードコメント

- 各ファイルの先頭にファイル説明を記述
- クラス・メソッド・関数には必ずドキュメンテーションブロックを記述
- 複雑なロジックには行コメントを追加

### 10.2 ユーザーマニュアル

- プラグインの使用方法を記述したマニュアルを用意
- 管理画面での設定方法を具体的に説明
- 動作要件や制限事項を明記

## 11. バージョン管理とデプロイ

### 11.1 バージョン命名規則

- [セマンティックバージョニング](https://semver.org/)に準拠
  - MAJOR.MINOR.PATCH形式
  - MAJOR: 後方互換性のない変更
  - MINOR: 後方互換性のある機能追加
  - PATCH: 後方互換性のあるバグ修正

### 11.2 デプロイチェックリスト

- コードの整形と検証
- 動作テスト（複数環境）
- 言語ファイルの更新
- バージョン番号の更新
- 変更履歴の記録
- 必要に応じたアップグレード処理の実装

## 付録: モジュール間のデータ構造

### 1. 都市データ構造

```json
{
  "name": "東京",
  "prefecture": "東京都",
  "region": "関東",
  "aliases": ["とうきょう", "Tokyo"],
  "keywords": ["観光", "ホテル", "旅行"],
  "coordinates": {
    "lat": 35.6762,
    "lng": 139.6503
  },
  "image_url": "tokyo.jpg",
  "description": "数多くのクラフトビールバーやブルワリーがある日本の首都"
}
```

### 2. 海外都市データ構造

```json
{
  "name": "シアトル",
  "country": "アメリカ",
  "region": "海外",
  "aliases": ["Seattle", "シアトル市"],
  "keywords": ["クラフトビール", "マイクロブルワリー"],
  "coordinates": {
    "lat": 47.6062,
    "lng": -122.3321
  },
  "image_url": "seattle.jpg",
  "description": "アメリカ北西部のクラフトビール先進地"
}
```

### 3. リンクテンプレート構造

```json
{
  "楽天トラベル": {
    "url": "https://travel.rakuten.co.jp/hotel/search/?f_area={CITY}&f_affiliate_id={AFFILIATE_ID}",
    "label": "{CITY}のホテルを楽天トラベルで探す",
    "image": "rakuten.png",
    "affiliate_id": "your-rakuten-affiliate-id",
    "priority": 10
  }
}
```

### 4. A8.net リンクテンプレート構造

```json
{
  "JTB": {
    "url": "https://px.a8.net/svt/ejp?a8mat={PROGRAM_ID}&a8ejpredirect=https://www.jtb.co.jp/kokunai/pkg/city/{CITY}/",
    "label": "JTBで{CITY}の旅行プランを見る",
    "image": "jtb.png",
    "affiliate_id": "a17092772583",
    "program_id": "your-program-id",
    "priority": 8,
    "international_support": true
  }
}
```
