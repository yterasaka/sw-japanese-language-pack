<?php declare(strict_types=1);

namespace JapaneseLanguagePack\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

/**
 * Service for managing Japanese language and locale configuration
 * 日本語言語とロケール設定を管理するサービス
 */
class JapaneseLanguageService
{
    private EntityRepository $localeRepository;
    private EntityRepository $languageRepository;
    private EntityRepository $snippetSetRepository;

    public function __construct(
        EntityRepository $localeRepository,
        EntityRepository $languageRepository,
        EntityRepository $snippetSetRepository
    ) {
        $this->localeRepository = $localeRepository;
        $this->languageRepository = $languageRepository;
        $this->snippetSetRepository = $snippetSetRepository;
    }

    public function createJapaneseLanguage(Context $context): void
    {
        $locale = $this->createLocale($context);
        
        if ($locale) {
            $language = $this->createLanguage($context, $locale->getId());
            
            if ($language) {
                $this->createSnippetSet($context);
            }
        }
    }

    public function removeJapaneseLanguage(Context $context): void
    {
        $this->removeSnippetSet($context);
        $this->removeLanguage($context);
        $this->removeLocale($context);
    }

    private function createLocale(Context $context): ?object
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', 'ja-JP'));
        
        $locale = $this->localeRepository->search($criteria, $context)->first();

        if (!$locale) {
            $this->localeRepository->create([
                [
                    'code' => 'ja-JP',
                    'name' => '日本語',
                    'territory' => 'Japan'
                ]
            ], $context);

            $locale = $this->localeRepository->search($criteria, $context)->first();
        }

        return $locale;
    }

    private function createLanguage(Context $context, string $localeId): ?object
    {
        $languageCriteria = new Criteria();
        $languageCriteria->addFilter(new EqualsFilter('locale.code', 'ja-JP'));
        
        $language = $this->languageRepository->search($languageCriteria, $context)->first();

        if (!$language) {
            $this->languageRepository->create([
                [
                    'name' => '日本語',
                    'localeId' => $localeId,
                    'translationCodeId' => $localeId
                ]
            ], $context);

            $language = $this->languageRepository->search($languageCriteria, $context)->first();
        }

        return $language;
    }

    private function createSnippetSet(Context $context): void
    {
        $snippetSetCriteria = new Criteria();
        $snippetSetCriteria->addFilter(new EqualsFilter('iso', 'ja-JP'));
        
        $snippetSet = $this->snippetSetRepository->search($snippetSetCriteria, $context)->first();

        if (!$snippetSet) {
            $this->snippetSetRepository->create([
                [
                    'name' => 'BASE ja-JP',
                    'baseFile' => 'messages.ja-JP',
                    'iso' => 'ja-JP'
                ]
            ], $context);
        }
    }

    private function removeSnippetSet(Context $context): void
    {
        $snippetSetCriteria = new Criteria();
        $snippetSetCriteria->addFilter(new EqualsFilter('iso', 'ja-JP'));
        
        $snippetSet = $this->snippetSetRepository->search($snippetSetCriteria, $context)->first();

        if ($snippetSet) {
            $this->snippetSetRepository->delete([
                ['id' => $snippetSet->getId()]
            ], $context);
        }
    }

    private function removeLanguage(Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('locale.code', 'ja-JP'));
        
        $language = $this->languageRepository->search($criteria, $context)->first();

        if ($language) {
            $this->languageRepository->delete([
                ['id' => $language->getId()]
            ], $context);
        }
    }

    private function removeLocale(Context $context): void
    {
        $localeCriteria = new Criteria();
        $localeCriteria->addFilter(new EqualsFilter('code', 'ja-JP'));
        
        $locale = $this->localeRepository->search($localeCriteria, $context)->first();

        if ($locale) {
            $this->localeRepository->delete([
                ['id' => $locale->getId()]
            ], $context);
        }
    }
}