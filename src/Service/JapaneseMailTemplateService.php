<?php declare(strict_types=1);

namespace YukiTJapaneseLanguagePack\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

/**
 * Service for managing Japanese mail template translations
 * メールテンプレートの日本語翻訳を管理するサービス
 */
class JapaneseMailTemplateService
{
    private EntityRepository $mailTemplateRepository;
    private EntityRepository $languageRepository;
    private string $pluginPath;

    public function __construct(
        EntityRepository $mailTemplateRepository,
        EntityRepository $languageRepository,
    ) {
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->languageRepository = $languageRepository;
        $this->pluginPath = dirname(__DIR__) . '/Resources/fixtures/mails';
    }

    public function createJapaneseMailTemplateTranslations(Context $context): void
    {
        $jaLanguageId = $this->getLanguageId($context, 'ja-JP');
        
        if (!$jaLanguageId) {
            error_log('Japanese language (ja-JP) not found');
            return;
        }

        $mailTemplateTranslations = $this->getMailTemplateTranslations();
        
        foreach ($mailTemplateTranslations as $technicalName => $translation) {
            $this->createMailTemplateTranslation($context, $technicalName, $translation, $jaLanguageId);
        }
    }

    private function getLanguageId(Context $context, string $locale): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('locale.code', $locale));
        $criteria->addAssociation('locale');
        
        $language = $this->languageRepository->search($criteria, $context)->first();
        
        return $language ? $language->getId() : null;
    }

    private function createMailTemplateTranslation(Context $context, string $technicalName, array $translation, string $languageId): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('mailTemplateType.technicalName', $technicalName));
        $criteria->addAssociation('mailTemplateType');
        
        $mailTemplate = $this->mailTemplateRepository->search($criteria, $context)->first();
        
        if (!$mailTemplate) {
            error_log("Mail template not found for technical name: {$technicalName}");
            return;
        }

        $existingTranslations = $mailTemplate->getTranslations();
        if ($existingTranslations && isset($existingTranslations[$languageId])) {
            error_log("Translation already exists for template: {$technicalName}");
            return;
        }

        if (empty($translation['contentHtml']) && empty($translation['contentPlain'])) {
            error_log("Both HTML and plain content are empty for template: {$technicalName}");
            return;
        }

        $updateData = [
            'id' => $mailTemplate->getId(),
            'translations' => [
                $languageId => $translation
            ]
        ];

        try {
            $this->mailTemplateRepository->update([$updateData], $context);
        } catch (\Exception $e) {
            error_log("Failed to create/update mail template translation for {$technicalName}: " . $e->getMessage());
        }
    }

    private function loadTemplate(string $templateName, string $type): string
    {
        $fileName = "ja-{$type}.html.twig";
        $filePath = $this->pluginPath . "/{$templateName}/{$fileName}";
        
        if (!file_exists($filePath)) {
            error_log("Template file not found: {$filePath}");
            return '';
        }
        
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            error_log("Failed to read template file: {$filePath}");
            return '';
        }
        
        if (empty(trim($content))) {
            error_log("Template content is empty: {$filePath}");
            return '';
        }
        
        return $content;
    }

    private function getMailTemplateTranslations(): array
    {
        return [
            'newsletterDoubleOptIn' => [
                'subject' => 'ニュースレター登録の確認をお願いします',
                'description' => 'ニュースレターダブルオプトインのメールテンプレート',
                'contentHtml' => $this->loadTemplate('newsletterDoubleOptIn', 'html'),
                'contentPlain' => $this->loadTemplate('newsletterDoubleOptIn', 'plain'),
                'senderName' => '{{ salesChannel.translated.name }}'
            ],
            
            'newsletterRegister' => [
                'subject' => 'ニュースレターにご登録いただきありがとうございます',
                'description' => 'ニュースレター登録確認のメールテンプレート',
                'contentHtml' => $this->loadTemplate('newsletterRegister', 'html'),
                'contentPlain' => $this->loadTemplate('newsletterRegister', 'plain'),
                'senderName' => '{{ salesChannel.translated.name }}'
            ],
            
            'order_confirmation_mail' => [
                'subject' => 'ご注文確認 - ご注文番号 {{ order.orderNumber }}',
                'description' => '注文確認のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_confirmation_mail', 'html'),
                'contentPlain' => $this->loadTemplate('order_confirmation_mail', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'customer_register' => [
                'subject' => 'ご登録ありがとうございます - {{ salesChannel.name }}',
                'description' => '顧客登録確認のメールテンプレート',
                'contentHtml' => $this->loadTemplate('customer_register', 'html'),
                'contentPlain' => $this->loadTemplate('customer_register', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'customer_register.double_opt_in' => [
                'subject' => 'ご登録の確認をお願いします - {{ salesChannel.translated.name }}',
                'description' => 'ダブルオプトイン登録のメールテンプレート',
                'contentHtml' => $this->loadTemplate('customer_register.double_opt_in', 'html'),
                'contentPlain' => $this->loadTemplate('customer_register.double_opt_in', 'plain'),
                'senderName' => '{{ salesChannel.translated.name }}'
            ],
            
            'customer.recovery.request' => [
                'subject' => 'パスワードリセットのご依頼 - {{ salesChannel.name }}',
                'description' => '顧客パスワード回復のメールテンプレート',
                'contentHtml' => $this->loadTemplate('customer.recovery.request', 'html'),
                'contentPlain' => $this->loadTemplate('customer.recovery.request', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'customer.group.registration.accepted' => [
                'subject' => '顧客グループ登録が承認されました - {{ salesChannel.name }}',
                'description' => '顧客グループ登録承認のメールテンプレート',
                'contentHtml' => $this->loadTemplate('customer.group.registration.accepted', 'html'),
                'contentPlain' => $this->loadTemplate('customer.group.registration.accepted', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'customer.group.registration.declined' => [
                'subject' => '顧客グループ登録が却下されました - {{ salesChannel.name }}',
                'description' => '顧客グループ登録却下のメールテンプレート',
                'contentHtml' => $this->loadTemplate('customer.group.registration.declined', 'html'),
                'contentPlain' => $this->loadTemplate('customer.group.registration.declined', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'customer_group_change_accept' => [
                'subject' => 'お客様アカウントが承認されました - {{ salesChannel.name }}',
                'description' => '顧客グループ変更承認のメールテンプレート',
                'contentHtml' => $this->loadTemplate('customer_group_change_accept', 'html'),
                'contentPlain' => $this->loadTemplate('customer_group_change_accept', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'customer_group_change_reject' => [
                'subject' => 'お客様アカウント申請が却下されました - {{ salesChannel.name }}',
                'description' => '顧客グループ変更却下のメールテンプレート',
                'contentHtml' => $this->loadTemplate('customer_group_change_reject', 'html'),
                'contentPlain' => $this->loadTemplate('customer_group_change_reject', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'user.recovery.request' => [
                'subject' => 'ユーザーパスワードリセットのご依頼',
                'description' => 'ユーザーパスワード回復のメールテンプレート',
                'contentHtml' => $this->loadTemplate('user.recovery.request', 'html'),
                'contentPlain' => $this->loadTemplate('user.recovery.request', 'plain'),
                'senderName' => 'Shopware Administration'
            ],
            
            'contact_form' => [
                'subject' => 'お問い合わせを受信いたしました - {{ salesChannel.name }}',
                'description' => 'お問い合わせフォームのメールテンプレート',
                'contentHtml' => $this->loadTemplate('contact_form', 'html'),
                'contentPlain' => $this->loadTemplate('contact_form', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'password_change' => [
                'subject' => 'パスワード変更のご依頼 - {{ salesChannel.name }}',
                'description' => 'パスワード変更依頼のメールテンプレート',
                'contentHtml' => $this->loadTemplate('password_change', 'html'),
                'contentPlain' => $this->loadTemplate('password_change', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order.state.cancelled' => [
                'subject' => 'ご注文がキャンセルされました - ご注文番号 {{ order.orderNumber }}',
                'description' => '注文キャンセル状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order.state.cancelled', 'html'),
                'contentPlain' => $this->loadTemplate('order.state.cancelled', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order.state.completed' => [
                'subject' => 'ご注文が完了いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '注文完了状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order.state.completed', 'html'),
                'contentPlain' => $this->loadTemplate('order.state.completed', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order.state.open' => [
                'subject' => 'ご注文を受付いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '注文オープン状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order.state.open', 'html'),
                'contentPlain' => $this->loadTemplate('order.state.open', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],

            'order.state.in_progress' => [
                'subject' => 'ご注文を処理中です - ご注文番号 {{ order.orderNumber }}',
                'description' => '注文処理中状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order.state.in_progress', 'html'),
                'contentPlain' => $this->loadTemplate('order.state.in_progress', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_delivery.state.shipped' => [
                'subject' => '商品を発送いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '配送完了状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_delivery.state.shipped', 'html'),
                'contentPlain' => $this->loadTemplate('order_delivery.state.shipped', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_delivery.state.shipped_partially' => [
                'subject' => '商品を一部発送いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '部分配送状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_delivery.state.shipped_partially', 'html'),
                'contentPlain' => $this->loadTemplate('order_delivery.state.shipped_partially', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_delivery.state.cancelled' => [
                'subject' => '配送がキャンセルされました - ご注文番号 {{ order.orderNumber }}',
                'description' => '配送キャンセル状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_delivery.state.cancelled', 'html'),
                'contentPlain' => $this->loadTemplate('order_delivery.state.cancelled', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_delivery.state.returned' => [
                'subject' => '商品が返送されました - ご注文番号 {{ order.orderNumber }}',
                'description' => '配送返送状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_delivery.state.returned', 'html'),
                'contentPlain' => $this->loadTemplate('order_delivery.state.returned', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_delivery.state.returned_partially' => [
                'subject' => '商品が一部返送されました - ご注文番号 {{ order.orderNumber }}',
                'description' => '部分返送状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_delivery.state.returned_partially', 'html'),
                'contentPlain' => $this->loadTemplate('order_delivery.state.returned_partially', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.paid' => [
                'subject' => 'お支払いを確認いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払い完了状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_transaction.state.paid', 'html'),
                'contentPlain' => $this->loadTemplate('order_transaction.state.paid', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.open' => [
                'subject' => 'お支払いをお待ちしております - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払い未完了状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_transaction.state.open', 'html'),
                'contentPlain' => $this->loadTemplate('order_transaction.state.open', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.authorized' => [
                'subject' => '支払いが承認されました - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払い承認状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_transaction.state.authorized', 'html'),
                'contentPlain' => $this->loadTemplate('order_transaction.state.authorized', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.paid_partially' => [
                'subject' => '一部入金を確認いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '部分支払い状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_transaction.state.paid_partially', 'html'),
                'contentPlain' => $this->loadTemplate('order_transaction.state.paid_partially', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.refunded' => [
                'subject' => '返金処理が完了いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '返金完了状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_transaction.state.refunded', 'html'),
                'contentPlain' => $this->loadTemplate('order_transaction.state.refunded', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.refunded_partially' => [
                'subject' => '一部返金処理が完了いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '部分返金状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_transaction.state.refunded_partially', 'html'),
                'contentPlain' => $this->loadTemplate('order_transaction.state.refunded_partially', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.reminded' => [
                'subject' => 'お支払いのリマインダー - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払いリマインダー状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_transaction.state.reminded', 'html'),
                'contentPlain' => $this->loadTemplate('order_transaction.state.reminded', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.cancelled' => [
                'subject' => 'お支払いがキャンセルされました - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払いキャンセル状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_transaction.state.cancelled', 'html'),
                'contentPlain' => $this->loadTemplate('order_transaction.state.cancelled', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.chargeback' => [
                'subject' => 'チャージバックが発生しました - ご注文番号 {{ order.orderNumber }}',
                'description' => 'チャージバック状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_transaction.state.chargeback', 'html'),
                'contentPlain' => $this->loadTemplate('order_transaction.state.chargeback', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.unconfirmed' => [
                'subject' => 'お支払いの確認待ち - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払い未確認状態のメールテンプレート',
                'contentHtml' => $this->loadTemplate('order_transaction.state.unconfirmed', 'html'),
                'contentPlain' => $this->loadTemplate('order_transaction.state.unconfirmed', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'invoice_mail' => [
                'subject' => '請求書 - ご注文番号 {{ order.orderNumber }}',
                'description' => '請求書のメールテンプレート',
                'contentHtml' => $this->loadTemplate('invoice_mail', 'html'),
                'contentPlain' => $this->loadTemplate('invoice_mail', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'delivery_mail' => [
                'subject' => '納品書 - ご注文番号 {{ order.orderNumber }}',
                'description' => '納品書のメールテンプレート',
                'contentHtml' => $this->loadTemplate('delivery_mail', 'html'),
                'contentPlain' => $this->loadTemplate('delivery_mail', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'credit_note_mail' => [
                'subject' => '返金通知書 - ご注文番号 {{ order.orderNumber }}',
                'description' => '返金通知書のメールテンプレート',
                'contentHtml' => $this->loadTemplate('credit_note_mail', 'html'),
                'contentPlain' => $this->loadTemplate('credit_note_mail', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'cancellation_mail' => [
                'subject' => 'キャンセル請求書 - ご注文番号 {{ order.orderNumber }}',
                'description' => 'キャンセル請求書のメールテンプレート',
                'contentHtml' => $this->loadTemplate('cancellation_mail', 'html'),
                'contentPlain' => $this->loadTemplate('cancellation_mail', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'guest_order.double_opt_in' => [
                'subject' => 'ご注文の確認をお願いします - {{ salesChannel.name }}',
                'description' => 'ゲスト注文ダブルオプトインのメールテンプレート',
                'contentHtml' => $this->loadTemplate('guest_order.double_opt_in', 'html'),
                'contentPlain' => $this->loadTemplate('guest_order.double_opt_in', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'downloads_delivery' => [
                'subject' => 'デジタル商品の配信完了 - ご注文番号 {{ order.orderNumber }}',
                'description' => 'デジタル商品配信のメールテンプレート',
                'contentHtml' => $this->loadTemplate('downloads_delivery', 'html'),
                'contentPlain' => $this->loadTemplate('downloads_delivery', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'review_form' => [
                'subject' => '商品レビューをお願いします',
                'description' => '商品レビューのメールテンプレート',
                'contentHtml' => $this->loadTemplate('review_form', 'html'),
                'contentPlain' => $this->loadTemplate('review_form', 'plain'),
                'senderName' => '{{ salesChannel.name }}'
            ],
        ];
    }
}