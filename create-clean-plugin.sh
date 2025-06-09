#!/bin/bash

# クリーンなプラグインパッケージを作成
echo "クリーンなプラグインパッケージを作成中..."

# 一時ディレクトリ作成
TEMP_DIR="/tmp/beer-affiliate-clean-$$"
PLUGIN_DIR="$TEMP_DIR/beer-affiliate-engine"
mkdir -p "$PLUGIN_DIR"

# 必要最小限のファイルのみコピー
echo "必要なファイルをコピー中..."

# メインファイル
cp beer-affiliate-clean.php "$PLUGIN_DIR/beer-affiliate-engine.php"

# アセット
mkdir -p "$PLUGIN_DIR/assets/css"
mkdir -p "$PLUGIN_DIR/assets/js"
mkdir -p "$PLUGIN_DIR/assets/images"

# CSSファイル
if [ -f "assets/css/main.css" ]; then
    cp assets/css/main.css "$PLUGIN_DIR/assets/css/"
else
    # 最小限のCSSを作成
    cat > "$PLUGIN_DIR/assets/css/main.css" << 'EOF'
.beer-affiliate-container {
    margin: 20px 0;
    padding: 20px;
    border: 1px solid #ddd;
    background: #f9f9f9;
    border-radius: 5px;
}

.beer-affiliate-link {
    display: inline-block;
    margin: 10px 0;
    padding: 10px 20px;
    background: #0095d9;
    color: white;
    text-decoration: none;
    border-radius: 3px;
    transition: background 0.3s;
}

.beer-affiliate-link:hover {
    background: #1e50a2;
    color: white;
}
EOF
fi

# READMEファイル
cat > "$PLUGIN_DIR/readme.txt" << 'EOF'
=== Beer Affiliate Engine ===
Contributors: rihobeer
Tags: affiliate, travel, beer
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

クラフトビール記事の地域情報から旅行アフィリエイトリンクを自動生成するプラグイン

== Description ==

Beer Affiliate Engineは、クラフトビールに関する記事から地域情報を自動検出し、
関連する旅行アフィリエイトリンクを生成するWordPressプラグインです。

== Installation ==

1. プラグインファイルを `/wp-content/plugins/beer-affiliate-engine` ディレクトリにアップロード
2. WordPressの「プラグイン」メニューからプラグインを有効化
3. 「設定」→「Beer Affiliate」から設定を行う

== Changelog ==

= 1.5.0 =
* 初回リリース
* 基本機能の実装
EOF

# インデックスファイル（セキュリティ）
echo "<?php // Silence is golden" > "$PLUGIN_DIR/index.php"
echo "<?php // Silence is golden" > "$PLUGIN_DIR/assets/index.php"

# ZIPファイルを作成
cd "$TEMP_DIR"
zip -r beer-affiliate-engine-v1.5.0-clean.zip beer-affiliate-engine
mv beer-affiliate-engine-v1.5.0-clean.zip "$OLDPWD/../"

# クリーンアップ
rm -rf "$TEMP_DIR"

echo "✅ 完了: ../beer-affiliate-engine-v1.5.0-clean.zip"
echo ""
echo "ZIPファイルの内容:"
unzip -l "$OLDPWD/../beer-affiliate-engine-v1.5.0-clean.zip"