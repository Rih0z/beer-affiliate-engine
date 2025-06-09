=== Beer Affiliate Engine ===
Contributors: rihobeer
Tags: affiliate, beer, travel, craft beer, auto-detection
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

記事内の地域名を自動検出してビール旅行アフィリエイトリンクを生成するWordPressプラグイン

== Description ==

Beer Affiliate Engineは、ビールに関する記事内の地域名を自動的に検出し、その地域への旅行やビール体験に関するアフィリエイトリンクを記事の最後に追加するプラグインです。

= 主な機能 =

* 記事内の地域名を自動検出
* アフィリエイトプログラムを自由に追加・管理
* 楽天トラベル、A8.netなど主要サービスに対応
* 管理画面から簡単設定
* ユーザーフレンドリーなリンク表示

= 対応地域 =

**国内都市**: 東京、大阪、京都、札幌、福岡、横浜、名古屋、神戸、仙台、金沢、広島、那覇など

**海外都市**: シアトル、ポートランド、サンディエゴ、ミュンヘン、ベルリン、プラハ、ブリュッセル、ダブリン、アムステルダムなど

== Installation ==

1. プラグインファイルを `/wp-content/plugins/beer-affiliate-engine-auto/` ディレクトリにアップロード
2. WordPressの'プラグイン'メニューからプラグインを有効化
3. 管理メニューの「Beer Affiliate」から設定

== Frequently Asked Questions ==

= どのような記事にリンクが表示されますか？ =

「ビール」「beer」「IPA」「エール」「スタウト」「ラガー」「ブルワリー」「醸造所」「クラフトビール」などのキーワードを含む記事で、かつ地域名が含まれている場合に自動的にリンクが表示されます。

= アフィリエイトプログラムの追加方法は？ =

管理画面の「Beer Affiliate」→「プログラム管理」から新規プログラムを追加できます。URLテンプレートに変数を使用して動的なリンクを作成できます。

= 使用できる変数は？ =

* {CITY} - 検出された都市名
* {AFFILIATE_ID} - アフィリエイトID（楽天）
* {APPLICATION_ID} - アプリケーションID（楽天）
* {PROGRAM_ID} - プログラムID（A8.net）
* {MEDIA_ID} - メディアID（A8.net）
* {COUNTRY} - 国名（海外都市の場合）

== Screenshots ==

1. 管理画面のプログラム管理
2. フロントエンドでのリンク表示
3. 使い方ガイド

== Changelog ==

= 2.0.0 =
* 記事内容から地域名を自動検出する機能を追加
* プログラムを自由に追加・管理できる機能を追加
* 管理画面を全面リニューアル
* パフォーマンスの最適化

= 1.0.0 =
* 初回リリース

== Upgrade Notice ==

= 2.0.0 =
この版は自動検出機能を搭載した大幅アップデート版です。設定方法が変更されているため、アップグレード後は管理画面から再設定してください。