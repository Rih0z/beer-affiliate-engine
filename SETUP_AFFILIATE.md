# アフィリエイト設定ガイド

## 重要：セキュリティについて

**アフィリエイトIDやプログラムIDは機密情報です。GitHubなどの公開リポジトリにアップロードしないでください。**

## 設定方法

### 方法1：JSONファイルをアップロード（最も簡単）

1. WordPress管理画面にログイン
2. 「設定」→「Beer Affiliate Import」に移動
3. `config-templates/affiliate-config-sample.json`をダウンロード
4. ファイルを編集してアフィリエイトIDを入力
5. 管理画面からファイルをアップロード

### 方法2：WordPress管理画面から個別設定

1. WordPress管理画面にログイン
2. 「外観」→「カスタマイズ」→「Beer Affiliate Settings」に移動
3. 各サービスのアフィリエイトIDを入力

### 方法2：wp-config.phpで設定

セキュリティのため、wp-config.phpに定数として設定することも可能です：

```php
// 楽天トラベル
define('BEER_AFFILIATE_RAKUTEN_ID', 'あなたの楽天アフィリエイトID');

// A8.net共通
define('BEER_AFFILIATE_A8_ID', 'あなたのA8アフィリエイトID');

// 各サービスのプログラムID
define('BEER_AFFILIATE_JTB_PROGRAM_ID', 'JTBのプログラムID');
define('BEER_AFFILIATE_HIS_PROGRAM_ID', 'HISのプログラムID');
define('BEER_AFFILIATE_TRAVEL_STANDARD_PROGRAM_ID', 'トラベルスタンダードのプログラムID');
define('BEER_AFFILIATE_JTB_SHOPPING_PROGRAM_ID', 'JTBショッピングのプログラムID');
define('BEER_AFFILIATE_QATAR_PROGRAM_ID', 'カタール航空のプログラムID');
define('BEER_AFFILIATE_TRAVELIST_PROGRAM_ID', 'TravelistのプログラムID');
define('BEER_AFFILIATE_OOOH_PROGRAM_ID', 'OoohのプログラムID');
define('BEER_AFFILIATE_SAILY_PROGRAM_ID', 'SailyのプログラムID');
```

### 方法3：環境変数で設定

.envファイルを使用する場合：

```env
BEER_AFFILIATE_RAKUTEN_ID=あなたの楽天アフィリエイトID
BEER_AFFILIATE_A8_ID=あなたのA8アフィリエイトID
BEER_AFFILIATE_JTB_PROGRAM_ID=JTBのプログラムID
# ... その他のID
```

## アフィリエイトID/プログラムIDの取得方法

### 楽天トラベル
1. [楽天アフィリエイト](https://affiliate.rakuten.co.jp/)に登録
2. 「リンク作成」→「楽天トラベル」を選択
3. リンクコード内の`&a_id=`の後の値がアフィリエイトID

### A8.net
1. [A8.net](https://www.a8.net/)に登録
2. 各プログラムに提携申請
3. 「広告リンク」からリンクコードを取得
4. `a8mat=`の後の値がプログラムID

## テスト方法

設定後、以下の手順でテスト：

1. 地名を含む記事を作成（例：「京都のクラフトビール」）
2. 記事を公開
3. 生成されたリンクをクリックして正しく遷移するか確認

## トラブルシューティング

- **リンクが表示されない**：アフィリエイトIDが正しく設定されているか確認
- **404エラー**：プログラムIDの形式が正しいか確認（スペースや特殊文字に注意）
- **リダイレクトエラー**：A8.netの提携状況を確認