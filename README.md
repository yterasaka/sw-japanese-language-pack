# Japanese Language Pack for Shopware 6

日本語言語パック for Shopware 6

## 概要 / Overview

このプラグインは、Shopware 6 に包括的な日本語サポートを追加します。ストアフロントの日本語翻訳、管理画面の日本語翻訳、日本円（JPY）通貨サポート、日本の 47 都道府県対応、商品ソートオプションの日本語化、メールテンプレートの日本語化、ステート管理の日本語化、および日本の国旗アイコンが含まれています。

This plugin adds comprehensive Japanese language support to Shopware 6, including complete Japanese translations for both storefront and administration panel, Japanese Yen (JPY) currency support, all 47 Japanese prefectures, localized product sorting options, Japanese email templates, Japanese state management, and Japanese flag icon.

## 機能 / Features

### 実装済み / Implemented

- **日本語ロケール (ja-JP)** - Japanese locale support
- **日本の国旗アイコン** - Japanese flag icon in language selector
- **日本円 (JPY) 通貨サポート** - Japanese Yen currency with proper formatting
- **日本国の自動作成** - Automatic creation of Japan country if not exists
- **最適化された国設定** - Optimized country configuration for Japan
- **都道府県サポート** - Japanese prefectures (47 prefectures) with bilingual names
- **商品ソートオプション** - Product sorting options localization
- **ストアフロント** - Complete storefront translation including
- **管理画面翻訳** - Comprehensive administration panel translations including
- **メールテンプレート日本語化** - Comprehensive Japanese email templates including
- **ステート管理の日本語化** - Japanese translations for state management
- **ドキュメント** - document translations with Japanese formatting

## インストール / Installation

### 手動インストール / Manual Installation

1. このリポジトリをクローンまたはダウンロード / Clone or download this repository
2. プラグインファイルを以下のディレクトリに配置 / Place plugin files in: `custom/plugins/JapaneseLanguagePack/`

### プラグインの有効化 / Plugin Activation

```bash
# プラグインをリフレッシュ / Refresh plugins
bin/console plugin:refresh

# プラグインをインストール / Install plugin
bin/console plugin:install --activate JapaneseLanguagePack

# キャッシュをクリア / Clear cache
bin/console cache:clear
```

## アンインストール / Uninstallation

```bash
# プラグインのアンインストール / Uninstall plugin
bin/console plugin:uninstall JapaneseLanguagePack

# キャッシュをクリア / Clear cache
bin/console cache:clear
```

**データ保持について / Data Retention:**

データ整合性のため、このプラグインをアンインストールしても以下のデータは**削除されません**：
For data integrity reasons, the following data will **NOT be deleted** when uninstalling this plugin:

- 日本語言語設定 / Japanese language configuration
- 日本円（JPY）通貨設定 / Japanese Yen (JPY) currency configuration
- 日本国・都道府県データ / Japan country and prefecture data
- 商品ソートオプションの日本語翻訳 / Japanese translations for product sorting options
- メールテンプレートの日本語翻訳 / Japanese translations for email templates
- ステート管理の日本語翻訳 / Japanese translations for state management
- 管理画面の日本語翻訳 / Japanese translations for administration panel
- 既存の注文・顧客データとの関連性 / Relationships with existing orders and customer data

これらのデータの削除が必要な場合は、管理画面から手動で削除してください。
If you need to remove this data, please delete it manually from the administration panel.

## 翻訳について / About Translations

このプラグインの翻訳は、生成 AI を活用して作成されています。
The translations in this plugin are created using generative AI.

翻訳の改善案やフィードバックをお待ちしています。
I welcome suggestions for translation improvements and feedback.

## 貢献 / Contributing

このプロジェクトへの貢献を歓迎します！/ Contributions are welcome!

### 翻訳の改善 / Translation Improvements

翻訳の改善案がある場合は、以下の手順でご協力ください：
If you have suggestions for translation improvements:

1. [issues](../../issues)で翻訳の改善案を報告 / Report translation improvements in [issues](../../issues)
2. フォークしてプルリクエストを送信 / Fork and submit a pull request

## 変更履歴 / Changelog

### v1.3.1

- エラーハンドリングの改善 / Improved error handling
- コードドキュメントの改善 / Improved code documentation

### v1.3.0

- 管理画面の包括的な日本語翻訳を追加 / Added comprehensive Japanese translations for administration panel

### v1.2.0

- メールテンプレート日本語化 / Japanese email templates added
- ステート管理日本語化 / Japanese state management added

### v1.1.0

- 日本国の自動作成機能 / Auto-creation of Japan country
- 最適化された国設定 / Optimized country configuration
- 47 都道府県の追加（英語・日本語対応）/ Added 47 Japanese prefectures (bilingual)
- ドキュメント翻訳の追加 / Added document translations (invoices, delivery notes, etc.)
- 商品ソートオプションの日本語化 / Japanese localization for product sorting options
- 各サービスを分離して保守性を向上 / Improved maintainability by separating services

### v1.0.0

- 初回リリース / Initial release
- 日本語ロケール (ja-JP) サポート / Japanese locale support
- 日本円 (JPY) 通貨サポート / Japanese Yen currency support
- ストアフロント / storefront translation
- 日本国旗アイコン / Japanese flag icon

---

**注意**: 本番環境での使用前に十分にテストしてください。
**Note**: Please test thoroughly before using in production.
