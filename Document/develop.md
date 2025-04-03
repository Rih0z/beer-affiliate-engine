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

- 国内: `tokyo.jpg`, `osaka.jpg`, `kyoto.jpg` など
- 海外: `seattle.jpg`, `los_angeles.jpg`, `san_diego.jpg`, `new_york.jpg`, `vancouver.jpg`, `portland.jpg` など

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

### 5.3 アフィリエイト設定の実装

`link-templates.json` ファイル内に以下のアフィリエイト情報を設定します:

1. **楽天トラベル** (楽天アフィリエイト)
   ```json
   "affiliate_id": "20a2fc9d.5c6c02f2.20a2fc9e.541a36d0"
   ```

2. **A8.net** (メディアID & プログラムID)
   - メディアID: `a17092772583`
   - JTB国内旅行: `4530O4+61B8KY+15A4+64Z8Z`
   - トラベル・スタンダード・ジャパン: `s00000026123001005000`
   - JTBショッピング: `s00000018449001012000`
   - Oooh(ウー): `4530O4+7VZSC2+5OEM+5YRHE`
   - カタール航空: `4530O4+64AELU+5NMU+5YJRM`
   - Travelist: `4530O4+63OZ02+4XZI+HVFKY`
   - Saily: `4530O4+5WJRQQ+5L2C+5YRHE`

3. **無効サービスの設定**
   ```json
   "エクスペディア": {
     "disabled": true,
     "priority": 1
   },
   "HotelsCombined": {
     "disabled": true,
     "priority": 1
   }
   ```

### 5.4 国際対応機能の実装

`class-travel-link-generator.php` に以下の国際対応機能を実装:

1. **地域タイプの判別**
   ```php
   $is_international = ($region === '海外' || !empty($country) && $country !== '日本');
   ```

2. **都市コード変換**
   ```php
   if (isset($template['city_codes']) && isset($template['city_codes'][$city_name])) {
       $city_code = $template['city_codes'][$city_name];
       $url = str_replace('{CITY_CODE}', $city_code, $url);
   }
   ```

3. **無効サービスのスキップ**
   ```php
   if (isset($template['disabled']) && $template['disabled']) {
       continue;
   }
   ```

4. **カテゴリーフィルタリング**
   ```php
   $category_filter = apply_filters('beer_affiliate_category_filter', 'travel');
   if ($category_filter !== 'all') {
       $links = array_filter($links, function($link) use ($category_filter) {
           return $link['category'] === $category_filter;
       });
   }
   ```

## 6. インストールと設定

### 6.1 プラグインのインストール

1. プラグインフォルダ全体を WordPress の `wp-content/plugins` ディレクトリにアップロードします。

2. WordPress 管理画面からプラグインを有効化します。
   - 管理画面 → プラグイン → 「クラフトビールアフィリエイト自動最適化プラグイン」を有効化

### 6.2 カスタマイザー設定

`class-customizer.php` に以下の設定オプションを実装:

1. **基本設定**
   - 表示テンプレート選択（カード/ボタン/スクロール追従）
   - 記事末尾自動挿入の有効/無効
   - 最大表示リンク数（1-5）

2. **国際対応設定**
   - 海外都市への対応を有効化（チェックボックス）
   - 優先国際旅行サービス選択
   - A8.netメディアID設定

3. **カテゴリー設定**
   - 表示するリンクカテゴリー（旅行/買い物/すべて）

4. **アフィリエイトID設定**
   - 楽天トラベルアフィリエイトID
   - 各サービスのA8.netプログラムID

## 7. カスタマイザーでの設定

1. WordPress 管理画面で「外観」→「カスタマイズ」をクリック。

2. 「ビールアフィリエイト設定」セクションで以下の項目を設定:
   - 表示テンプレート: カード表示/ボタン表示/スクロール追従表示
   - 記事末尾に自動挿入: 有効/無効
   - 最大表示リンク数: 1〜5
   - 海外都市への対応を有効化: チェック
   - 優先国際旅行サービス: Travelist/トラベル・スタンダード・ジャパン/Oooh/カタール航空
   - 表示するリンクカテゴリー: 旅行のみ/買い物のみ/すべて表示

3. 「公開」をクリックして設定を保存します。

## 8. 動作確認

### 8.1 国内都市テスト

1. 「東京」「大阪」「京都」などの都市名を含む記事を作成。
2. 記事末尾に楽天トラベル、JTBのリンクが表示されることを確認。

### 8.2 国際都市テスト

1. 「シアトル」「ポートランド」「ニューヨーク」などの都市名を含む記事を作成。
2. 記事末尾に国際旅行サービス（Travelist、トラベル・スタンダード・ジャパン等）のリンクが表示されることを確認。

### 8.3 カテゴリーフィルタリングテスト

1. カスタマイザーで「表示するリンクカテゴリー」を変更。
2. 各設定で適切なカテゴリーのリンクのみが表示されることを確認。

## 9. トラブルシューティング

### 9.1 リンクが表示されない

1. `city-dictionary.json` に記事で使用している地域名が含まれているか確認
2. アフィリエイトIDが正しく設定されているか確認
3. カテゴリーフィルターが適切に設定されているか確認
4. デバッグモードを有効にしてエラーを確認

### 9.2 画像が表示されない

1. 画像ファイルが正しい名前で正しい場所に配置されているか確認
2. 画像ファイルのパーミッションが適切か確認（644推奨）

### 9.3 国際リンクのみ表示される/されない

1. `is_international` の判定ロジックを確認
2. 地域名辞書の `region` や `country` が正しく設定されているか確認

## 10. 拡張ガイド

### 10.1 新しい地域の追加

`city-dictionary.json` に新しい地域を追加:

```json
{
  "name": "シカゴ",
  "country": "アメリカ",
  "region": "海外",
  "aliases": ["Chicago", "シカゴ市"],
  "keywords": ["クラフトビール", "アメリカ"],
  "coordinates": {
    "lat": 41.8781,
    "lng": -87.6298
  },
  "image_url": "chicago.jpg",
  "description": "中西部最大の都市。多様なクラフトビールシーンが発展中"
}
```

### 10.2 新しいアフィリエイトサービスの追加

`link-templates.json` に新しいサービスを追加:

```json
"新サービス名": {
  "url": "https://example.com/?city={CITY}&affiliate_id={AFFILIATE_ID}",
  "label": "{CITY}の新サービス",
  "image": "new-service.png",
  "affiliate_id": "your-affiliate-id",
  "priority": 5,
  "category": "travel"
}
```

### 10.3 新しいカテゴリーの追加

1. カスタマイザーに新カテゴリーを追加:

```php
'choices' => array(
    'travel' => __('旅行のみ', 'beer-affiliate-engine'),
    'shopping' => __('買い物のみ', 'beer-affiliate-engine'),
    'beer' => __('ビールショップのみ', 'beer-affiliate-engine'),
    'all' => __('すべて表示', 'beer-affiliate-engine')
)
```

2. リンクテンプレートで新カテゴリーを指定:

```json
"category": "beer"
```

## 11. 定期メンテナンス

### 11.1 アフィリエイトIDの確認

6ヶ月ごとに以下を確認:
- 楽天アフィリエイトIDの有効性
- A8.netプログラムIDの有効性
- A8.netに新しい旅行関連広告主が追加されていないか

### 11.2 新規地域の追加

クラフトビール記事で言及される新しい地域があれば:
- `city-dictionary.json` に地域情報を追加
- 必要に応じて地域画像を追加

### 11.3 パフォーマンス最適化

定期的にキャッシュを確認し、必要に応じて:
- 各種キャッシュの有効期間を調整
- 画像サイズの最適化
- リソース読み込みの条件を見直し

## まとめ

このプラグインは、クラフトビール記事から自動的に地域名を検出し、対応する旅行アフィリエイトリンクを生成する機能を提供します。楽天アフィリエイトとA8.netのサービスに対応し、国内・海外の両方の地域をサポートします。カテゴリー分類により、将来的に旅行以外のアフィリエイトにも容易に拡張可能な設計となっています。
