#!/bin/bash

# Beer Affiliate Engine ビルドスクリプト
# 使用方法: ./build-plugin.sh [config-file]

set -e

# カラー出力
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Beer Affiliate Engine ビルドスクリプト${NC}"
echo "======================================"

# 設定ファイルのパス（デフォルトはaffiliate-config.json）
CONFIG_FILE="${1:-affiliate-config.json}"

# affiliate-config.jsonが存在しない場合はaffiliate-config.json.exampleから作成
if [ ! -f "$CONFIG_FILE" ] && [ -f "affiliate-config.json.example" ]; then
    echo -e "${YELLOW}affiliate-config.jsonが見つかりません。サンプルから作成します。${NC}"
    cp affiliate-config.json.example affiliate-config.json
    CONFIG_FILE="affiliate-config.json"
fi

# 一時ディレクトリを作成
TEMP_DIR=$(mktemp -d)
PLUGIN_NAME="beer-affiliate-engine"
BUILD_DIR="$TEMP_DIR/$PLUGIN_NAME"

echo -e "${YELLOW}一時ディレクトリ: $TEMP_DIR${NC}"

# ファイルをコピー
echo "ファイルをコピー中..."
cp -r . "$BUILD_DIR"

# 不要なファイルを削除
echo "不要なファイルを削除中..."
cd "$BUILD_DIR"
rm -rf .git .gitignore test-*.php *.zip build-plugin.sh
rm -rf config-templates/

# 設定ファイルが存在する場合、link-templates.jsonに統合
if [ -f "$OLDPWD/$CONFIG_FILE" ]; then
    echo -e "${GREEN}設定ファイルを検出: $CONFIG_FILE${NC}"
    
    # Pythonスクリプトで設定を統合
    python3 - <<EOF
import json
import sys

# 設定ファイルを読み込み
with open("$OLDPWD/$CONFIG_FILE", 'r', encoding='utf-8') as f:
    config = json.load(f)

# link-templates.jsonを読み込み
template_file = "modules/travel/link-templates.json"
with open(template_file, 'r', encoding='utf-8') as f:
    templates = json.load(f)

# 設定を適用
if 'affiliate_ids' in config:
    for service, settings in config['affiliate_ids'].items():
        if service in templates:
            # YOUR_で始まる値はスキップ
            if 'affiliate_id' in settings and not settings['affiliate_id'].startswith('YOUR_'):
                templates[service]['affiliate_id'] = settings['affiliate_id']
            if 'program_id' in settings and not settings['program_id'].startswith('YOUR_'):
                templates[service]['program_id'] = settings['program_id']
            print(f"✓ {service} の設定を適用しました")

# 更新されたテンプレートを保存
with open(template_file, 'w', encoding='utf-8') as f:
    json.dump(templates, f, ensure_ascii=False, indent=2)

print("設定の統合が完了しました")
EOF

else
    echo -e "${YELLOW}設定ファイルが見つかりません。デフォルト設定でビルドします。${NC}"
    echo -e "${YELLOW}設定ファイルを使用する場合: ./build-plugin.sh your-config.json${NC}"
fi

# ZIPファイルを作成
echo "ZIPファイルを作成中..."
cd "$TEMP_DIR"
zip -r "$PLUGIN_NAME.zip" "$PLUGIN_NAME" -x "*.DS_Store"

# 元のディレクトリに移動
mv "$PLUGIN_NAME.zip" "$OLDPWD/"

# 一時ディレクトリを削除
rm -rf "$TEMP_DIR"

echo -e "${GREEN}✓ ビルド完了: $PLUGIN_NAME.zip${NC}"
echo ""
echo "使い方:"
echo "1. WordPress管理画面 > プラグイン > 新規追加"
echo "2. 「プラグインのアップロード」をクリック"
echo "3. $PLUGIN_NAME.zip を選択してインストール"