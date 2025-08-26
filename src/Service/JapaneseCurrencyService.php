<?php declare(strict_types=1);

namespace JapaneseLanguagePack\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

/**
 * Service for managing Japanese currency (JPY) configuration
 * 日本円（JPY）通貨の設定を管理するサービス
 */
class JapaneseCurrencyService
{
    private EntityRepository $currencyRepository;

    public function __construct(EntityRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function createJapaneseCurrency(Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('isoCode', 'JPY'));
        
        $currency = $this->currencyRepository->search($criteria, $context)->first();

        if (!$currency) {
            $this->currencyRepository->create([
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
                        'interval' => 0.01,
                        'roundForNet' => false
                    ],
                    'totalRounding' => [
                        'decimals' => 0,
                        'interval' => 0.01,
                        'roundForNet' => false
                    ]
                ]
            ], $context);
        }
    }

    public function removeJapaneseCurrency(Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('isoCode', 'JPY'));
        
        $currency = $this->currencyRepository->search($criteria, $context)->first();

        if ($currency) {
            $this->currencyRepository->delete([
                ['id' => $currency->getId()]
            ], $context);
        }
    }
}