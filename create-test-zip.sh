#!/bin/bash

# テスト用の最小プラグインZIPを作成
mkdir -p /tmp/beer-test-$$
cp beer-affiliate-engine-test-minimal.php /tmp/beer-test-$$/beer-affiliate-engine-test-minimal.php
cd /tmp/beer-test-$$
zip -r beer-affiliate-test.zip beer-affiliate-engine-test-minimal.php
mv beer-affiliate-test.zip "$OLDPWD/../"
cd "$OLDPWD"
rm -rf /tmp/beer-test-$$
echo "テストZIP作成完了: ../beer-affiliate-test.zip"