# クラフトビールアフィリエイト自動最適化プラグイン実装手順書

## 1. 準備作業

### 1.1 必要なツール

- ローカル開発環境（XAMPP, MAMP, Local by Flywheel など）
- WordPressの動作環境
- コードエディタ（Visual Studio Code, Sublime Text など）
- FTPクライアント（必要に応じて）

### 1.2 ファイル構造の作成

以下のディレクトリ構造を作成します:

```
beer-affiliate-engine/
├── beer-affiliate-engine.php     // メインプラグインファイル
├── includes/                     // コア機能フォルダ
├── modules/                      // モジュールフォルダ
│   └── travel/                   // 旅行モジュール
│       ├── images/               // 地域画像
│       ├── city-dictionary.json  // 地域名辞書
│       └── link-templates.json   // リンクテンプレート
├── templates/                    // 表示テンプレート
├── assets/                       // アセットファイル
│   ├── css/                      // CSSファイル
│   │   └── main.css              // メインスタイルシート
│   └── js/                       // JavaScriptファイル
│       └── sticky.js             // スクロール追従用JS
└── languages/                    // 翻訳ファイル
```

## 2. コアファイルの実装

### 2.1 メインプラグインファイル

`beer-affiliate-engine.php` を作成し、コードを実装します。

### 2.2 インクルードファイルの作成

`includes` ディレクトリに以下のファイルを作成します:

1. `class-core.php` - プラグインコア機能
2. `interface-affiliate-module.php` - モジュールインターフェース
3. `class-base-affiliate-module.php` - 基本モジュールクラス
4. `class-module-manager.php` - モジュール管理クラス
5. `class-data-store.php` - データストアクラス
6. `class-customizer.php` - カスタマイザークラス

## 3. 旅行モジュールの実装

### 3.1 モジュールクラスファイルの作成

`modules/travel` ディレクトリに以下のファイルを作成します:

1. `class-travel-module.php` - 旅行モジュールクラス
2. `class-travel-content-analyzer.php` - コンテンツ解析クラス
3. `class-travel-link-generator.php` - リンク生成クラス
4. `class-travel-display-manager.php` - 表示管理クラス

### 3.2 データファイルの作成

1. `city-dictionary.json` - 地域名辞書ファイル
2. `link-templates.json` - リンクテンプレートファイル

### 3.3 画像ファイルの配置

`modules/travel/images` ディレクトリに地域の画像ファイルを配置します:

- `tokyo.jpg`
- `osaka.jpg`
- `kyoto.jpg`
- など

## 4. アセットファイルの実装

### 4.1 CSSファイルの作成

`assets/css/main.css` を作成し、スタイルを実装します。

### 4.2 JavaScriptファイルの作成

`assets/js/sticky.js` を作成し、スクロール追従機能を実装します。

## 5. 実装手順

### 5.1 プラグインの基本構造を作成

1. プラグインディレクトリを作成します。
   ```bash
   mkdir -p beer-affiliate-engine/{includes,modules/travel/images,templates,assets/{css,js},languages}
   ```

2. メインプラグインファイルを作成します。
   ```bash
   touch beer-affiliate-engine/beer-affiliate-engine.php
   ```

3. 必要なコアファイルを作成します。
   ```bash
   touch beer-affiliate-engine/includes/{class-core.php,interface-affiliate-module.php,class-base-affiliate-module.php,class-module-manager.php,class-data-store.php,class-customizer.php}
   ```

### 5.2 モジュールファイルを作成

1. 旅行モジュールのファイルを作成します。
   ```bash
   touch beer-affiliate-engine/modules/travel/{class-travel-module.php,class-travel-content-analyzer.php,class-travel-link-generator.php,class-travel-display-manager.php,city-dictionary.json,link-templates.json}
   ```

2. アセットファイルを作成します。
   ```bash
   touch beer-affiliate-engine/assets/css/main.css
   touch beer-affiliate-engine/assets/js/sticky.js
   ```

### 5.3 各ファイルにコードを実装

前述のコードを各ファイルにコピーします。ファイル名とコードが一致していることを確認してください。

### 5.4 アフィリエイトIDの設定

`link-templates.json` ファイル内のアフィリエイトIDを、実際のアフィリエイトIDに置き換えます:

```json
"affiliate_id": "your-rakuten-affiliate-id"
```

を実際のIDに変更:

```json
"affiliate_id": "123456789"
```

### 5.5 画像ファイルの準備

地域名辞書で定義した画像ファイルを `modules/travel/images` ディレクトリに配置します。

## 6. インストールと動作確認

### 6.1 プラグインのインストール

1. プラグインフォルダ全体を WordPress の `wp-content/plugins` ディレクトリにアップロードします。

2. WordPress 管理画面からプラグインを有効化します。
   - 管理画面 → プラグイン → 「クラフトビールアフィリエイト自動最適化プラグイン」を有効化

### 6.2 プラグインの設定

1. WordPress カスタマイザーから設定を行います。
   - 管理画面 → 外観 → カスタマイズ → ビールアフィリエイト設定

2. 以下の設定を確認・変更します:
   - 表示テンプレート: カード表示/ボタン表示/スクロール追従表示
   - 記事末尾に自動挿入: 有効/無効
   - 最大表示リンク数: 1〜5
   - 各サービスのアフィリエイトID

### 6.3 動作確認

1. 地域名を含む記事を作成し、プラグインの動作を確認します。

2. 自動挿入を有効にしている場合は、記事の末尾にアフィリエイトリンクが表示されることを確認します。

3. 手動でショートコードを挿入する場合は、以下のコードを記事内に追加します:
   ```
   [beer_affiliate]
   ```
   
4. 表示されたリンクをクリックして、正しいアフィリエイトURLにリダイレクトされることを確認します。

## 7. カスタマイズと拡張

### 7.1 テンプレートのカスタマイズ

`class-travel-display-manager.php` 内の表示テンプレートを編集して、デザインをカスタマイズできます。

### 7.2 地域名辞書の拡張

`city-dictionary.json` に新しい地域を追加することで、対応する地域を増やすことができます。

### 7.3 新しいモジュールの追加（将来拡張）

1. `modules` ディレクトリに新しいモジュール用のフォルダを作成します。
   ```bash
   mkdir -p beer-affiliate-engine/modules/beer-shop
   ```

2. 必要なクラスファイルを作成し、 `Base_Affiliate_Module` を継承したモジュールクラスを実装します。

3. プラグインのコアで新しいモジュールを登録します。

## 8. トラブルシューティング

### 8.1 プラグインが動作しない場合

1. WordPress のデバッグモードを有効にして、エラーを確認します。
   ```php
   // wp-config.php に追加
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

2. 各ファイルが正しい場所に配置されているか確認します。

3. PHP のバージョンが 7.2 以上であることを確認します。

### 8.2 リンクが表示されない場合

1. 記事に地域名が含まれているか確認します。

2. 地域名辞書のデータが正しく読み込まれているか確認します。

3. カスタマイザーの設定を確認します。

### 8.3 リンクのクリックが正しく追跡されない場合

1. JavaScript エラーがないか確認します。

2. データベーステーブルが正しく作成されているか確認します。

## 9. 将来の拡張計画

### 9.1 ビールショップモジュール

ビール商品のアフィリエイトを自動生成するモジュールを追加します。

### 9.2 ビールグッズモジュール

ビール関連グッズのアフィリエイトを自動生成するモジュールを追加します。

### 9.3 管理画面の追加

プラグイン専用の管理画面を追加し、より詳細な設定やレポートを提供します。

### 9.4 統計レポート機能

クリック数や収益などの統計情報を表示する機能を追加します。

## 10. まとめ

今回実装したプラグインは、クラフトビール記事から自動的に関連する旅行先のアフィリエイトリンクを生成する基本機能を持っています。モジュール式の設計により、将来的な拡張も容易に行えます。

初期フェーズでは旅行アフィリエイトに特化していますが、設計の拡張性を活かして他の商品カテゴリーへの展開も可能です。

実装にあたっては、WordPressの標準機能や無料APIのみを使用して、追加課金なしで実現できる点が特徴です。
