# アフィリエイト設定テンプレート

このフォルダには、Beer Affiliate Engineの設定に使用できるテンプレートファイルが含まれています。

## ファイル一覧

### affiliate-config-sample.json
アフィリエイトIDとプログラムIDを一括設定するためのテンプレートです。

使い方：
1. このファイルをダウンロード
2. `YOUR_`で始まる部分を実際のIDに置き換え
3. WordPressの管理画面 > 設定 > Beer Affiliate Import からアップロード

### link-templates-example.json
各アフィリエイトサービスの設定例です（参考用）。

## セキュリティ上の注意

⚠️ **重要**: 実際のアフィリエイトIDが含まれたファイルは、GitHubなどの公開リポジトリにアップロードしないでください。

## 設定方法

### 方法1: 管理画面からファイルをアップロード（推奨）
1. `affiliate-config-sample.json`をダウンロード
2. テキストエディタで開き、IDを入力
3. 管理画面からアップロード

### 方法2: FTPで直接アップロード
1. 設定済みのJSONファイルを`wp-content/uploads/beer-affiliate/`にアップロード
2. 管理画面から読み込み

### 方法3: wp-config.phpに直接記述
```php
define('BEER_AFFILIATE_RAKUTEN_ID', 'your-id-here');
define('BEER_AFFILIATE_A8_ID', 'your-id-here');
```

## トラブルシューティング

- **アップロードエラー**: JSONファイルの形式が正しいか確認
- **設定が反映されない**: キャッシュをクリア
- **リンクが表示されない**: プログラムIDが正しく設定されているか確認