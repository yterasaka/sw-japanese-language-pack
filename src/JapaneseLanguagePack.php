<?php declare(strict_types=1);

namespace JapaneseLanguagePack;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class JapaneseLanguagePack extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        $this->createJapaneseLanguage($installContext->getContext());
        $this->createJapaneseCurrency($installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->removeJapaneseLanguage($uninstallContext->getContext());
        $this->removeJapaneseCurrency($uninstallContext->getContext());
    }

    public function activate(ActivateContext $activateContext): void
    {
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
    }

    public function update(UpdateContext $updateContext): void
    {
        $this->createJapaneseLanguage($updateContext->getContext());
        $this->createJapaneseCurrency($updateContext->getContext());
    }

    public function postInstall(InstallContext $installContext): void
    {
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
    }

    // 言語とスニペット
    private function createJapaneseLanguage(Context $context): void
    {
        $localeRepository = $this->container->get('locale.repository');
        $languageRepository = $this->container->get('language.repository');
        $snippetSetRepository = $this->container->get('snippet_set.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', 'ja-JP'));
        
        $locale = $localeRepository->search($criteria, $context)->first();

        if (!$locale) {
            $localeRepository->create([
                [
                    'code' => 'ja-JP',
                    'name' => '日本語',
                    'territory' => 'Japan'
                ]
            ], $context);

            $locale = $localeRepository->search($criteria, $context)->first();
        }

        if ($locale) {
            $languageCriteria = new Criteria();
            $languageCriteria->addFilter(new EqualsFilter('locale.code', 'ja-JP'));
            
            $language = $languageRepository->search($languageCriteria, $context)->first();

            if (!$language) {
                $languageRepository->create([
                    [
                        'name' => '日本語',
                        'localeId' => $locale->getId(),
                        'translationCodeId' => $locale->getId()
                    ]
                ], $context);

                $language = $languageRepository->search($languageCriteria, $context)->first();
            }

            if ($language) {
                $snippetSetCriteria = new Criteria();
                $snippetSetCriteria->addFilter(new EqualsFilter('iso', 'ja-JP'));
                
                $snippetSet = $snippetSetRepository->search($snippetSetCriteria, $context)->first();

                if (!$snippetSet) {
                    $snippetSetRepository->create([
                        [
                            'name' => 'BASE ja-JP',
                            'baseFile' => 'messages.ja-JP',
                            'iso' => 'ja-JP'
                        ]
                    ], $context);
                }
            }
        }
    }

    private function removeJapaneseLanguage(Context $context): void
    {
        $languageRepository = $this->container->get('language.repository');
        $snippetSetRepository = $this->container->get('snippet_set.repository');

        $snippetSetCriteria = new Criteria();
        $snippetSetCriteria->addFilter(new EqualsFilter('iso', 'ja-JP'));
        
        $snippetSet = $snippetSetRepository->search($snippetSetCriteria, $context)->first();

        if ($snippetSet) {
            $snippetSetRepository->delete([
                ['id' => $snippetSet->getId()]
            ], $context);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('locale.code', 'ja-JP'));
        
        $language = $languageRepository->search($criteria, $context)->first();

        if ($language) {
            $languageRepository->delete([
                ['id' => $language->getId()]
            ], $context);
        }

        $localeRepository = $this->container->get('locale.repository');

        $localeCriteria = new Criteria();
        $localeCriteria->addFilter(new EqualsFilter('code', 'ja-JP'));
        
        $locale = $localeRepository->search($localeCriteria, $context)->first();

        if ($locale) {
            $localeRepository->delete([
                ['id' => $locale->getId()]
            ], $context);
        }
    }

    // 通貨
    private function createJapaneseCurrency(Context $context): void
    {
        $currencyRepository = $this->container->get('currency.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('isoCode', 'JPY'));
        
        $currency = $currencyRepository->search($criteria, $context)->first();

        if (!$currency) {
            $currencyRepository->create([
            [
                'isoCode' => 'JPY',
                'name' => '日本円',
                'symbol' => '¥',
                'factor' => 1.0,
                'decimalPrecision' => 0,
                'shortName' => 'JPY',
                'position' => 1,
                'itemRounding' => [
                    'decimals' => 0,
                    'interval' => 1.0,
                    'roundForNet' => true
                ],
                'totalRounding' => [
                    'decimals' => 0,
                    'interval' => 1.0,
                    'roundForNet' => true
                ]
            ]
        ], $context);
        }
    }

    private function removeJapaneseCurrency(Context $context): void
    {
        $currencyRepository = $this->container->get('currency.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('isoCode', 'JPY'));
        
        $currency = $currencyRepository->search($criteria, $context)->first();

        if ($currency) {
            $currencyRepository->delete([
                ['id' => $currency->getId()]
            ], $context);
        }
    }
}
