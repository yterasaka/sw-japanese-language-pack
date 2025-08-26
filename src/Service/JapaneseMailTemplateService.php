<?php declare(strict_types=1);

namespace JapaneseLanguagePack\Service;

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

    public function __construct(
        EntityRepository $mailTemplateRepository,
        EntityRepository $languageRepository
    ) {
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->languageRepository = $languageRepository;
    }

    public function createJapaneseMailTemplateTranslations(Context $context): void
    {
        $jaLanguageId = $this->getLanguageId($context, 'ja-JP');
        
        if (!$jaLanguageId) {
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
            return;
        }

        // Check if Japanese translation already exists
        $existingTranslations = $mailTemplate->getTranslations();
        if ($existingTranslations && isset($existingTranslations[$languageId])) {
            return;
        }

        $updateData = [
            'id' => $mailTemplate->getId(),
            'translations' => [
                $languageId => $translation
            ]
        ];

        $this->mailTemplateRepository->update([$updateData], $context);
    }

    private function getMailTemplateTranslations(): array
    {
        return [
            // Newsletter templates - using correct technical names from MailTemplateTypes
            'newsletterDoubleOptIn' => [
                'subject' => 'ニュースレター登録の確認をお願いします',
                'description' => 'ニュースレターダブルオプトインのメールテンプレート',
                'contentHtml' => $this->getNewsletterDoubleOptInHtmlTemplate(),
                'contentPlain' => $this->getNewsletterDoubleOptInPlainTemplate(),
                'senderName' => '{{ salesChannel.translated.name }}'
            ],
            
            'newsletterRegister' => [
                'subject' => 'ニュースレターにご登録いただきありがとうございます',
                'description' => 'ニュースレター登録確認のメールテンプレート',
                'contentHtml' => $this->getNewsletterRegistrationHtmlTemplate(),
                'contentPlain' => $this->getNewsletterRegistrationPlainTemplate(),
                'senderName' => '{{ salesChannel.translated.name }}'
            ],
            
            // Order confirmation
            'order_confirmation_mail' => [
                'subject' => 'ご注文確認 - ご注文番号 {{ order.orderNumber }}',
                'description' => '注文確認のメールテンプレート',
                'contentHtml' => $this->getOrderConfirmationHtmlTemplate(),
                'contentPlain' => $this->getOrderConfirmationPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Customer registration
            'customer_register' => [
                'subject' => 'ご登録ありがとうございます - {{ salesChannel.name }}',
                'description' => '顧客登録確認のメールテンプレート',
                'contentHtml' => $this->getCustomerRegistrationHtmlTemplate(),
                'contentPlain' => $this->getCustomerRegistrationPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Customer double opt-in registration
            'customer_register.double_opt_in' => [
                'subject' => 'ご登録の確認をお願いします - {{ salesChannel.translated.name }}',
                'description' => 'ダブルオプトイン登録のメールテンプレート',
                'contentHtml' => $this->getDoubleOptInRegistrationHtmlTemplate(),
                'contentPlain' => $this->getDoubleOptInRegistrationPlainTemplate(),
                'senderName' => '{{ salesChannel.translated.name }}'
            ],
            
            // Customer password recovery
            'customer.recovery.request' => [
                'subject' => 'パスワードリセットのご依頼 - {{ salesChannel.name }}',
                'description' => '顧客パスワード回復のメールテンプレート',
                'contentHtml' => $this->getCustomerPasswordRecoveryHtmlTemplate(),
                'contentPlain' => $this->getCustomerPasswordRecoveryPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Customer group templates
            'customer.group.registration.accepted' => [
                'subject' => '顧客グループ登録が承認されました - {{ salesChannel.name }}',
                'description' => '顧客グループ登録承認のメールテンプレート',
                'contentHtml' => $this->getCustomerGroupAcceptedHtmlTemplate(),
                'contentPlain' => $this->getCustomerGroupAcceptedPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'customer.group.registration.declined' => [
                'subject' => '顧客グループ登録が却下されました - {{ salesChannel.name }}',
                'description' => '顧客グループ登録却下のメールテンプレート',
                'contentHtml' => $this->getCustomerGroupRejectedHtmlTemplate(),
                'contentPlain' => $this->getCustomerGroupRejectedPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'customer_group_change_accept' => [
                'subject' => 'お客様アカウントが承認されました - {{ salesChannel.name }}',
                'description' => '顧客グループ変更承認のメールテンプレート',
                'contentHtml' => $this->getCustomerGroupChangeAcceptedHtmlTemplate(),
                'contentPlain' => $this->getCustomerGroupChangeAcceptedPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'customer_group_change_reject' => [
                'subject' => 'お客様アカウント申請が却下されました - {{ salesChannel.name }}',
                'description' => '顧客グループ変更却下のメールテンプレート',
                'contentHtml' => $this->getCustomerGroupChangeRejectedHtmlTemplate(),
                'contentPlain' => $this->getCustomerGroupChangeRejectedPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // User password recovery
            'user.recovery.request' => [
                'subject' => 'ユーザーパスワードリセットのご依頼',
                'description' => 'ユーザーパスワード回復のメールテンプレート',
                'contentHtml' => $this->getUserPasswordRecoveryHtmlTemplate(),
                'contentPlain' => $this->getUserPasswordRecoveryPlainTemplate(),
                'senderName' => 'Shopware Administration'
            ],
            
            // Contact form
            'contact_form' => [
                'subject' => 'お問い合わせを受信いたしました - {{ salesChannel.name }}',
                'description' => 'お問い合わせフォームのメールテンプレート',
                'contentHtml' => $this->getContactFormHtmlTemplate(),
                'contentPlain' => $this->getContactFormPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Customer password change
            'password_change' => [
                'subject' => 'パスワード変更のご依頼 - {{ salesChannel.name }}',
                'description' => 'パスワード変更依頼のメールテンプレート',
                'contentHtml' => $this->getPasswordChangeRequestHtmlTemplate(),
                'contentPlain' => $this->getPasswordChangeRequestPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Order state change templates
            'order.state.cancelled' => [
                'subject' => 'ご注文がキャンセルされました - ご注文番号 {{ order.orderNumber }}',
                'description' => '注文キャンセル状態のメールテンプレート',
                'contentHtml' => $this->getOrderStateCancelledHtmlTemplate(),
                'contentPlain' => $this->getOrderStateCancelledPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order.state.completed' => [
                'subject' => 'ご注文が完了いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '注文完了状態のメールテンプレート',
                'contentHtml' => $this->getOrderStateDoneHtmlTemplate(),
                'contentPlain' => $this->getOrderStateDonePlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order.state.open' => [
                'subject' => 'ご注文を受付いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '注文オープン状態のメールテンプレート',
                'contentHtml' => $this->getOrderStateOpenHtmlTemplate(),
                'contentPlain' => $this->getOrderStateOpenPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],

            'order.state.in_progress' => [
                'subject' => 'ご注文を処理中です - ご注文番号 {{ order.orderNumber }}',
                'description' => '注文処理中状態のメールテンプレート',
                'contentHtml' => $this->getOrderStateInProgressHtmlTemplate(),
                'contentPlain' => $this->getOrderStateInProgressPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Delivery state change templates
            'order_delivery.state.shipped' => [
                'subject' => '商品を発送いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '配送完了状態のメールテンプレート',
                'contentHtml' => $this->getDeliveryStateShippedHtmlTemplate(),
                'contentPlain' => $this->getDeliveryStateShippedPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_delivery.state.shipped_partially' => [
                'subject' => '商品を一部発送いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '部分配送状態のメールテンプレート',
                'contentHtml' => $this->getDeliveryStateShippedPartiallyHtmlTemplate(),
                'contentPlain' => $this->getDeliveryStateShippedPartiallyPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_delivery.state.cancelled' => [
                'subject' => '配送がキャンセルされました - ご注文番号 {{ order.orderNumber }}',
                'description' => '配送キャンセル状態のメールテンプレート',
                'contentHtml' => $this->getDeliveryStateCancelledHtmlTemplate(),
                'contentPlain' => $this->getDeliveryStateCancelledPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_delivery.state.returned' => [
                'subject' => '商品が返送されました - ご注文番号 {{ order.orderNumber }}',
                'description' => '配送返送状態のメールテンプレート',
                'contentHtml' => $this->getDeliveryStateReturnedHtmlTemplate(),
                'contentPlain' => $this->getDeliveryStateReturnedPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_delivery.state.returned_partially' => [
                'subject' => '商品が一部返送されました - ご注文番号 {{ order.orderNumber }}',
                'description' => '部分返送状態のメールテンプレート',
                'contentHtml' => $this->getDeliveryStateReturnedPartiallyHtmlTemplate(),
                'contentPlain' => $this->getDeliveryStateReturnedPartiallyPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Payment state change templates
            'order_transaction.state.paid' => [
                'subject' => 'お支払いを確認いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払い完了状態のメールテンプレート',
                'contentHtml' => $this->getPaymentStatePaidHtmlTemplate(),
                'contentPlain' => $this->getPaymentStatePaidPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.open' => [
                'subject' => 'お支払いをお待ちしております - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払い未完了状態のメールテンプレート',
                'contentHtml' => $this->getPaymentStateOpenHtmlTemplate(),
                'contentPlain' => $this->getPaymentStateOpenPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.authorized' => [
                'subject' => '支払いが承認されました - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払い承認状態のメールテンプレート',
                'contentHtml' => $this->getPaymentStateAuthorizedHtmlTemplate(),
                'contentPlain' => $this->getPaymentStateAuthorizedPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.paid_partially' => [
                'subject' => '一部入金を確認いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '部分支払い状態のメールテンプレート',
                'contentHtml' => $this->getPaymentStatePaidPartiallyHtmlTemplate(),
                'contentPlain' => $this->getPaymentStatePaidPartiallyPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.refunded' => [
                'subject' => '返金処理が完了いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '返金完了状態のメールテンプレート',
                'contentHtml' => $this->getPaymentStateRefundedHtmlTemplate(),
                'contentPlain' => $this->getPaymentStateRefundedPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.refunded_partially' => [
                'subject' => '一部返金処理が完了いたしました - ご注文番号 {{ order.orderNumber }}',
                'description' => '部分返金状態のメールテンプレート',
                'contentHtml' => $this->getPaymentStateRefundedPartiallyHtmlTemplate(),
                'contentPlain' => $this->getPaymentStateRefundedPartiallyPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.reminded' => [
                'subject' => 'お支払いのリマインダー - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払いリマインダー状態のメールテンプレート',
                'contentHtml' => $this->getPaymentStateRemindedHtmlTemplate(),
                'contentPlain' => $this->getPaymentStateRemindedPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.cancelled' => [
                'subject' => 'お支払いがキャンセルされました - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払いキャンセル状態のメールテンプレート',
                'contentHtml' => $this->getPaymentStateCancelledHtmlTemplate(),
                'contentPlain' => $this->getPaymentStateCancelledPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.chargeback' => [
                'subject' => 'チャージバックが発生しました - ご注文番号 {{ order.orderNumber }}',
                'description' => 'チャージバック状態のメールテンプレート',
                'contentHtml' => $this->getPaymentStateChargebackHtmlTemplate(),
                'contentPlain' => $this->getPaymentStateChargebackPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'order_transaction.state.unconfirmed' => [
                'subject' => 'お支払いの確認待ち - ご注文番号 {{ order.orderNumber }}',
                'description' => '支払い未確認状態のメールテンプレート',
                'contentHtml' => $this->getPaymentStateUnconfirmedHtmlTemplate(),
                'contentPlain' => $this->getPaymentStateUnconfirmedPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Document templates
            'invoice_mail' => [
                'subject' => '請求書 - ご注文番号 {{ order.orderNumber }}',
                'description' => '請求書のメールテンプレート',
                'contentHtml' => $this->getInvoiceHtmlTemplate(),
                'contentPlain' => $this->getInvoicePlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'delivery_mail' => [
                'subject' => '納品書 - ご注文番号 {{ order.orderNumber }}',
                'description' => '納品書のメールテンプレート',
                'contentHtml' => $this->getDeliveryNoteHtmlTemplate(),
                'contentPlain' => $this->getDeliveryNotePlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'credit_note_mail' => [
                'subject' => '返金通知書 - ご注文番号 {{ order.orderNumber }}',
                'description' => '返金通知書のメールテンプレート',
                'contentHtml' => $this->getCreditNoteHtmlTemplate(),
                'contentPlain' => $this->getCreditNotePlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            'cancellation_mail' => [
                'subject' => 'キャンセル請求書 - ご注文番号 {{ order.orderNumber }}',
                'description' => 'キャンセル請求書のメールテンプレート',
                'contentHtml' => $this->getCancellationInvoiceHtmlTemplate(),
                'contentPlain' => $this->getCancellationInvoicePlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Guest order double opt-in
            'guest_order.double_opt_in' => [
                'subject' => 'ご注文の確認をお願いします - {{ salesChannel.name }}',
                'description' => 'ゲスト注文ダブルオプトインのメールテンプレート',
                'contentHtml' => $this->getDoubleOptInGuestOrderHtmlTemplate(),
                'contentPlain' => $this->getDoubleOptInGuestOrderPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Digital product delivery
            'downloads_delivery' => [
                'subject' => 'デジタル商品の配信完了 - ご注文番号 {{ order.orderNumber }}',
                'description' => 'デジタル商品配信のメールテンプレート',
                'contentHtml' => $this->getDigitalProductDeliveryHtmlTemplate(),
                'contentPlain' => $this->getDigitalProductDeliveryPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
            
            // Product review
            'review_form' => [
                'subject' => '商品レビューをお願いします',
                'description' => '商品レビューのメールテンプレート',
                'contentHtml' => $this->getProductReviewHtmlTemplate(),
                'contentPlain' => $this->getProductReviewPlainTemplate(),
                'senderName' => '{{ salesChannel.name }}'
            ],
        ];
    }

    // Template methods - all methods that are referenced in the array above
    private function getNewsletterDoubleOptInHtmlTemplate(): string
    {
        return '
<h3>{{ newsletterRecipient.lastName }} {{ newsletterRecipient.firstName }} 様</h3>
<p>ニュースレターにご興味をお持ちいただき、誠にありがとうございます。</p>
<p>メールアドレスの不正使用を防ぐため、確認メールをお送りしております。ニュースレターの定期配信をご希望の場合は、<a href="{{ url }}">こちら</a>をクリックして確認してください。</p>
<p>ニュースレターにお申し込みされていない場合は、このメールを無視してください。</p>
        ';
    }

    private function getNewsletterDoubleOptInPlainTemplate(): string
    {
        return '
{{ newsletterRecipient.lastName }} {{ newsletterRecipient.firstName }} 様

ニュースレターにご興味をお持ちいただき、誠にありがとうございます。

メールアドレスの不正使用を防ぐため、確認メールをお送りしております。ニュースレターの定期配信をご希望の場合は、下記のリンクをクリックして確認してください：{{ url }}

ニュースレターにお申し込みされていない場合は、このメールを無視してください。
        ';
    }

    private function getNewsletterRegistrationHtmlTemplate(): string
    {
        return '
<h3>{{ newsletterRecipient.lastName }} {{ newsletterRecipient.firstName }} 様</h3>
<p>ニュースレターにご登録いただき、誠にありがとうございます。</p>
<p>ニュースレターの登録が正常に完了いたしました。</p>
        ';
    }

    private function getNewsletterRegistrationPlainTemplate(): string
    {
        return '
{{ newsletterRecipient.lastName }} {{ newsletterRecipient.firstName }} 様

ニュースレターにご登録いただき、誠にありがとうございます。

ニュースレターの登録が正常に完了いたしました。
        ';
    }

    private function getOrderConfirmationHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">

    {% set currencyIsoCode = order.currency.isoCode %}
    {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br>
    <br>
    {{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }} にご注文を受付いたしました。<br>
    <br>
    ご注文番号：{{ order.orderNumber }}<br>
    <br>
    お支払いが確認され次第、別途ご連絡いたします。ご注文の処理を開始いたします。<br>
    <br>
    こちらのリンクからご注文の現在の状況をご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}<br>
    このリンクからご注文の編集、お支払い方法の変更、追加のお支払いが可能です。<br>
    <br>
    <strong>ご注文内容：</strong><br>
    <br>

    <table border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品番号</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品画像</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品説明</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>数量</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>単価</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>小計</strong></td>
        </tr>

        {% for lineItem in order.nestedLineItems %}
            {% set nestingLevel = 0 %}
            {% set nestedItem = lineItem %}
            {% block lineItem %}
                <tr>
                    <td>{% if nestedItem.payload.productNumber is defined %}{{ nestedItem.payload.productNumber|u.wordwrap(80) }}{% endif %}</td>
                    <td>{% if nestedItem.cover is defined and nestedItem.cover is not null %}<img src="{{ nestedItem.cover.url }}" width="75" height="auto"/>{% endif %}</td>
                    <td>
                        {% if nestingLevel > 0 %}
                            {% for i in 1..nestingLevel %}
                                <span style="position: relative;">
                            <span style="display: inline-block;
                                position: absolute;
                                width: 6px;
                                height: 20px;
                                top: 0;
                                border-left:  2px solid rgba(0, 0, 0, 0.15);
                                margin-left: {{ i * 10 }}px;"></span>
                        </span>
                            {% endfor %}
                        {% endif %}

                        <div{% if nestingLevel > 0 %} style="padding-left: {{ (nestingLevel + 1) * 10 }}px"{% endif %}>
                            {{ nestedItem.label|u.wordwrap(80) }}
                        </div>

                        {% if nestedItem.payload.options is defined and nestedItem.payload.options|length >= 1 %}
                            <div>
                                {% for option in nestedItem.payload.options %}
                                    {{ option.group }}：{{ option.option }}
                                    {% if nestedItem.payload.options|last != option %}
                                        {{ " | " }}
                                    {% endif %}
                                {% endfor %}
                            </div>
                        {% endif %}

                        {% if nestedItem.payload.features is defined and nestedItem.payload.features|length >= 1 %}
                            {% set referencePriceFeatures = nestedItem.payload.features|filter(feature => feature.type == \'referencePrice\') %}
                            {% if referencePriceFeatures|length >= 1 %}
                                {% set referencePriceFeature = referencePriceFeatures|first %}
                                <div>
                                    {{ referencePriceFeature.value.purchaseUnit }} {{ referencePriceFeature.value.unitName }}
                                    ({{ referencePriceFeature.value.price|currency(currencyIsoCode) }} / {{ referencePriceFeature.value.referenceUnit }} {{ referencePriceFeature.value.unitName }})
                                </div>
                            {% endif %}
                        {% endif %}
                    </td>
                    <td style="text-align: center">{{ nestedItem.quantity }}</td>
                    <td>{{ nestedItem.unitPrice|currency(currencyIsoCode) }}</td>
                    <td>{{ nestedItem.totalPrice|currency(currencyIsoCode) }}</td>
                </tr>

                {% if nestedItem.children.count > 0 %}
                    {% set nestingLevel = nestingLevel + 1 %}
                    {% for lineItem in nestedItem.children %}
                        {% set nestedItem = lineItem %}
                        {{ block(\'lineItem\') }}
                    {% endfor %}
                {% endif %}
            {% endblock %}
        {% endfor %}
    </table>

    {% set delivery = order.deliveries.first %}

    {% set displayRounded = order.totalRounding.interval != 0.01 or order.totalRounding.decimals != order.itemRounding.decimals %}
    {% set decimals = order.totalRounding.decimals %}
    {% set total = order.price.totalPrice %}
    {% if displayRounded %}
        {% set total = order.price.rawTotal %}
        {% set decimals = order.itemRounding.decimals %}
    {% endif %}
    <p>
        <br>
        <br>
        {% for shippingCost in order.deliveries %}
            送料：{{ shippingCost.shippingCosts.totalPrice|currency(currencyIsoCode) }}<br>
        {% endfor %}

        商品合計（税抜）：{{ order.amountNet|currency(currencyIsoCode) }}<br>
        {% for calculatedTax in order.price.calculatedTaxes %}
            {% if order.taxStatus is same as(\'net\') %}税込{% else %}税込{% endif %} {{ calculatedTax.taxRate }}% 消費税：{{ calculatedTax.tax|currency(currencyIsoCode) }}<br>
        {% endfor %}
        {% if not displayRounded %}<strong>{% endif %}合計金額（税込）：{{ total|currency(currencyIsoCode,decimals=decimals) }}{% if not displayRounded %}</strong>{% endif %}<br>
        {% if displayRounded %}
            <strong>端数調整後合計金額：{{ order.price.totalPrice|currency(currencyIsoCode,decimals=order.totalRounding.decimals) }}</strong><br>
        {% endif %}
        <br>

        {% if order.transactions is defined and order.transactions is not empty %}
            <strong>お支払い方法：</strong> {{ order.transactions.first.paymentMethod.translated.name }}<br>
            {{ order.transactions.first.paymentMethod.translated.description }}<br>
            <br>
        {% endif %}

        {% if delivery %}
            <strong>配送方法：</strong> {{ delivery.shippingMethod.translated.name }}<br>
            {{ delivery.shippingMethod.translated.description }}<br>
            <br>
        {% endif %}

        {% set billingAddress = order.addresses.get(order.billingAddressId) %}
        <strong>請求先住所：</strong><br>
        {{ billingAddress.company }}<br>
        {{ billingAddress.lastName }} {{ billingAddress.firstName }}<br>
        {{ billingAddress.zipcode }} {{ billingAddress.city }}<br>
        {{ billingAddress.street }} <br>
        {{ billingAddress.country.translated.name }}<br>
        <br>

        {% if delivery %}
            <strong>配送先住所：</strong><br>
            {{ delivery.shippingOrderAddress.company }}<br>
            {{ delivery.shippingOrderAddress.lastName }} {{ delivery.shippingOrderAddress.firstName }}<br>
            {{ delivery.shippingOrderAddress.zipcode}} {{ delivery.shippingOrderAddress.city }}<br>
            {{ delivery.shippingOrderAddress.street }} <br>
            {{ delivery.shippingOrderAddress.country.translated.name }}<br>
            <br>
        {% endif %}
        {% if order.orderCustomer.vatIds %}
            VAT番号：{{ order.orderCustomer.vatIds|first }}
            正常にご注文が完了し、EU諸国にお住まいの場合は、付加価値税を免除した商品をお受け取りいただけます。<br>
        {% endif %}
        <br>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        <br>
        ご不明な点がございましたら、お気軽にお問い合わせください。
        <br>
        {% if a11yDocuments is defined and a11yDocuments is not empty %}
            <br>
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>
            <ul>
                {% for a11y in a11yDocuments %}
                    {% set documentLink = rawUrl(
                        \'frontend.account.order.single.document.a11y\',
                        {
                            documentId: a11y.documentId,
                            deepLinkCode: a11y.deepLinkCode,
                            fileType: a11y.fileExtension,
                        },
                        salesChannel.domains|first.url
                    )%}
                    <li><a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a></li>
                {% endfor %}
            </ul>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
    <br>
</div>
        ';
    }

    private function getOrderConfirmationPlainTemplate(): string
    {
        return '
{% set currencyIsoCode = order.currency.isoCode %}
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }} にご注文を受付いたしました。

ご注文番号：{{ order.orderNumber }}

お支払いが確認され次第、別途ご連絡いたします。ご注文の処理を開始いたします。

こちらのリンクからご注文の現在の状況をご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
このリンクからご注文の編集、お支払い方法の変更、追加のお支払いが可能です。

ご注文内容：

{% for lineItem in order.lineItems %}
{{ loop.index }}. 
---------------------
{% if lineItem.payload.productNumber is defined %}
商品番号：{{ lineItem.payload.productNumber|u.wordwrap(80) }}
{% endif %}
{% if lineItem.cover is defined and lineItem.cover is not null %}
画像：{{ lineItem.cover.alt }}
{% endif %}
商品説明：{{ lineItem.label|u.wordwrap(80) }}
{% if lineItem.payload.options is defined and lineItem.payload.options|length >= 1 %}
{% for option in lineItem.payload.options %}
{{ option.group }}：{{ option.option }}{{ ", " }}
{% endfor %}
{% endif %}
{% if lineItem.payload.features is defined and lineItem.payload.features|length >= 1 %}
{% set referencePriceFeatures = lineItem.payload.features|filter(feature => feature.type == \'referencePrice\') %}
{% if referencePriceFeatures|length >= 1 %}
{% set referencePriceFeature = referencePriceFeatures|first %}
{{ referencePriceFeature.value.purchaseUnit }} {{ referencePriceFeature.value.unitName }}({{ referencePriceFeature.value.price|currency(currencyIsoCode) }} / {{ referencePriceFeature.value.referenceUnit }} {{ referencePriceFeature.value.unitName }})
{% endif %}
{% endif %}
数量：{{ lineItem.quantity }}
単価：{{ lineItem.unitPrice|currency(currencyIsoCode) }}
小計：{{ lineItem.totalPrice|currency(currencyIsoCode) }}

{% endfor %}
{% set delivery = order.deliveries.first %}
{% set displayRounded = order.totalRounding.interval != 0.01 or order.totalRounding.decimals != order.itemRounding.decimals %}
{% set decimals = order.totalRounding.decimals %}
{% set total = order.price.totalPrice %}
{% if displayRounded %}
    {% set total = order.price.rawTotal %}
    {% set decimals = order.itemRounding.decimals %}
{% endif %}
{% for shippingCost in order.deliveries %}
送料：{{ shippingCost.shippingCosts.totalPrice|currency(currencyIsoCode) }}
{% endfor %}
商品合計（税抜）：{{ order.amountNet|currency(currencyIsoCode) }}
{% for calculatedTax in order.price.calculatedTaxes %}
{% if order.taxStatus is same as(\'net\') %}税込{% else %}税込{% endif %} {{ calculatedTax.taxRate }}% 消費税：{{ calculatedTax.tax|currency(currencyIsoCode) }}
{% endfor %}
合計金額（税込）：{{ total|currency(currencyIsoCode,decimals=decimals) }}
{% if displayRounded %}
端数調整後合計金額：{{ order.price.totalPrice|currency(currencyIsoCode,decimals=order.totalRounding.decimals) }}
{% endif %}

{% if order.transactions is defined and order.transactions is not empty %}
お支払い方法：{{ order.transactions.first.paymentMethod.translated.name }}
{{ order.transactions.first.paymentMethod.translated.description }}
{% endif %}

{% if delivery %}
配送方法：{{ delivery.shippingMethod.translated.name }}
{{ delivery.shippingMethod.translated.description }}
{% endif %}
{% set billingAddress = order.addresses.get(order.billingAddressId) %}
請求先住所：
{{ billingAddress.company }}
{{ billingAddress.lastName }} {{ billingAddress.firstName }}
{{ billingAddress.zipcode }} {{ billingAddress.city }}
{{ billingAddress.street }}
{{ billingAddress.country.translated.name }}

{% if delivery %}
配送先住所：
{{ delivery.shippingOrderAddress.company }}
{{ delivery.shippingOrderAddress.lastName }} {{ delivery.shippingOrderAddress.firstName }}
{{ delivery.shippingOrderAddress.zipcode}} {{ delivery.shippingOrderAddress.city }}
{{ delivery.shippingOrderAddress.street }}
{{ delivery.shippingOrderAddress.country.translated.name }}
{% endif %}

{% if order.orderCustomer.vatIds %}
VAT番号：{{ order.orderCustomer.vatIds|first }}
正常にご注文が完了し、EU諸国にお住まいの場合は、付加価値税を免除した商品をお受け取りいただけます。

{% endif %}
ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ご不明な点がございましたら、お気軽にお問い合わせください。

{% if a11yDocuments is defined and a11yDocuments is not empty %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
{% set documentLink = rawUrl(
    \'frontend.account.order.single.document.a11y\',
    {
        documentId: a11y.documentId,
        deepLinkCode: a11y.deepLinkCode,
        fileType: a11y.fileExtension,
    },
    salesChannel.domains|first.url
)%}
- {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。
ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getCustomerRegistrationHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <p>
        {{ customer.lastName }} {{ customer.firstName }} 様<br/>
        <br/>
        この度はショップにご登録いただき、誠にありがとうございます。<br/>
        メールアドレス <strong>{{ customer.email }}</strong> とご設定いただいたパスワードでアクセスいただけます。<br/>
        パスワードはいつでも変更していただけます。
    </p>
</div>
        ';
    }

    private function getCustomerRegistrationPlainTemplate(): string
    {
        return '
{{ customer.lastName }} {{ customer.firstName }} 様

この度はショップにご登録いただき、誠にありがとうございます。
メールアドレス {{ customer.email }} とご設定いただいたパスワードでアクセスいただけます。
パスワードはいつでも変更していただけます。
        ';
    }

    private function getDoubleOptInRegistrationHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <p>
        {{ customer.lastName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} にご登録いただき、誠にありがとうございます。<br/>
        以下のリンクからご登録の確認をお願いします：<br/>
        <br/>
        <a href="{{ confirmUrl }}">登録を完了する</a><br/>
        <br/>
        この確認により、契約履行の一環として今後メールをお送りすることにご同意いただいたものとします。
    </p>
</div>
        ';
    }

    private function getDoubleOptInRegistrationPlainTemplate(): string
    {
        return '
{{ customer.lastName }} 様

{{ salesChannel.translated.name }} にご登録いただき、誠にありがとうございます。
以下のリンクからご登録の確認をお願いします：

{{ confirmUrl }}

この確認により、契約履行の一環として今後メールをお送りすることにご同意いただいたものとします。
        ';
    }

    private function getCustomerPasswordRecoveryHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <p>
        {{ customerRecovery.customer.lastName }} {{ customerRecovery.customer.firstName }} 様<br/>
        <br/>
        {{ shopName }} のアカウントの新しいパスワードをリクエストされました。<br/>
        以下のリンクをクリックしてパスワードをリセットしてください：<br/>
        <br/>
        <a href="{{ resetUrl }}">{{ resetUrl }}</a><br/>
        <br/>
        このリンクの有効期限は2時間です。<br/>
        パスワードをリセットしない場合は、このメールを無視してください。変更は行われません。<br/>
        <br/>
        敬具<br/>
        {{ shopName }} チーム
    </p>
</div>
        ';
    }

    private function getCustomerPasswordRecoveryPlainTemplate(): string
    {
        return '
{{ customerRecovery.customer.lastName }} {{ customerRecovery.customer.firstName }} 様

{{ shopName }} のアカウントの新しいパスワードをリクエストされました。
以下のリンクをクリックしてパスワードをリセットしてください：

{{ resetUrl }}

このリンクの有効期限は2時間です。
パスワードをリセットしない場合は、このメールを無視してください。変更は行われません。

敬具
{{ shopName }} チーム
        ';
    }

    private function getCustomerGroupAcceptedHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ customer.lastName }} 様<br/>
        <br/>
        お客様のアカウントが顧客グループ「{{ customerGroup.translated.name }}」として有効化されました。<br/>
        今後はこの顧客グループの新しい条件でお買い物いただけます。<br/><br/>

        ご不明な点がございましたら、いつでもお気軽にお問い合わせください。
    </p>
</div>
        ';
    }

    private function getCustomerGroupAcceptedPlainTemplate(): string
    {
        return '
{{ customer.lastName }} 様

お客様のアカウントが顧客グループ「{{ customerGroup.translated.name }}」として有効化されました。
今後はこの顧客グループの新しい条件でお買い物いただけます。

ご不明な点がございましたら、いつでもお気軽にお問い合わせください。
        ';
    }

    private function getCustomerGroupRejectedHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ customer.lastName }} 様<br/>
        <br/>
        顧客グループ「{{ customerGroup.translated.name }}」の条件にご興味をお持ちいただき、誠にありがとうございます。<br/>
        申し訳ございませんが、この顧客グループでお客様のアカウントを有効化することができません。<br/><br/>

        ご不明な点がございましたら、お電話またはメールにてお気軽にお問い合わせください。
    </p>
</div>
        ';
    }

    private function getCustomerGroupRejectedPlainTemplate(): string
    {
        return '
{{ customer.lastName }} 様

顧客グループ「{{ customerGroup.translated.name }}」の条件にご興味をお持ちいただき、誠にありがとうございます。
申し訳ございませんが、この顧客グループでお客様のアカウントを有効化することができません。

ご不明な点がございましたら、お電話またはメールにてお気軽にお問い合わせください。
        ';
    }

    private function getCustomerGroupChangeAcceptedHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <p>
        いつもお世話になっております。<br/>
        <br/>
        {{ salesChannel.translated.name }} での法人アカウントが有効化されました。<br/>
        今後は税抜価格でご購入いただけます。
    </p>
</div>
        ';
    }

    private function getCustomerGroupChangeAcceptedPlainTemplate(): string
    {
        return '
いつもお世話になっております。

{{ salesChannel.translated.name }} での法人アカウントが有効化されました。
今後は税抜価格でご購入いただけます。
        ';
    }

    private function getCustomerGroupChangeRejectedHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <p>
        いつもお世話になっております。<br/>
        <br/>
        この度は法人価格にご興味をお持ちいただき、誠にありがとうございます。<br/>
        申し訳ございませんが、現在営業許可を取得していないため、法人のお客様としてお受けすることができません。<br/>
        ご不明な点がございましたら、お電話、FAX、またはメールにてお気軽にお問い合わせください。
    </p>
</div>
        ';
    }

    private function getCustomerGroupChangeRejectedPlainTemplate(): string
    {
        return '
いつもお世話になっております。

この度は法人価格にご興味をお持ちいただき、誠にありがとうございます。
申し訳ございませんが、現在営業許可を取得していないため、法人のお客様としてお受けすることができません。
ご不明な点がございましたら、お電話、FAX、またはメールにてお気軽にお問い合わせください。
        ';
    }

    private function getUserPasswordRecoveryHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <p>
        {{ userRecovery.user.lastName }} {{ userRecovery.user.firstName }} 様<br/>
        <br/>
        パスワードのリセットがリクエストされました。<br/>
        新しいパスワードを設定するために、以下のリンクをクリックして確認してください。<br/>
        <br/>
        <a href="{{ resetUrl }}">パスワードをリセット</a><br/>
        <br/>
        このリンクの有効期限は2時間です。期限が切れた場合は、新しい確認リンクをリクエストしてください。<br/>
        パスワードをリセットしない場合は、このメールを無視してください。変更は行われません。
    </p>
</div>
        ';
    }

    private function getUserPasswordRecoveryPlainTemplate(): string
    {
        return '
{{ userRecovery.user.lastName }} {{ userRecovery.user.firstName }} 様

パスワードのリセットがリクエストされました。
新しいパスワードを設定するために、以下のリンクをクリックして確認してください。

パスワードをリセット: {{ resetUrl }}

このリンクの有効期限は2時間です。期限が切れた場合は、新しい確認リンクをリクエストしてください。
パスワードをリセットしない場合は、このメールを無視してください。変更は行われません。
        ';
    }

    private function getContactFormHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <p>
        お問い合わせフォームより以下のメッセージが送信されました。<br/>
        <br/>
        お問い合わせ者名：{{ contactFormData.lastName }} {{ contactFormData.firstName }}
        <br/>
        メールアドレス：{{ contactFormData.email }}
        <br/>
        電話番号：{{ contactFormData.phone }}<br/>
        <br/>
        件名：{{ contactFormData.subject }}<br/>
        <br/>
        メッセージ：<br/>
        {{ contactFormData.comment|nl2br }}<br/>
    </p>
</div>
        ';
    }

    private function getContactFormPlainTemplate(): string
    {
        return '
お問い合わせフォームより以下のメッセージが送信されました。

お問い合わせ者名：{{ contactFormData.lastName }} {{ contactFormData.firstName }}
メールアドレス：{{ contactFormData.email }}
電話番号：{{ contactFormData.phone }}

件名：{{ contactFormData.subject }}

メッセージ：
{{ contactFormData.comment }}
        ';
    }

    private function getPasswordChangeRequestHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <p>
        {{ customer.lastName }} {{ customer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でパスワードのリセットがリクエストされました。<br/>
        新しいパスワードを設定するために、以下のリンクをクリックして確認してください。<br/>
        <br/>
        <a href="{{ resetUrl }}">パスワードをリセット</a><br/>
        <br/>
        このリンクの有効期限は2時間です。期限が切れた場合は、新しい確認リンクをリクエストしてください。<br/>
        パスワードをリセットしない場合は、このメールを無視してください。変更は行われません。
    </p>
</div>
        ';
    }

    private function getPasswordChangeRequestPlainTemplate(): string
    {
        return '
{{ customer.lastName }} {{ customer.firstName }} 様

{{ salesChannel.translated.name }} でパスワードのリセットがリクエストされました。
新しいパスワードを設定するために、以下のリンクをクリックして確認してください。

パスワードをリセット: {{ resetUrl }}

このリンクの有効期限は2時間です。期限が切れた場合は、新しい確認リンクをリクエストしてください。
パスワードをリセットしない場合は、このメールを無視してください。変更は行われません。
        ';
    }

    private function getOrderStateCancelledHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getOrderStateCancelledPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の状況が変更されました。
新しい状況は以下の通りです：{{ order.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getOrderStateDoneHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getOrderStateDonePlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の状況が変更されました。
新しい状況は以下の通りです：{{ order.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getOrderStateOpenHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getOrderStateOpenPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の状況が変更されました。
新しい状況は以下の通りです：{{ order.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getOrderStateInProgressHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getOrderStateInProgressPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の状況が変更されました。
新しい状況は以下の通りです：{{ order.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getDeliveryStateShippedHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の配送状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.deliveries.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getDeliveryStateShippedPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の配送状況が変更されました。
新しい状況は以下の通りです：{{ order.deliveries.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getDeliveryStateShippedPartiallyHtmlTemplate(): string
    {
        return '<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の配送状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.deliveries.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>';
    }

    private function getDeliveryStateShippedPartiallyPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の配送状況が変更されました。
新しい状況は以下の通りです：{{ order.deliveries.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getDeliveryStateCancelledHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の配送状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.deliveries.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getDeliveryStateCancelledPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の配送状況が変更されました。
新しい状況は以下の通りです：{{ order.deliveries.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getDeliveryStateReturnedHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の配送状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.deliveries.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getDeliveryStateReturnedPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の配送状況が変更されました。
新しい状況は以下の通りです：{{ order.deliveries.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getDeliveryStateReturnedPartiallyHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の配送状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.deliveries.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getDeliveryStateReturnedPartiallyPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の配送状況が変更されました。
新しい状況は以下の通りです：{{ order.deliveries.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getPaymentStatePaidHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">

    {% set currencyIsoCode = order.currency.isoCode %}
    {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br>
    <br>
    お支払いを確認いたしました。ご注文の処理を開始いたします。<br>
    ご注文番号：{{ order.orderNumber }}<br>
    <br>

    <strong>ご注文内容：</strong><br>
    <br>

    <table border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品番号</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品画像</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品説明</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>数量</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>単価</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>小計</strong></td>
        </tr>

        {% for lineItem in order.nestedLineItems %}
            {% set nestingLevel = 0 %}
            {% set nestedItem = lineItem %}
            {% block lineItem %}
                <tr>
                    <td>{% if nestedItem.payload.productNumber is defined %}{{ nestedItem.payload.productNumber|u.wordwrap(80) }}{% endif %}</td>
                    <td>{% if nestedItem.cover is defined and nestedItem.cover is not null %}<img src="{{ nestedItem.cover.url }}" width="75" height="auto"/>{% endif %}</td>
                    <td>
                        {% if nestingLevel > 0 %}
                            {% for i in 1..nestingLevel %}
                                <span style="position: relative;">
                            <span style="display: inline-block;
                                position: absolute;
                                width: 6px;
                                height: 20px;
                                top: 0;
                                border-left:  2px solid rgba(0, 0, 0, 0.15);
                                margin-left: {{ i * 10 }}px;"></span>
                        </span>
                            {% endfor %}
                        {% endif %}

                        <div{% if nestingLevel > 0 %} style="padding-left: {{ (nestingLevel + 1) * 10 }}px"{% endif %}>
                            {{ nestedItem.label|u.wordwrap(80) }}
                        </div>

                        {% if nestedItem.payload.options is defined and nestedItem.payload.options|length >= 1 %}
                            <div>
                                {% for option in nestedItem.payload.options %}
                                    {{ option.group }}：{{ option.option }}
                                    {% if nestedItem.payload.options|last != option %}
                                        {{ " | " }}
                                    {% endif %}
                                {% endfor %}
                            </div>
                        {% endif %}

                        {% if nestedItem.payload.features is defined and nestedItem.payload.features|length >= 1 %}
                            {% set referencePriceFeatures = nestedItem.payload.features|filter(feature => feature.type == \'referencePrice\') %}
                            {% if referencePriceFeatures|length >= 1 %}
                                {% set referencePriceFeature = referencePriceFeatures|first %}
                                <div>
                                    {{ referencePriceFeature.value.purchaseUnit }} {{ referencePriceFeature.value.unitName }}
                                    ({{ referencePriceFeature.value.price|currency(currencyIsoCode) }} / {{ referencePriceFeature.value.referenceUnit }} {{ referencePriceFeature.value.unitName }})
                                </div>
                            {% endif %}
                        {% endif %}
                    </td>
                    <td style="text-align: center">{{ nestedItem.quantity }}</td>
                    <td>{{ nestedItem.unitPrice|currency(currencyIsoCode) }}</td>
                    <td>{{ nestedItem.totalPrice|currency(currencyIsoCode) }}</td>
                </tr>

                {% if nestedItem.children.count > 0 %}
                    {% set nestingLevel = nestingLevel + 1 %}
                    {% for lineItem in nestedItem.children %}
                        {% set nestedItem = lineItem %}
                        {{ block(\'lineItem\') }}
                    {% endfor %}
                {% endif %}
            {% endblock %}
        {% endfor %}
    </table>

    {% set delivery = order.deliveries.first %}

    {% set displayRounded = order.totalRounding.interval != 0.01 or order.totalRounding.decimals != order.itemRounding.decimals %}
    {% set decimals = order.totalRounding.decimals %}
    {% set total = order.price.totalPrice %}
    {% if displayRounded %}
        {% set total = order.price.rawTotal %}
        {% set decimals = order.itemRounding.decimals %}
    {% endif %}
    <p>
        <br>
        <br>
        {% for shippingCost in order.deliveries %}
            送料：{{ shippingCost.shippingCosts.totalPrice|currency(currencyIsoCode) }}<br>
        {% endfor %}

        商品合計（税抜）：{{ order.amountNet|currency(currencyIsoCode) }}<br>
        {% for calculatedTax in order.price.calculatedTaxes %}
            {% if order.taxStatus is same as(\'net\') %}税込{% else %}税込{% endif %} {{ calculatedTax.taxRate }}% 消費税：{{ calculatedTax.tax|currency(currencyIsoCode) }}<br>
        {% endfor %}
        {% if not displayRounded %}<strong>{% endif %}合計金額（税込）：{{ total|currency(currencyIsoCode,decimals=decimals) }}{% if not displayRounded %}</strong>{% endif %}<br>
        {% if displayRounded %}
            <strong>端数調整後合計金額：{{ order.price.totalPrice|currency(currencyIsoCode,decimals=order.totalRounding.decimals) }}</strong><br>
        {% endif %}
        <br>

        {% if order.transactions is defined and order.transactions is not empty %}
            <strong>お支払い方法：</strong> {{ order.transactions.first.paymentMethod.translated.name }}<br>
            {{ order.transactions.first.paymentMethod.translated.description }}<br>
            <br>
        {% endif %}

        {% if delivery %}
            <strong>配送方法：</strong> {{ delivery.shippingMethod.translated.name }}<br>
            {{ delivery.shippingMethod.translated.description }}<br>
            <br>
        {% endif %}

        {% set billingAddress = order.addresses.get(order.billingAddressId) %}
        <strong>請求先住所：</strong><br>
        {{ billingAddress.company }}<br>
        {{ billingAddress.lastName }} {{ billingAddress.firstName }}<br>
        {{ billingAddress.zipcode }} {{ billingAddress.city }}<br>
        {{ billingAddress.street }} <br>
        {{ billingAddress.country.translated.name }}<br>
        <br>

        {% if delivery %}
            <strong>配送先住所：</strong><br>
            {{ delivery.shippingOrderAddress.company }}<br>
            {{ delivery.shippingOrderAddress.lastName }} {{ delivery.shippingOrderAddress.firstName }}<br>
            {{ delivery.shippingOrderAddress.zipcode}} {{ delivery.shippingOrderAddress.city }}<br>
            {{ delivery.shippingOrderAddress.street }} <br>
            {{ delivery.shippingOrderAddress.country.translated.name }}<br>
            <br>
        {% endif %}
        {% if order.orderCustomer.vatIds %}
            VAT番号：{{ order.orderCustomer.vatIds|first }}
            正常にご注文が完了し、EU諸国にお住まいの場合は、付加価値税を免除した商品をお受け取りいただけます。<br>
        {% endif %}
        <br>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        <br>
        ご不明な点がございましたら、お気軽にお問い合わせください。
        <br>
        {% if a11yDocuments is defined and a11yDocuments is not empty %}
            <br>
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>
            <ul>
                {% for a11y in a11yDocuments %}
                    {% set documentLink = rawUrl(
                        \'frontend.account.order.single.document.a11y\',
                        {
                            documentId: a11y.documentId,
                            deepLinkCode: a11y.deepLinkCode,
                            fileType: a11y.fileExtension,
                        },
                        salesChannel.domains|first.url
                    )%}
                    <li><a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a></li>
                {% endfor %}
            </ul>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
    <br>
</div>
        ';
    }

    private function getPaymentStatePaidPlainTemplate(): string
    {
        return '
{% set currencyIsoCode = order.currency.isoCode %}
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

お支払いを確認いたしました。ご注文の処理を開始いたします。

ご注文番号：{{ order.orderNumber }}


ご注文内容：

{% for lineItem in order.lineItems %}
{{ loop.index }}. 
---------------------
{% if lineItem.payload.productNumber is defined %}
商品番号：{{ lineItem.payload.productNumber|u.wordwrap(80) }}
{% endif %}
{% if nestedItem.cover is defined and nestedItem.cover is not null %}
画像：{{ lineItem.cover.alt }}
{% endif %}
商品説明：{{ lineItem.label|u.wordwrap(80) }}
{% if lineItem.payload.options is defined and lineItem.payload.options|length >= 1 %}
{% for option in lineItem.payload.options %}
{{ option.group }}：{{ option.option }}{{ ", " }}
{% endfor %}
{% endif %}
{% if lineItem.payload.features is defined and lineItem.payload.features|length >= 1 %}
{% set referencePriceFeatures = lineItem.payload.features|filter(feature => feature.type == \'referencePrice\') %}
{% if referencePriceFeatures|length >= 1 %}
{% set referencePriceFeature = referencePriceFeatures|first %}
{{ referencePriceFeature.value.purchaseUnit }} {{ referencePriceFeature.value.unitName }}({{ referencePriceFeature.value.price|currency(currencyIsoCode) }} / {{ referencePriceFeature.value.referenceUnit }} {{ referencePriceFeature.value.unitName }})
{% endif %}
{% endif %}
数量：{{ lineItem.quantity }}
単価：{{ lineItem.unitPrice|currency(currencyIsoCode) }}
小計：{{ lineItem.totalPrice|currency(currencyIsoCode) }}

{% endfor %}
{% set delivery = order.deliveries.first %}
{% set displayRounded = order.totalRounding.interval != 0.01 or order.totalRounding.decimals != order.itemRounding.decimals %}
{% set decimals = order.totalRounding.decimals %}
{% set total = order.price.totalPrice %}
{% if displayRounded %}
    {% set total = order.price.rawTotal %}
    {% set decimals = order.itemRounding.decimals %}
{% endif %}
{% for shippingCost in order.deliveries %}
送料：{{ shippingCost.shippingCosts.totalPrice|currency(currencyIsoCode) }}
{% endfor %}
商品合計（税抜）：{{ order.amountNet|currency(currencyIsoCode) }}
{% for calculatedTax in order.price.calculatedTaxes %}
{% if order.taxStatus is same as(\'net\') %}税込{% else %}税込{% endif %} {{ calculatedTax.taxRate }}% 消費税：{{ calculatedTax.tax|currency(currencyIsoCode) }}
{% endfor %}
合計金額（税込）：{{ total|currency(currencyIsoCode,decimals=decimals) }}
{% if displayRounded %}
端数調整後合計金額：{{ order.price.totalPrice|currency(currencyIsoCode,decimals=order.totalRounding.decimals) }}
{% endif %}

{% if order.transactions is defined and order.transactions is not empty %}
お支払い方法：{{ order.transactions.first.paymentMethod.translated.name }}
{{ order.transactions.first.paymentMethod.translated.description }}
{% endif %}

{% if delivery %}
配送方法：{{ delivery.shippingMethod.translated.name }}
{{ delivery.shippingMethod.translated.description }}
{% endif %}
{% set billingAddress = order.addresses.get(order.billingAddressId) %}
請求先住所：
{{ billingAddress.company }}
{{ billingAddress.lastName }} {{ billingAddress.firstName }}
{{ billingAddress.zipcode }} {{ billingAddress.city }}
{{ billingAddress.street }}
{{ billingAddress.country.translated.name }}

{% if delivery %}
配送先住所：
{{ delivery.shippingOrderAddress.company }}
{{ delivery.shippingOrderAddress.lastName }} {{ delivery.shippingOrderAddress.firstName }}
{{ delivery.shippingOrderAddress.zipcode}} {{ delivery.shippingOrderAddress.city }}
{{ delivery.shippingOrderAddress.street }}
{{ delivery.shippingOrderAddress.country.translated.name }}
{% endif %}

{% if order.orderCustomer.vatIds %}
VAT番号：{{ order.orderCustomer.vatIds|first }}
正常にご注文が完了し、EU諸国にお住まいの場合は、付加価値税を免除した商品をお受け取りいただけます。

{% endif %}
ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ご不明な点がございましたら、お気軽にお問い合わせください。

{% if a11yDocuments is defined and a11yDocuments is not empty %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
{% set documentLink = rawUrl(
    \'frontend.account.order.single.document.a11y\',
    {
        documentId: a11y.documentId,
        deepLinkCode: a11y.deepLinkCode,
        fileType: a11y.fileExtension,
    },
    salesChannel.domains|first.url
)%}
- {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。
ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getPaymentStateOpenHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getPaymentStateOpenPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。
新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getPaymentStateAuthorizedHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getPaymentStateAuthorizedPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。
新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getPaymentStatePaidPartiallyHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getPaymentStatePaidPartiallyPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。
新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getPaymentStateRefundedHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getPaymentStateRefundedPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。
新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getPaymentStateRefundedPartiallyHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getPaymentStateRefundedPartiallyPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。
新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getPaymentStateRemindedHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getPaymentStateRemindedPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。
新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getPaymentStateCancelledHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">

    {% set currencyIsoCode = order.currency.isoCode %}
    {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br>
    <br>
    {{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }} にご注文を受付いたしました。<br>
    <br>
    ご注文番号：{{ order.orderNumber }}<br>
    <br>
    {{ order.transactions.first.paymentMethod.translated.name }} でのお支払いがまだ完了していません。以下のURLから支払い手続きを再開していただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}<br>
    <br>
    <strong>ご注文内容：</strong><br>
    <br>

    <table border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品番号</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品画像</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品説明</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>数量</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>単価</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>小計</strong></td>
        </tr>

        {% for lineItem in order.nestedLineItems %}
            {% set nestingLevel = 0 %}
            {% set nestedItem = lineItem %}
            {% block lineItem %}
                <tr>
                    <td>{% if nestedItem.payload.productNumber is defined %}{{ nestedItem.payload.productNumber|u.wordwrap(80) }}{% endif %}</td>
                    <td>{% if nestedItem.cover is defined and nestedItem.cover is not null %}<img src="{{ nestedItem.cover.url }}" width="75" height="auto"/>{% endif %}</td>
                    <td>
                        {% if nestingLevel > 0 %}
                            {% for i in 1..nestingLevel %}
                                <span style="position: relative;">
                            <span style="display: inline-block;
                                position: absolute;
                                width: 6px;
                                height: 20px;
                                top: 0;
                                border-left:  2px solid rgba(0, 0, 0, 0.15);
                                margin-left: {{ i * 10 }}px;"></span>
                        </span>
                            {% endfor %}
                        {% endif %}

                        <div{% if nestingLevel > 0 %} style="padding-left: {{ (nestingLevel + 1) * 10 }}px"{% endif %}>
                            {{ nestedItem.label|u.wordwrap(80) }}
                        </div>

                        {% if nestedItem.payload.options is defined and nestedItem.payload.options|length >= 1 %}
                            <div>
                                {% for option in nestedItem.payload.options %}
                                    {{ option.group }}：{{ option.option }}
                                    {% if nestedItem.payload.options|last != option %}
                                        {{ " | " }}
                                    {% endif %}
                                {% endfor %}
                            </div>
                        {% endif %}

                        {% if nestedItem.payload.features is defined and nestedItem.payload.features|length >= 1 %}
                            {% set referencePriceFeatures = nestedItem.payload.features|filter(feature => feature.type == \'referencePrice\') %}
                            {% if referencePriceFeatures|length >= 1 %}
                                {% set referencePriceFeature = referencePriceFeatures|first %}
                                <div>
                                    {{ referencePriceFeature.value.purchaseUnit }} {{ referencePriceFeature.value.unitName }}
                                    ({{ referencePriceFeature.value.price|currency(currencyIsoCode) }} / {{ referencePriceFeature.value.referenceUnit }} {{ referencePriceFeature.value.unitName }})
                                </div>
                            {% endif %}
                        {% endif %}
                    </td>
                    <td style="text-align: center">{{ nestedItem.quantity }}</td>
                    <td>{{ nestedItem.unitPrice|currency(currencyIsoCode) }}</td>
                    <td>{{ nestedItem.totalPrice|currency(currencyIsoCode) }}</td>
                </tr>

                {% if nestedItem.children.count > 0 %}
                    {% set nestingLevel = nestingLevel + 1 %}
                    {% for lineItem in nestedItem.children %}
                        {% set nestedItem = lineItem %}
                        {{ block(\'lineItem\') }}
                    {% endfor %}
                {% endif %}
            {% endblock %}
        {% endfor %}
    </table>

    {% set delivery = order.deliveries.first %}

    {% set displayRounded = order.totalRounding.interval != 0.01 or order.totalRounding.decimals != order.itemRounding.decimals %}
    {% set decimals = order.totalRounding.decimals %}
    {% set total = order.price.totalPrice %}
    {% if displayRounded %}
        {% set total = order.price.rawTotal %}
        {% set decimals = order.itemRounding.decimals %}
    {% endif %}
    <p>
        <br>
        <br>
        {% for shippingCost in order.deliveries %}
            送料：{{ shippingCost.shippingCosts.totalPrice|currency(currencyIsoCode) }}<br>
        {% endfor %}

        商品合計（税抜）：{{ order.amountNet|currency(currencyIsoCode) }}<br>
        {% for calculatedTax in order.price.calculatedTaxes %}
            {% if order.taxStatus is same as(\'net\') %}税込{% else %}税込{% endif %} {{ calculatedTax.taxRate }}% 消費税：{{ calculatedTax.tax|currency(currencyIsoCode) }}<br>
        {% endfor %}
        {% if not displayRounded %}<strong>{% endif %}合計金額（税込）：{{ total|currency(currencyIsoCode,decimals=decimals) }}{% if not displayRounded %}</strong>{% endif %}<br>
        {% if displayRounded %}
            <strong>端数調整後合計金額：{{ order.price.totalPrice|currency(currencyIsoCode,decimals=order.totalRounding.decimals) }}</strong><br>
        {% endif %}
        <br>

        {% if order.transactions is defined and order.transactions is not empty %}
            <strong>お支払い方法：</strong> {{ order.transactions.first.paymentMethod.translated.name }}<br>
            {{ order.transactions.first.paymentMethod.translated.description }}<br>
            <br>
        {% endif %}

        {% if delivery %}
            <strong>配送方法：</strong> {{ delivery.shippingMethod.translated.name }}<br>
            {{ delivery.shippingMethod.translated.description }}<br>
            <br>
        {% endif %}

        {% set billingAddress = order.addresses.get(order.billingAddressId) %}
        <strong>請求先住所：</strong><br>
        {{ billingAddress.company }}<br>
        {{ billingAddress.lastName }} {{ billingAddress.firstName }}<br>
        {{ billingAddress.zipcode }} {{ billingAddress.city }}<br>
        {{ billingAddress.street }} <br>
        {{ billingAddress.country.translated.name }}<br>
        <br>

        {% if delivery %}
            <strong>配送先住所：</strong><br>
            {{ delivery.shippingOrderAddress.company }}<br>
            {{ delivery.shippingOrderAddress.lastName }} {{ delivery.shippingOrderAddress.firstName }}<br>
            {{ delivery.shippingOrderAddress.zipcode}} {{ delivery.shippingOrderAddress.city }}<br>
            {{ delivery.shippingOrderAddress.street }} <br>
            {{ delivery.shippingOrderAddress.country.translated.name }}<br>
            <br>
        {% endif %}
        {% if order.orderCustomer.vatIds %}
            VAT番号：{{ order.orderCustomer.vatIds|first }}
            正常にご注文が完了し、EU諸国にお住まいの場合は、付加価値税を免除した商品をお受け取りいただけます。<br>
        {% endif %}
        <br>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        <br>
        ご不明な点がございましたら、お気軽にお問い合わせください。
        <br>
        {% if a11yDocuments is defined and a11yDocuments is not empty %}
            <br>
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>
            <ul>
                {% for a11y in a11yDocuments %}
                    {% set documentLink = rawUrl(
                        \'frontend.account.order.single.document.a11y\',
                        {
                            documentId: a11y.documentId,
                            deepLinkCode: a11y.deepLinkCode,
                            fileType: a11y.fileExtension,
                        },
                        salesChannel.domains|first.url
                    )%}
                    <li><a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a></li>
                {% endfor %}
            </ul>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
    <br>
</div>
        ';
    }

    private function getPaymentStateCancelledPlainTemplate(): string
    {
        return '
{% set currencyIsoCode = order.currency.isoCode %}
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }} にご注文を受付いたしました。

ご注文番号：{{ order.orderNumber }}

{{ order.transactions.first.paymentMethod.translated.name }} でのお支払いがまだ完了していません。以下のURLから支払い手続きを再開していただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}

ご注文内容：

{% for lineItem in order.lineItems %}
{{ loop.index }}. 
---------------------
{% if lineItem.payload.productNumber is defined %}
商品番号：{{ lineItem.payload.productNumber|u.wordwrap(80) }}
{% endif %}
{% if nestedItem.cover is defined and nestedItem.cover is not null %}
画像：{{ lineItem.cover.alt }}
{% endif %}
商品説明：{{ lineItem.label|u.wordwrap(80) }}
{% if lineItem.payload.options is defined and lineItem.payload.options|length >= 1 %}
{% for option in lineItem.payload.options %}
{{ option.group }}：{{ option.option }}{{ ", " }}
{% endfor %}
{% endif %}
{% if lineItem.payload.features is defined and lineItem.payload.features|length >= 1 %}
{% set referencePriceFeatures = lineItem.payload.features|filter(feature => feature.type == \'referencePrice\') %}
{% if referencePriceFeatures|length >= 1 %}
{% set referencePriceFeature = referencePriceFeatures|first %}
{{ referencePriceFeature.value.purchaseUnit }} {{ referencePriceFeature.value.unitName }}({{ referencePriceFeature.value.price|currency(currencyIsoCode) }} / {{ referencePriceFeature.value.referenceUnit }} {{ referencePriceFeature.value.unitName }})
{% endif %}
{% endif %}
数量：{{ lineItem.quantity }}
単価：{{ lineItem.unitPrice|currency(currencyIsoCode) }}
小計：{{ lineItem.totalPrice|currency(currencyIsoCode) }}

{% endfor %}
{% set delivery = order.deliveries.first %}
{% set displayRounded = order.totalRounding.interval != 0.01 or order.totalRounding.decimals != order.itemRounding.decimals %}
{% set decimals = order.totalRounding.decimals %}
{% set total = order.price.totalPrice %}
{% if displayRounded %}
    {% set total = order.price.rawTotal %}
    {% set decimals = order.itemRounding.decimals %}
{% endif %}
{% for shippingCost in order.deliveries %}
送料：{{ shippingCost.shippingCosts.totalPrice|currency(currencyIsoCode) }}
{% endfor %}
商品合計（税抜）：{{ order.amountNet|currency(currencyIsoCode) }}
{% for calculatedTax in order.price.calculatedTaxes %}
{% if order.taxStatus is same as(\'net\') %}税込{% else %}税込{% endif %} {{ calculatedTax.taxRate }}% 消費税：{{ calculatedTax.tax|currency(currencyIsoCode) }}
{% endfor %}
合計金額（税込）：{{ total|currency(currencyIsoCode,decimals=decimals) }}
{% if displayRounded %}
端数調整後合計金額：{{ order.price.totalPrice|currency(currencyIsoCode,decimals=order.totalRounding.decimals) }}
{% endif %}

{% if order.transactions is defined and order.transactions is not empty %}
お支払い方法：{{ order.transactions.first.paymentMethod.translated.name }}
{{ order.transactions.first.paymentMethod.translated.description }}
{% endif %}

{% if delivery %}
配送方法：{{ delivery.shippingMethod.translated.name }}
{{ delivery.shippingMethod.translated.description }}
{% endif %}
{% set billingAddress = order.addresses.get(order.billingAddressId) %}
請求先住所：
{{ billingAddress.company }}
{{ billingAddress.lastName }} {{ billingAddress.firstName }}
{{ billingAddress.zipcode }} {{ billingAddress.city }}
{{ billingAddress.street }}
{{ billingAddress.country.translated.name }}

{% if delivery %}
配送先住所：
{{ delivery.shippingOrderAddress.company }}
{{ delivery.shippingOrderAddress.lastName }} {{ delivery.shippingOrderAddress.firstName }}
{{ delivery.shippingOrderAddress.zipcode}} {{ delivery.shippingOrderAddress.city }}
{{ delivery.shippingOrderAddress.street }}
{{ delivery.shippingOrderAddress.country.translated.name }}
{% endif %}

{% if order.orderCustomer.vatIds %}
VAT番号：{{ order.orderCustomer.vatIds|first }}
正常にご注文が完了し、EU諸国にお住まいの場合は、付加価値税を免除した商品をお受け取りいただけます。

{% endif %}
ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ご不明な点がございましたら、お気軽にお問い合わせください。

{% if a11yDocuments is defined and a11yDocuments is not empty %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
{% set documentLink = rawUrl(
    \'frontend.account.order.single.document.a11y\',
    {
        documentId: a11y.documentId,
        deepLinkCode: a11y.deepLinkCode,
        fileType: a11y.fileExtension,
    },
    salesChannel.domains|first.url
)%}
- {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。
ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getPaymentStateChargebackHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getPaymentStateChargebackPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。
新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getPaymentStateUnconfirmedHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <br/>
    <p>
        {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br/>
        <br/>
        {{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。<br/>
        <strong>新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}</strong><br/>
        <br/>
        ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
        </br>
        ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。
        <br><br>
        {% if a11yDocuments %}
            アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>

            {% for a11y in a11yDocuments %}
                {% set documentLink = rawUrl(
                    \'frontend.account.order.single.document.a11y\',
                    {
                        documentId: a11y.documentId,
                        deepLinkCode: a11y.deepLinkCode,
                        fileType: a11y.fileExtension,
                    },
                    salesChannel.domains|first.url
                )%}

                - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
            {% endfor %}<br>
            個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
            ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
        {% endif %}
    </p>
</div>
        ';
    }

    private function getPaymentStateUnconfirmedPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

{{ salesChannel.translated.name }} でのご注文（注文番号：{{ order.orderNumber }}、{{ order.orderDateTime|format_datetime(\'medium\', \'short\', locale=\'ja-JP\') }}）の支払い状況が変更されました。
新しい状況は以下の通りです：{{ order.transactions.first.stateMachineState.translated.name }}

ご注文の現在の状況は、当サイトの「マイアカウント」→「ご注文履歴」からいつでもご確認いただけます：{{ rawUrl(\'frontend.account.order.single.page\', { \'deepLinkCode\': order.deepLinkCode }, salesChannel.domains|first.url) }}
ただし、会員登録なしでご購入いただいた場合は、この機能をご利用いただけません。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getInvoiceHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">

    {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br>
    <br>
    ご注文番号：{{ order.orderNumber }} の請求書を添付いたします。<br>
    <br>
    ご不明な点がございましたら、お気軽にお問い合わせください。
    <br><br>

    {% if a11yDocuments %}
        アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>
        {% for a11y in a11yDocuments %}
            {% set documentLink = rawUrl(
                \'frontend.account.order.single.document.a11y\',
                {
                    documentId: a11y.documentId,
                    deepLinkCode: a11y.deepLinkCode,
                    fileType: a11y.fileExtension,
                },
                salesChannel.domains|first.url
            )%}

            - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
        {% endfor %}<br>

        個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
        ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
    {% endif %}
</div>
        ';
    }

    private function getInvoicePlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

ご注文番号：{{ order.orderNumber }} の請求書を添付いたします。

ご不明な点がございましたら、お気軽にお問い合わせください。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getDeliveryNoteHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">

    {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br>
    <br>
    ご注文番号：{{ order.orderNumber }} の納品書を添付いたします。<br>
    <br>
    ご不明な点がございましたら、お気軽にお問い合わせください。
    <br><br>

    {% if a11yDocuments %}
        アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>
        {% for a11y in a11yDocuments %}
            {% set documentLink = rawUrl(
                \'frontend.account.order.single.document.a11y\',
                {
                    documentId: a11y.documentId,
                    deepLinkCode: a11y.deepLinkCode,
                    fileType: a11y.fileExtension,
                },
                salesChannel.domains|first.url
            )%}

            - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
        {% endfor %}<br>

        個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
        ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
    {% endif %}
</div>
        ';
    }

    private function getDeliveryNotePlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

ご注文番号：{{ order.orderNumber }} の納品書を添付いたします。

ご不明な点がございましたら、お気軽にお問い合わせください。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getCreditNoteHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">

    {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br>
    <br>
    ご注文番号：{{ order.orderNumber }} の返金通知書を添付いたします。<br>
    <br>
    ご不明な点がございましたら、お気軽にお問い合わせください。
    <br><br>

    {% if a11yDocuments %}
        アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>
        {% for a11y in a11yDocuments %}
            {% set documentLink = rawUrl(
                \'frontend.account.order.single.document.a11y\',
                {
                    documentId: a11y.documentId,
                    deepLinkCode: a11y.deepLinkCode,
                    fileType: a11y.fileExtension,
                },
                salesChannel.domains|first.url
            )%}

            - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
        {% endfor %}<br>

        個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
        ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
    {% endif %}
</div>
        ';
    }

    private function getCreditNotePlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

ご注文番号：{{ order.orderNumber }} の返金通知書を添付いたします。

ご不明な点がございましたら、お気軽にお問い合わせください。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getCancellationInvoiceHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">

    {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br>
    <br>
    ご注文番号：{{ order.orderNumber }} のキャンセル請求書を添付いたします。<br>
    <br>
    ご不明な点がございましたら、お気軽にお問い合わせください。
    <br><br>

    {% if a11yDocuments %}
        アクセシビリティ向上のため、文書のHTML版もご利用いただけます：<br><br>
        {% for a11y in a11yDocuments %}
            {% set documentLink = rawUrl(
                \'frontend.account.order.single.document.a11y\',
                {
                    documentId: a11y.documentId,
                    deepLinkCode: a11y.deepLinkCode,
                    fileType: a11y.fileExtension,
                },
                salesChannel.domains|first.url
            )%}

            - <a href="{{ documentLink }}" target="_blank">{{ documentLink }}</a> <br>
        {% endfor %}<br>

        個人情報保護の観点から、HTML版の閲覧にはログインが必要です。<br><br>
        ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。<br>
    {% endif %}
</div>
        ';
    }

    private function getCancellationInvoicePlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

ご注文番号：{{ order.orderNumber }} のキャンセル請求書を添付いたします。

ご不明な点がございましたら、お気軽にお問い合わせください。

{% if a11yDocuments %}
アクセシビリティ向上のため、文書のHTML版もご利用いただけます：

{% for a11y in a11yDocuments %}
    {% set documentLink = rawUrl(
        \'frontend.account.order.single.document.a11y\',
        {
            documentId: a11y.documentId,
            deepLinkCode: a11y.deepLinkCode,
            fileType: a11y.fileExtension,
        },
        salesChannel.domains|first.url
    )%}

    - {{ documentLink }}
{% endfor %}

個人情報保護の観点から、HTML版の閲覧にはログインが必要です。

ゲスト注文の場合は、メールアドレスと請求先住所の郵便番号をご利用ください。
{% endif %}
        ';
    }

    private function getDoubleOptInGuestOrderHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <p>
        {{ customer.lastName }} 様<br/>
        <br/>
        以下のリンクからメールアドレスの確認をお願いします：<br/>
        <br/>
        <a href="{{ confirmUrl }}">メールアドレスを確認する</a><br/>
        <br/>
        確認後、チェックアウト画面にリダイレクトされ、ご注文内容を再度確認・完了していただけます。<br/>
        この確認により、契約履行の一環として今後メールをお送りすることにご同意いただいたものとします。
    </p>
</div>
        ';
    }

    private function getDoubleOptInGuestOrderPlainTemplate(): string
    {
        return '
{{ customer.lastName }} 様

以下のリンクからメールアドレスの確認をお願いします：

{{ confirmUrl }}

確認後、チェックアウト画面にリダイレクトされ、ご注文内容を再度確認・完了していただけます。
この確認により、契約履行の一環として今後メールをお送りすることにご同意いただいたものとします。
        ';
    }

    private function getDigitalProductDeliveryHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">

    {{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様<br>
    <br>
    ご注文番号：{{ order.orderNumber }} のデジタル商品ファイルを添付いたします。
    <br>
    <br>

    <table border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品番号</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>商品名</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>ダウンロード</strong></td>
        </tr>

        {% for lineItem in order.lineItems %}
            {% if lineItem.downloads is defined and lineItem.downloads|length %}
                {% block lineItem %}
                    <tr>
                        <td>{% if lineItem.payload.productNumber is defined %}{{ lineItem.payload.productNumber|u.wordwrap(80) }}{% endif %}</td>
                        <td>{{ lineItem.label|u.wordwrap(80) }}</td>
                        <td>
                            {% for download in lineItem.downloads %}
                                {% if download.accessGranted %}
                                    {% set downloadLink = rawUrl(\'frontend.account.order.single.download\', {\'orderId\': order.id, \'downloadId\': download.id, \'deepLinkCode\': order.deepLinkCode}, salesChannel.domains|first.url) %}
                                    <a href="{{ downloadLink }}" target="_blank">
                                        {{ download.media.fileName }}.{{ download.media.fileExtension }}
                                    </a><br>
                                {% endif %}
                            {% endfor %}
                        </td>
                    </tr>
                {% endblock %}
            {% endif %}
        {% endfor %}
    </table>

    <br>
</div>
        ';
    }

    private function getDigitalProductDeliveryPlainTemplate(): string
    {
        return '
{{ order.orderCustomer.lastName }} {{ order.orderCustomer.firstName }} 様

ご注文番号：{{ order.orderNumber }} のデジタル商品ファイルを添付いたします。

{% for lineItem in order.lineItems %}{% if lineItem.downloads is defined and lineItem.downloads|length %}
{{ lineItem.label|u.wordwrap(80) }} {% if lineItem.payload.productNumber is defined %}({{ lineItem.payload.productNumber|u.wordwrap(80) }}){% endif %}

-------------------------------------
{% for download in lineItem.downloads %}{% if download.accessGranted %}
{{ download.media.fileName }}.{{ download.media.fileExtension }} - {% set downloadLink = rawUrl(\'frontend.account.order.single.download\', {\'orderId\': order.id, \'downloadId\': download.id, \'deepLinkCode\': order.deepLinkCode}, salesChannel.domains|first.url) %}{{ downloadLink }}
{% endif %}{% endfor %}

{% endif %}{% endfor %}
        ';
    }

    private function getProductReviewHtmlTemplate(): string
    {
        return '
<div style="font-family:arial; font-size:12px;">
    <p>
        商品レビューが{% if reviewFormData.id is defined %}編集{% else %}送信{% endif %}されました。<br/>
        <br/>
        {% if reviewFormData.name is defined %}
            お名前: {% if reviewFormData.lastName is defined %}{{ reviewFormData.lastName }}{% endif %} {{ reviewFormData.name }}
            <br/>
        {% endif %}
        {% if reviewFormData.email is defined %}
            メールアドレス: {{ reviewFormData.email }}<br/>
            <br>
        {% endif %}
        {% if product.translated.name is defined %}
            商品名: {{ product.translated.name }}<br/>
            <br>
        {% endif %}
        評価: {{ reviewFormData.points }}<br/>
        <br/>
        タイトル: {{ reviewFormData.title }}<br/>
        <br/>
        内容:<br/>
        {{ reviewFormData.content|nl2br }}<br/>
    </p>
</div>
        ';
    }

    private function getProductReviewPlainTemplate(): string
    {
        return '
商品レビューが{% if reviewFormData.id is defined %}編集{% else %}送信{% endif %}されました。

{% if reviewFormData.name is defined %}お名前: {% if reviewFormData.lastName is defined %}{{ reviewFormData.lastName }}{% endif %} {{ reviewFormData.name }}{% endif %}

{% if reviewFormData.email is defined %}メールアドレス: {{ reviewFormData.email }}{% endif %}

{% if product.translated.name is defined %}商品名: {{ product.translated.name }}{% endif %}

評価: {{ reviewFormData.points }}

タイトル: {{ reviewFormData.title }}

内容:
{{ reviewFormData.content }}
        ';
    }
}