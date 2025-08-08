<?php declare(strict_types=1);

namespace JapaneseLanguagePack\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class JapaneseProductSortingService
{
    private EntityRepository $languageRepository;
    private Connection $connection;

    public function __construct(
        EntityRepository $languageRepository,
        Connection $connection
    ) {
        $this->languageRepository = $languageRepository;
        $this->connection = $connection;
    }

    public function updateProductSortingTranslations(Context $context): void
    {
        $jaLanguageId = $this->getLanguageId($context, 'ja-JP');
        
        if (!$jaLanguageId) {
            return;
        }

        $sortingTranslations = $this->getSortingTranslations();
        
        foreach ($sortingTranslations as $urlKey => $translation) {
            $this->insertSortingTranslationIfNotExists($urlKey, $translation, $jaLanguageId);
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

    private function insertSortingTranslationIfNotExists(string $urlKey, string $translation, string $languageId): void
    {
        $sortingId = $this->connection->fetchOne(
            'SELECT id FROM product_sorting WHERE url_key = :urlKey',
            ['urlKey' => $urlKey]
        );

        if (!$sortingId) {
            return;
        }

        $existingTranslation = $this->connection->fetchOne(
            'SELECT label FROM product_sorting_translation WHERE product_sorting_id = :sortingId AND language_id = :languageId',
            [
                'sortingId' => $sortingId,
                'languageId' => Uuid::fromHexToBytes($languageId)
            ]
        );

        if ($existingTranslation) {
            return;
        }

        $now = (new \DateTime())->format('Y-m-d H:i:s.v');
        
        $this->connection->executeStatement(
            'INSERT INTO product_sorting_translation (product_sorting_id, language_id, label, created_at) VALUES (:sortingId, :languageId, :label, :createdAt)',
            [
                'sortingId' => $sortingId,
                'languageId' => Uuid::fromHexToBytes($languageId),
                'label' => $translation,
                'createdAt' => $now
            ]
        );
    }

    private function getSortingTranslations(): array
    {
        return [
            'name-asc' => '名前順（A-Z）',
            'name-desc' => '名前順（Z-A）',
            'price-asc' => '価格順（安い順）',
            'price-desc' => '価格順（高い順）',
            'score' => '関連度順',
            'topseller' => '人気順'
        ];
    }
}