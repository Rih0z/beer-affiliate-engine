# 国際対応版 クラフトビールアフィリエイト自動最適化プラグイン仕様書

## 概要

クラフトビール記事中の地域情報（国内・海外）を元に、関連する旅行アフィリエイト広告（楽天トラベル、JTB、エクスペディア等）を**自動的に記事下部に表示**するWordPress用プラグインです。初期フェーズでは旅行アフィリエイトに特化し、将来的には他の商品カテゴリにも展開可能な拡張性の高い設計を採用します。すべての機能は追加課金の発生しないOSS・無料API・標準機能のみで実装します。

---

## 目的

- クラフトビールブログ（rihobeer.com）の収益化
- E-2ビザ制約下でも合法的にアフィリエイト収益を得る
- ブログ運営者の手間を最小限にし、半自動的な収益化を実現
- 将来的な機能拡張や横展開が容易な設計
- 国内外の地域に対応したアフィリエイト生成

---

## 国際対応の主な特徴

### 1. 北米主要都市対応
- **米国主要都市**: シアトル、ポートランド、ロサンゼルス、サンディエゴ、ニューヨーク
- **カナダ主要都市**: バンクーバー
- 各都市のクラフトビール情報を含む豊富な地域データ

### 2. A8.net アフィリエイト連携
- メディアID: a17092772583 を使用
- 各旅行会社のプログラムIDを設定可能
- リンク生成時に自動的にA8.netパラメータを適用

### 3. 国際旅行専門サービス対応
- エクスペディア（海外ホテル）
- HotelsCombined（国際料金比較）
- 各国に最適化されたパラメータ設定

---

## コアアーキテクチャ（拡張性重視）

### 1. モジュール分離型設計

```
beer-affiliate-engine/
├── beer-affiliate-engine.php     // メインプラグインファイル
├── includes/
│   ├── class-core.php            // コア機能
│   ├── class-content-analyzer.php // コンテンツ解析エンジン
│   ├── class-link-generator.php  // リンク生成エンジン
│   ├── class-display-manager.php // 表示管理
│   └── class-data-store.php      // データストア
├── modules/
│   ├── travel/                   // 旅行アフィリエイトモジュール（初期実装）
│   │   ├── class-travel-module.php
│   │   ├── city-dictionary.json
│   │   └── link-templates.json
│   └── future-modules/           // 将来的なモジュール（プレースホルダー）
│       ├── beer-shop/            // ビールショップモジュール（将来実装）
│       └── beer-goods/           // ビール関連商品モジュール（将来実装）
├── templates/
│   ├── card.php
│   ├── button.php
│   └── sticky.php
└── assets/
    ├── css/
    └── js/
```

### 2. 抽象化レイヤー

```php
/**
 * インターフェース定義：すべてのアフィリエイトモジュールが実装すべきインターフェース
 */
interface Affiliate_Module_Interface {
  // キーワード抽出メソッド
  public function extract_keywords($content);
  
  // リンク生成メソッド
  public function generate_links($keywords);
  
  // 表示テンプレート取得メソッド
  public function get_display_template($links, $template_type = 'card');
}
```

---

## 国際対応実装詳細

### 1. 国内・海外判別機能

```php
// 国内/海外でフィルタリング
$is_international = ($region === '海外' || !empty($country) && $country !== '日本');

// 国際旅行専用サービスの場合
if ($is_international && isset($template['international_support'])) {
    // 国際用のテンプレートを使用
}
```

### 2. 地域名辞書の国際対応構造

国内の地域の構造:
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
  "description": "数多くの最先端クラフトビールバーやブルワリーがある日本の首都"
}
```

海外の地域の構造:
```json
{
  "name": "シアトル",
  "country": "アメリカ",
  "region": "海外",
  "aliases": ["Seattle", "シアトル市"],
  "keywords": ["クラフトビール", "マイクロブルワリー", "スターバックス", "アメリカ"],
  "coordinates": {
    "lat": 47.6062,
    "lng": -122.3321
  },
  "image_url": "seattle.jpg",
  "description": "アメリカ北西部のクラフトビール先進地。多数のマイクロブルワリーと独自のホップ文化が特徴"
}
```

### 3. A8.net アフィリエイトリンク構造

```json
"JTB": {
  "url": "https://px.a8.net/svt/ejp?a8mat={PROGRAM_ID}&a8ejpredirect=https://www.jtb.co.jp/kokunai/pkg/city/{CITY}/?utm_source=affiliate&utm_medium=blog&utm_campaign=beer",
  "label": "JTBで{CITY}の旅行プランを見る",
  "image": "jtb.png",
  "affiliate_id": "a17092772583",
  "program_id": "ここにA8プログラムIDを入力",
  "priority": 8
}
```

海外専用サービスの例:
```json
"エクスペディア": {
  "url": "https://px.a8.net/svt/ejp?a8mat={PROGRAM_ID}&a8ejpredirect=https://www.expedia.co.jp/Hotel-Search?destination={CITY}&utm_source=affiliate&utm_medium=blog&utm_campaign=beerblog",
  "label": "エクスペディアで{CITY}のホテルを探す",
  "image": "expedia.png",
  "affiliate_id": "a17092772583",
  "program_id": "ここにA8プログラムIDを入力",
  "priority": 9,
  "international_support": true,
  "country_params": {
    "アメリカ": "&regionId=6340629",
    "カナダ": "&regionId=3000671"
  }
}
```

### 4. 表示の国際化対応

```php
// 国内・海外で表示を変える例
if ($is_international) {
    $heading = "{$city['name']}（{$city['country']}）のクラフトビール旅";
} else {
    $heading = "{$city['name']}のクラフトビール旅";
}
```

---

## 北米主要都市データ

プラグインには以下の北米主要都市データが含まれています：

### 1. シアトル (Seattle)
- **概要**: アメリカ北西部のクラフトビール先進地
- **特徴**: 多数のマイクロブルワリーと独自のホップ文化
- **対応アフィリエイト**: エクスペディア、HotelsCombined

### 2. ポートランド (Portland)
- **概要**: アメリカのクラフトビール革命の震源地
- **特徴**: 数百もの醸造所が集まるビール天国
- **対応アフィリエイト**: エクスペディア、HotelsCombined

### 3. ロサンゼルス (Los Angeles)
- **概要**: 西海岸スタイルのIPAや実験的なサワービールの拠点
- **特徴**: 革新的なクラフトビールシーンが発展中
- **対応アフィリエイト**: エクスペディア、HotelsCombined

### 4. サンディエゴ (San Diego)
- **概要**: カリフォルニアIPAの聖地
- **特徴**: 150以上の醸造所がある米国最高峰のビール都市のひとつ
- **対応アフィリエイト**: エクスペディア、HotelsCombined

### 5. ニューヨーク (New York)
- **概要**: ブルックリンを中心に発展した洗練されたクラフトビールシーン
- **特徴**: 革新的な醸造所が集結
- **対応アフィリエイト**: エクスペディア、HotelsCombined

### 6. バンクーバー (Vancouver)
- **概要**: カナダ西海岸の美しい自然に囲まれた都市
- **特徴**: ブリティッシュコロンビア州のクラフトビール文化の中心地
- **対応アフィリエイト**: エクスペディア、HotelsCombined

---

## A8.net アフィリエイト設定手順

### 1. 必要なIDの取得
- **メディアID**: a17092772583（既に設定済み）
- **プログラムID**: 各サービス（JTB、HIS、エクスペディアなど）ごとに取得
- **バナーID/テキストリンクID**: 必要に応じて取得

### 2. 設定ファイルへの反映

`link-templates.json` に各サービスのプログラムIDを設定：

```json
"JTB": {
  "url": "https://px.a8.net/svt/ejp?a8mat={PROGRAM_ID}&a8ejpredirect=https://www.jtb.co.jp/...",
  "program_id": "XXXXXXX", // A8で取得したプログラムIDを入力
}
```

### 3. 各サービスのパラメータ設定
- プログラムIDごとに正しいリダイレクトURLフォーマットを確認
- 各サービスの地域別パラメータを最適化

---

## フック・拡張ポイント

将来の拡張を容易にするための主要なフックポイントを提供します：

```php
// 1. 国際対応判定フィルター
$is_international = apply_filters('beer_affiliate_is_international', $is_international, $city);

// 2. 国際リンク生成前フィルター
$url = apply_filters('beer_affiliate_international_url', $url, $city, $service);

// 3. 国/地域パラメータフィルター
$country_param = apply_filters('beer_affiliate_country_param', $country_param, $country, $service);
```

---

## パフォーマンス最適化

```php
// 国内・海外の地域データをそれぞれキャッシュ
$international_cities = $this->data_store->get_cache('international_cities');
$domestic_cities = $this->data_store->get_cache('domestic_cities');

// キャッシュがない場合は取得して保存
if (false === $international_cities) {
    $international_cities = $this->filter_cities_by_type(true);
    $this->data_store->set_cache('international_cities', $international_cities, DAY_IN_SECONDS);
}
```

---

## WordPressとの統合

カスタマイザーに国際対応設定を追加：

```php
// 国際対応設定
$wp_customize->add_setting('beer_affiliate_enable_international', [
    'default' => true,
    'transport' => 'refresh',
    'sanitize_callback' => [$this, 'sanitize_checkbox']
]);

$wp_customize->add_control('beer_affiliate_enable_international', [
    'label' => __('海外都市への対応を有効化', 'beer-affiliate-engine'),
    'section' => 'beer_affiliate_options',
    'settings' => 'beer_affiliate_enable_international',
    'type' => 'checkbox'
]);

// 優先国際サービス設定
$wp_customize->add_setting('beer_affiliate_primary_intl_service', [
    'default' => 'expedia',
    'transport' => 'refresh',
    'sanitize_callback' => 'sanitize_text_field'
]);

$wp_customize->add_control('beer_affiliate_primary_intl_service', [
    'label' => __('優先国際旅行サービス', 'beer-affiliate-engine'),
    'section' => 'beer_affiliate_options',
    'settings' => 'beer_affiliate_primary_intl_service',
    'type' => 'select',
    'choices' => [
        'expedia' => __('エクスペディア', 'beer-affiliate-engine'),
        'hotelscombined' => __('HotelsCombined', 'beer-affiliate-engine')
    ]
]);
```

---

## 実装ロードマップ

### フェーズ1（初期実装）：国内旅行アフィリエイト特化
1. コアプラグイン構造構築
2. 地域名抽出モジュール実装
3. 国内旅行アフィリエイトリンク生成
4. 基本表示テンプレート実装

### フェーズ2（国際対応）：海外旅行アフィリエイト追加
1. 北米主要都市データの追加
2. A8.netによる国際対応リンク生成
3. 国内/海外判別と最適化された表示
4. 国別パラメータ最適化

### フェーズ3（拡張）：
1. モジュール拡張システムの実装
2. ビールショップアフィリエイトモジュール
3. ビール関連商品モジュール
4. 管理画面とカスタマイズオプション拡充

---

## 法的配慮・制約対応

- 使用するアフィリエイトプログラムは全て日本法人（楽天、JTB、HIS、エクスペディア等）
- 収益受け取り口座は日本国内に限定
- 米国での直接契約・労務的作業なし（E-2ビザ遵守）
- プライバシーポリシーへのアフィリエイト利用明記を推奨

---

## まとめ

本プラグインは初期段階で国内旅行アフィリエイトに特化しつつ、北米を中心とした国際対応機能を追加。A8.netを活用した海外旅行アフィリエイトにも対応し、将来的な拡張性を考慮した設計を採用しています。モジュール構造、抽象化レイヤー、そして豊富なフックポイントによって、様々な商品カテゴリーへの横展開が容易に行えるようになっています。

またE-2ビザの制限下でも合法的に収益化を図れる仕組みとなっており、ブログ運営の手間を最小限に抑えつつ効果的な収益化を実現します。
