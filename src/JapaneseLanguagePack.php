<?php declare(strict_types=1);

namespace JapaneseLanguagePack;

use JapaneseLanguagePack\Service\JapaneseCurrencyService;
use JapaneseLanguagePack\Service\JapaneseLanguageService;
use JapaneseLanguagePack\Service\JapanesePrefectureService;
use JapaneseLanguagePack\Service\JapaneseProductSortingService;
use JapaneseLanguagePack\Service\JapaneseMailTemplateService;
use JapaneseLanguagePack\Service\JapaneseStateMachineStateService;
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
        $this->getLanguageService()->createJapaneseLanguage($installContext->getContext());
        $this->getCurrencyService()->createJapaneseCurrency($installContext->getContext());
        $this->getPrefectureService()->createJapanesePrefectures($installContext->getContext());
        $this->getProductSortingService()->updateProductSortingTranslations($installContext->getContext());
        $this->getMailTemplateService()->createJapaneseMailTemplateTranslations($installContext->getContext());
        $this->getStateMachineStateService()->addJapaneseStateTranslations($installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }
    }

    public function activate(ActivateContext $activateContext): void
    {
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
    }

    public function update(UpdateContext $updateContext): void
    {
        $this->getLanguageService()->createJapaneseLanguage($updateContext->getContext());
        $this->getCurrencyService()->createJapaneseCurrency($updateContext->getContext());
        $this->getPrefectureService()->createJapanesePrefectures($updateContext->getContext());
        $this->getProductSortingService()->updateProductSortingTranslations($updateContext->getContext());
        $this->getMailTemplateService()->createJapaneseMailTemplateTranslations($updateContext->getContext());
        $this->getStateMachineStateService()->addJapaneseStateTranslations($updateContext->getContext());
    }

    public function postInstall(InstallContext $installContext): void
    {
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
    }

    private function getLanguageService(): JapaneseLanguageService
    {
        try {
            return $this->container->get(JapaneseLanguageService::class);
        } catch (\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $e) {
            return new JapaneseLanguageService(
                $this->container->get('locale.repository'),
                $this->container->get('language.repository'),
                $this->container->get('snippet_set.repository')
            );
        }
    }

    private function getCurrencyService(): JapaneseCurrencyService
    {
        try {
            return $this->container->get(JapaneseCurrencyService::class);
        } catch (\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $e) {
            return new JapaneseCurrencyService(
                $this->container->get('currency.repository')
            );
        }
    }

    private function getPrefectureService(): JapanesePrefectureService
    {
        try {
            return $this->container->get(JapanesePrefectureService::class);
        } catch (\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $e) {
            return new JapanesePrefectureService(
                $this->container->get('country.repository'),
                $this->container->get('country_state.repository'),
                $this->container->get('language.repository'),
            );
        }
    }

    private function getProductSortingService(): JapaneseProductSortingService
    {
        try {
            return $this->container->get(JapaneseProductSortingService::class);
        } catch (\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $e) {
            return new JapaneseProductSortingService(
                $this->container->get('language.repository'),
                $this->container->get(\Doctrine\DBAL\Connection::class)
            );
        }
    }

    private function getMailTemplateService(): JapaneseMailTemplateService
    {
        try {
            return $this->container->get(JapaneseMailTemplateService::class);
        } catch (\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $e) {
            return new JapaneseMailTemplateService(
                $this->container->get('mail_template.repository'),
                $this->container->get('language.repository'),
            );
        }
    }

    private function getStateMachineStateService(): JapaneseStateMachineStateService
    {
        try {
            return $this->container->get(JapaneseStateMachineStateService::class);
        } catch (\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $e) {
            return new JapaneseStateMachineStateService(
                $this->container->get('state_machine_state.repository'),
                $this->container->get('language.repository')
            );
        }
    }
}
