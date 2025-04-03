# クラフトビールアフィリエイト自動最適化プラグイン実装手順

## 1. 開発環境の準備

### 1.1 必要なツール
- テキストエディタまたはコードエディタ（VSCode、Sublime Textなど）
- FTPクライアント（FileZilla、Cyberduckなど）またはWordPressのファイルマネージャー
- WordPressサイトの管理者アクセス権

### 1.2 プラグインのディレクトリ構造を作成

```
beer-affiliate-engine/
├── includes/
├── modules/
│   └── travel/
│       └── images/
├── templates/
├── assets/
│   ├── css/
│   └── js/
└── languages/
```

## 2. 基本ファイルの作成

### 2.1 メインプラグインファイル
`beer-affiliate-engine.php` を作成し、前述のコードを貼り付け

### 2.2 コアファイル
次のファイルをそれぞれ作成し、対応するコードを貼り付け：

- `includes/interface-affiliate-module.php`
- `includes/class-base-affiliate-module.php`
- `includes/class-core.php`
- `includes/class-module-manager.php`
- `includes/class-data-store.php`
- `includes/class-customizer.php`

### 2.3 旅行モジュールファイル
次のファイルを作成し、対応するコードを貼り付け：

- `modules/travel/class-travel-module.php`
- `modules/travel/class-travel-content-analyzer.php`
- `modules/travel/class-travel-link-generator.php`
- `modules/travel/class-travel-display-manager.php`

### 2.4 データファイル
次のJSONファイルを作成し、対応するコードを貼り付け：

- `modules/travel/city-dictionary.json`
- `modules/travel/link-templates.json`

### 2.5 アセットファイル
次のCSSとJavaScriptファイルを作成：

- `assets/css/main.css`
- `assets/js/sticky.js`

## 3. アフィリエイト設定の準備

### 3.1 楽天アフィリエイトの設定
1. [楽天アフィリエイト](https://affiliate.rakuten.co.jp/)にログイン
2. 楽天トラベルと提携
3. アフィリエイトIDを取得
4. `link-templates.json`の楽天トラベル部分を編集：
```json
"楽天トラベル": {
  ...
  "affiliate_id": "あなたの楽天アフィリエイトID",
  ...
}
```

### 3.2 A8.netアフィリエイトの設定
1. [A8.net](https://www.a8.net/)にログイン
2. 使用したい旅行会社（JTB、HIS、エクスペディアなど）と提携申請
3. 承認後、各サービスのプログラムページに移動
4. 「リンク作成」または「バナー/テキスト」からリンクを作成
5. 生成されたリンクからプログラムIDを抽出
   例: `https://px.a8.net/svt/ejp?a8mat=3ABCDE+FGHIJK+1234+5678XY&...`
   この場合、`3ABCDE+FGHIJK+1234+5678XY`がプログラムID
6. `link-templates.json`の各サービス部分を編集：
```json
"JTB": {
  ...
  "program_id": "抽出したプログラムID",
  ...
},
"HIS": {
  ...
  "program_id": "抽出したプログラムID",
  ...
},
"エクスペディア": {
  ...
  "program_id": "抽出したプログラムID",
  ...
}
```

### 3.3 地域画像の準備
1. 各地域の代表的な画像を用意（理想的にはクラフトビール関連）
2. 次のように名前を付けて保存：
   - `tokyo.jpg`
   - `osaka.jpg`
   - `seattle.jpg`
   - `los_angeles.jpg`
   - `san_diego.jpg`
   - `new_york.jpg`
   - `vancouver.jpg`
   - `portland.jpg`
   など
3. これらの画像を`modules/travel/images/`ディレクトリに配置

## 4. プラグインのインストール

### 4.1 ファイルのアップロード
1. 作成したディレクトリとファイルをすべてZIPファイルに圧縮
2. WordPressの管理画面で「プラグイン」→「新規追加」→「プラグインのアップロード」をクリック
3. 圧縮したZIPファイルを選択してアップロード
4. または、FTPを使用して`wp-content/plugins/`ディレクトリに直接アップロード

### 4.2 プラグインの有効化
1. WordPressの管理画面で「プラグイン」に移動
2. 「Beer Affiliate Engine」を探して「有効化」をクリック

## 5. プラグインの設定

### 5.1 カスタマイザーでの設定
1. WordPressの管理画面で「外観」→「カスタマイズ」をクリック
2. 「ビールアフィリエイト設定」セクションを開く
3. 次の設定を行う：
   - 表示テンプレート：カード表示、ボタン表示、またはスクロール追従表示を選択
   - 記事末尾に自動挿入：有効または無効を選択
   - 最大表示リンク数：表示する最大リンク数を設定（1〜5）
   - 海外都市への対応を有効化：チェックを入れる
   - 優先国際旅行サービス：エクスペディアまたはHotelsCombinedを選択
4. 「公開」をクリックして設定を保存

## 6. プラグインの使用と動作確認

### 6.1 自動挿入の確認
1. クラフトビールに関する記事を作成または編集
2. 記事内に都市名（「東京」「シアトル」「ポートランド」など）を含める
3. 記事を公開または更新
4. フロントエンドで記事を表示し、末尾にアフィリエイトリンクが表示されるか確認

### 6.2 ショートコードの使用
1. 記事編集画面で、アフィリエイトリンクを表示したい位置に次のショートコードを挿入：
```
[beer_affiliate]
```
2. オプションを指定する場合：
```
[beer_affiliate template="card" max_links="2"]
```
3. 記事を公開または更新し、ショートコードの位置にアフィリエイトリンクが表示されるか確認

### 6.3 国際対応の確認
1. 北米の都市名（「シアトル」「ポートランド」など）を含む記事を作成
2. 記事を公開し、適切な国際旅行サービス（エクスペディアなど）のリンクが表示されるか確認

## 7. トラブルシューティング

### 7.1 リンクが表示されない場合
1. 記事に都市名が正確に含まれているか確認
2. `city-dictionary.json`の都市名と一致しているか確認
3. アフィリエイトIDとプログラムIDが正しく設定されているか確認
4. WordPressのデバッグモードを有効にしてエラーを確認

### 7.2 アフィリエイトリンクが正しく動作しない場合
1. 生成されたリンクをクリックして、正しいページにリダイレクトされるか確認
2. A8.netで提供されている正確なリンク形式と一致しているか確認
3. 必要に応じて`link-templates.json`のURL形式を修正

この手順に従えば、クラフトビール記事から自動的に国内外の旅行アフィリエイトリンクを生成するプラグインが実装できます。何か問題があれば、具体的な箇所を特定して対処してください。
