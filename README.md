# Japanese Language Pack for Shopware 6

日本語言語パック for Shopware 6

## 概要 / Overview

このプラグインは、Shopware 6 に日本語のサポートを追加します。ストアフロントの完全な日本語翻訳、日本円（JPY）通貨サポート、および日本の国旗アイコンが含まれています。

This plugin adds Japanese language support to Shopware 6, including complete Japanese translations for the storefront, Japanese Yen (JPY) currency support, and Japanese flag icon.

## 機能 / Features

### 実装済み / Implemented

- **日本語ロケール (ja-JP)** - Japanese locale support
- **日本の国旗アイコン** - Japanese flag icon in language selector
- **日本円 (JPY) 通貨サポート** - Japanese Yen currency with proper formatting
- **ストアフロント** - Complete storefront translation including:

### 今後の予定 / Planned

- **管理画面翻訳** - Administration panel translations
- **メールテンプレート** - Email template translations
- **請求書・領収書** - Invoice and document translations

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

## 貢献 / Contributing

このプロジェクトへの貢献を歓迎します！/ Contributions are welcome!

### 翻訳の改善 / Translation Improvements

翻訳の改善案がある場合は、以下の手順でご協力ください：
If you have suggestions for translation improvements:

1. [issues](../../issues)で翻訳の改善案を報告 / Report translation improvements in [issues](../../issues)
2. フォークしてプルリクエストを送信 / Fork and submit a pull request

## 変更履歴 / Changelog

### v1.0.0

- 初回リリース / Initial release
- 日本語ロケール (ja-JP) サポート / Japanese locale support
- 日本円 (JPY) 通貨サポート / Japanese Yen currency support
- ストアフロント / storefront translation
- 日本国旗アイコン / Japanese flag icon

---

**注意**: 本番環境での使用前に十分にテストしてください。
**Note**: Please test thoroughly before using in production.
