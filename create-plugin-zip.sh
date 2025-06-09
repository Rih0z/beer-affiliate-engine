#!/bin/bash

# プラグインディレクトリ名
PLUGIN_NAME="beer-affiliate-engine"
VERSION="1.4.3"

# 現在のディレクトリを保存
CURRENT_DIR=$(pwd)

# 一時ディレクトリを作成
TEMP_DIR="/tmp/${PLUGIN_NAME}-build-$$"
mkdir -p "$TEMP_DIR/$PLUGIN_NAME"

# ファイルをコピー（除外ファイルを考慮）
echo "ファイルをコピー中..."
rsync -av --exclude='.git' \
          --exclude='node_modules' \
          --exclude='.DS_Store' \
          --exclude='test-*' \
          --exclude='debug-*' \
          --exclude='*-minimal.php' \
          --exclude='*-v143.php' \
          --exclude='*-backup.php' \
          --exclude='*-safe.php' \
          --exclude='*.zip' \
          --exclude='beer-affiliate-engine-*' \
          --exclude='create-plugin-zip.sh' \
          ./ "$TEMP_DIR/$PLUGIN_NAME/"

# ZIPファイルを作成
echo "ZIPファイルを作成中..."
cd "$TEMP_DIR"
zip -r "$CURRENT_DIR/../${PLUGIN_NAME}-v${VERSION}.zip" "$PLUGIN_NAME"

# 一時ディレクトリを削除
rm -rf "$TEMP_DIR"

echo "完了: $CURRENT_DIR/../${PLUGIN_NAME}-v${VERSION}.zip"

# ZIPファイルの構造を確認
echo ""
echo "ZIPファイルの構造:"
unzip -l "$CURRENT_DIR/../${PLUGIN_NAME}-v${VERSION}.zip" | head -20