# Japanese Language Pack for Shopware 6

日本語言語パック for Shopware 6

## 概要 / Overview

このプラグインは、Shopware 6 に包括的な日本語サポートを追加します。ストアフロントの日本語翻訳、日本円（JPY）通貨サポート、日本の 47 都道府県対応、商品ソートオプションの日本語化、および日本の国旗アイコンが含まれています。

This plugin adds comprehensive Japanese language support to Shopware 6, including complete Japanese translations for the storefront, Japanese Yen (JPY) currency support, all 47 Japanese prefectures, localized product sorting options, and Japanese flag icon.

## 機能 / Features

### 実装済み / Implemented

- **日本語ロケール (ja-JP)** - Japanese locale support
- **日本の国旗アイコン** - Japanese flag icon in language selector
- **日本円 (JPY) 通貨サポート** - Japanese Yen currency with proper formatting
- **日本国の自動作成** - Automatic creation of Japan country if not exists
- **最適化された国設定** - Optimized country configuration for Japan
- **都道府県サポート** - Japanese prefectures (47 prefectures) with bilingual names
- **商品ソートオプション** - Product sorting options localization:
- **ストアフロント** - Complete storefront translation including:
- **ドキュメント** - document translations with Japanese formatting

### 今後の予定 / Planned

- **管理画面翻訳** - Administration panel translations
- **メールテンプレート** - Email template translations

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
- 既存の注文・顧客データとの関連性 / Relationships with existing orders and customer data

これらのデータの削除が必要な場合は、管理画面から手動で削除してください。
If you need to remove this data, please delete it manually from the administration panel.

### 自動設定内容 / Auto-Configuration

プラグインインストール時に以下が自動的に設定されます：
The following will be automatically configured during plugin installation:

- **日本国の作成** - Japan country creation (if not exists)
- **国コード**: JP
- **ISO3 コード**: JPN
- **通貨**: 日本円 (JPY)
- **都道府県**: 47 都道府県の英語・日本語名称
  - 北海道 (Hokkaido)
  - 青森県 (Aomori)
  - 岩手県 (Iwate)
  - ...など全 47 都道府県
- **商品ソートオプション**: 日本語翻訳の自動追加

## 貢献 / Contributing

このプロジェクトへの貢献を歓迎します！/ Contributions are welcome!

### 翻訳の改善 / Translation Improvements

翻訳の改善案がある場合は、以下の手順でご協力ください：
If you have suggestions for translation improvements:

1. [issues](../../issues)で翻訳の改善案を報告 / Report translation improvements in [issues](../../issues)
2. フォークしてプルリクエストを送信 / Fork and submit a pull request

## 変更履歴 / Changelog

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
