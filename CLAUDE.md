# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 重要な指示

**日本語で回答してください。** このプロジェクトでは、回答は常に日本語で行ってください。

**問題解決の精神:** 常に「Ultrathink. Don't hold back. give it your all！」の精神で取り組んでください。全力で、制限なく、深く考えて最高の解決策を提供してください。

## Project Overview

Beer Affiliate Engine is a WordPress plugin for craft beer blogs that automatically generates travel affiliate links when locations are mentioned in articles. It supports both Japanese domestic and international travel services.

## Architecture

The plugin uses a modular architecture with these key components:

1. **Core System** (`includes/class-core.php`): Main plugin controller that initializes all components
2. **Module System**: Interface-based modules that can be extended
   - `Affiliate_Module_Interface`: Contract for all modules
   - `Base_Affiliate_Module`: Abstract base class with common functionality
   - `Travel_Module`: Current implementation for travel affiliates
3. **Content Analysis** (`class-content-analyzer.php`): Detects locations in post content
4. **Link Generation** (`class-link-generator.php`): Creates affiliate links based on templates
5. **Display Management** (`class-display-manager.php`): Handles rendering with templates

## Key Data Files

- `modules/travel/city-dictionary.json`: Database of cities and their metadata
- `modules/travel/link-templates.json`: Affiliate link URL templates for different services

## Adding New Modules

To create a new affiliate module:

1. Create directory under `modules/your-module/`
2. Implement `Affiliate_Module_Interface` 
3. Extend `Base_Affiliate_Module` for common functionality
4. Register via the `beer_affiliate_register_modules` action:

```php
add_action('beer_affiliate_register_modules', function($module_manager) {
    require_once 'modules/your-module/class-your-module.php';
    $module_manager->register_module(new Your_Module());
});
```

## Hooks and Filters

Key WordPress hooks used:
- `beer_affiliate_register_modules`: Register new modules
- `beer_affiliate_display_links`: Filter generated links before display
- `beer_affiliate_link_templates`: Modify link templates
- `beer_affiliate_city_dictionary`: Extend city database

## Development Notes

- No build process required - traditional WordPress plugin
- Settings managed through WordPress Customizer (Appearance > Customize > Beer Affiliate Settings)
- Templates in `templates/` directory: card.php, button.php, sticky.php
- CSS in `assets/css/main.css`, JavaScript in `assets/js/sticky.js`

## Testing

Currently no automated tests. Test manually by:
1. Creating posts with location names (e.g., "東京", "Seattle", "ミュンヘン")
2. Checking if affiliate links appear correctly
3. Verifying different display templates work
4. Testing category filtering options

## Building for Production

プロダクション用のZIPファイルを作成する際、アフィリエイトIDを自動的に設定できます：

### 設定ファイルを使用したビルド
```bash
# 1. affiliate-config.json.exampleをコピーして編集
cp affiliate-config.json.example affiliate-config.json
# エディタでaffiliate-config.jsonを開き、実際のIDを入力

# 2. ビルドスクリプトを実行
./build-plugin.sh affiliate-config.json
```

### デフォルトビルド（設定なし）
```bash
./build-plugin.sh
```

**重要**: 
- `affiliate-config.json`は.gitignoreに含まれているため、GitHubにはアップロードされません
- ビルドスクリプトは設定ファイルの内容をlink-templates.jsonに統合します
- 生成されたZIPファイルには設定済みのアフィリエイトIDが含まれます