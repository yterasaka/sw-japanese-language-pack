<?php declare(strict_types=1);

namespace JapaneseLanguagePack\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;

/**
 * ステートマシンステータス（注文状態、配送状態、支払い状態等）の日本語翻訳を管理するサービス
 * 
 * 主な機能:
 * - 注文状態の日本語翻訳追加
 * - 配送状態の日本語翻訳追加  
 * - 支払い状態の日本語翻訳追加
 */
class JapaneseStateMachineStateService
{
    private EntityRepository $stateMachineStateRepository;
    private EntityRepository $languageRepository;

    public function __construct(
        EntityRepository $stateMachineStateRepository,
        EntityRepository $languageRepository
    ) {
        $this->stateMachineStateRepository = $stateMachineStateRepository;
        $this->languageRepository = $languageRepository;
    }

    public function addJapaneseStateTranslations(Context $context): void
    {
        $japaneseLanguageId = $this->getJapaneseLanguageId($context);
        if (!$japaneseLanguageId) {
            return;
        }

        $translations = $this->getJapaneseTranslations();
        
        foreach ($translations as $technicalName => $japaneseName) {
            $this->addTranslationForState($technicalName, $japaneseName, $japaneseLanguageId, $context);
        }
    }

    private function getJapaneseLanguageId(Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('locale.code', 'ja-JP'));
        $criteria->addAssociation('locale');

        $languages = $this->languageRepository->search($criteria, $context);
        
        return $languages->first()?->getId();
    }

    private function getJapaneseTranslations(): array
    {
        return [
            // Order states
            'open' => '受注',
            'completed' => '完了',
            'in_progress' => '処理中',
            'cancelled' => 'キャンセル',
            
            // Delivery states
            'shipped' => '発送済み',
            'shipped_partially' => '一部発送',
            'returned' => '返品',
            'returned_partially' => '一部返品',
            
            // Payment states
            'paid' => '支払い済み',
            'paid_partially' => '一部支払い',
            'reminded' => '督促',
            'refunded' => '返金済み',
            'refunded_partially' => '一部返金',
            'failed' => '失敗',
            'authorized' => '承認済み',
            'chargeback' => 'チャージバック',
            'unconfirmed' => '未確認',
            
            // Import/Export states
            'pending' => '保留中',
            'complete' => '完了済み',
        ];
    }

    private function addTranslationForState(
        string $technicalName,
        string $japaneseName,
        string $languageId,
        Context $context
    ): void {
        try {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('technicalName', $technicalName));
            $criteria->addAssociation('translations');

            $states = $this->stateMachineStateRepository->search($criteria, $context);

            foreach ($states->getElements() as $state) {
                /** @var StateMachineStateEntity $state */

                $this->stateMachineStateRepository->upsert([
                    [
                        'id' => $state->getId(),
                        'translations' => [
                            $languageId => [
                                'name' => $japaneseName,
                            ],
                        ],
                    ],
                ], $context);
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'JapaneseLanguagePack: Failed to add translation for state "%s": %s',
                $technicalName,
                $e->getMessage()
            ));
        }
    }
}