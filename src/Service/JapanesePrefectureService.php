<?php declare(strict_types=1);

namespace JapaneseLanguagePack\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class JapanesePrefectureService
{
    private EntityRepository $countryRepository;
    private EntityRepository $countryStateRepository;
    private EntityRepository $languageRepository;

    public function __construct(
        EntityRepository $countryRepository,
        EntityRepository $countryStateRepository,
        EntityRepository $languageRepository
    ) {
        $this->countryRepository = $countryRepository;
        $this->countryStateRepository = $countryStateRepository;
        $this->languageRepository = $languageRepository;
    }

    public function createJapanesePrefectures(Context $context): void
    {
        $japan = $this->findJapan($context);

        if (!$japan) {
            $japan = $this->createJapanCountry($context);
            if (!$japan) {
                return;
            }
        }

        if ($this->prefecturesAlreadyExist($context, $japan->getId())) {
            return;
        }

        $this->createPrefecturesData($context, $japan->getId());
    }

    private function createJapanCountry(Context $context): ?object
    {
        try {
            $this->countryRepository->create([
                [
                    'iso' => 'JP',
                    'iso3' => 'JPN',
                    'name' => 'Japan',
                    'position' => 1,
                    'active' => true,
                    'shippingAvailable' => true,
                    'taxFree' => false,
                    'forceStateInRegistration' => true,
                    'displayStateInRegistration' => true,
                    'postalCodeRequired' => true,
                    'checkPostalCodePattern' => true,
                    'defaultPostalCodePattern' => '^[0-9]{3}-[0-9]{4}$'
                ]
            ], $context);
            return $this->findJapan($context);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function removeJapanesePrefectures(Context $context): void
    {
        $japan = $this->findJapan($context);

        if (!$japan) {
            return;
        }

        $this->deletePrefecturesData($context, $japan->getId());
    }

    private function findJapan(Context $context): ?object
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('iso', 'JP'));
        
        return $this->countryRepository->search($criteria, $context)->first();
    }

    private function prefecturesAlreadyExist(Context $context, string $countryId): bool
    {
        $stateCriteria = new Criteria();
        $stateCriteria->addFilter(new EqualsFilter('countryId', $countryId));
        
        $existingStates = $this->countryStateRepository->search($stateCriteria, $context);

        return $existingStates->getTotal() > 0;
    }

    private function createPrefecturesData(Context $context, string $countryId): void
    {
        $prefectures = $this->getPrefecturesData();
        
        $stateData = [];
        foreach ($prefectures as $prefecture) {
            $stateData[] = [
                'countryId' => $countryId,
                'shortCode' => $prefecture['shortCode'],
                'name' => $prefecture['nameEn'],
                'position' => 1,
                'active' => true,
                'translations' => [
                    $this->getLanguageId($context, 'en-GB') => [
                        'name' => $prefecture['nameEn']
                    ],
                    $this->getLanguageId($context, 'ja-JP') => [
                        'name' => $prefecture['nameJa']
                    ]
                ]
            ];
        }

        $this->countryStateRepository->create($stateData, $context);
    }

    private function getLanguageId(Context $context, string $locale): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('locale.code', $locale));
        $criteria->addAssociation('locale');
        
        $language = $this->languageRepository->search($criteria, $context)->first();
        
        return $language ? $language->getId() : null;
    }

    private function deletePrefecturesData(Context $context, string $countryId): void
    {
        $stateCriteria = new Criteria();
        $stateCriteria->addFilter(new EqualsFilter('countryId', $countryId));
        
        $states = $this->countryStateRepository->search($stateCriteria, $context);

        if ($states->getTotal() === 0) {
            return;
        }

        $stateIds = [];
        foreach ($states as $state) {
            $stateIds[] = ['id' => $state->getId()];
        }

        $this->countryStateRepository->delete($stateIds, $context);
    }

    private function getPrefecturesData(): array
    {
        return [
            ['shortCode' => 'JP-01', 'nameEn' => 'Hokkaido', 'nameJa' => '北海道'],
            ['shortCode' => 'JP-02', 'nameEn' => 'Aomori', 'nameJa' => '青森県'],
            ['shortCode' => 'JP-03', 'nameEn' => 'Iwate', 'nameJa' => '岩手県'],
            ['shortCode' => 'JP-04', 'nameEn' => 'Miyagi', 'nameJa' => '宮城県'],
            ['shortCode' => 'JP-05', 'nameEn' => 'Akita', 'nameJa' => '秋田県'],
            ['shortCode' => 'JP-06', 'nameEn' => 'Yamagata', 'nameJa' => '山形県'],
            ['shortCode' => 'JP-07', 'nameEn' => 'Fukushima', 'nameJa' => '福島県'],
            ['shortCode' => 'JP-08', 'nameEn' => 'Ibaraki', 'nameJa' => '茨城県'],
            ['shortCode' => 'JP-09', 'nameEn' => 'Tochigi', 'nameJa' => '栃木県'],
            ['shortCode' => 'JP-10', 'nameEn' => 'Gunma', 'nameJa' => '群馬県'],
            ['shortCode' => 'JP-11', 'nameEn' => 'Saitama', 'nameJa' => '埼玉県'],
            ['shortCode' => 'JP-12', 'nameEn' => 'Chiba', 'nameJa' => '千葉県'],
            ['shortCode' => 'JP-13', 'nameEn' => 'Tokyo', 'nameJa' => '東京都'],
            ['shortCode' => 'JP-14', 'nameEn' => 'Kanagawa', 'nameJa' => '神奈川県'],
            ['shortCode' => 'JP-15', 'nameEn' => 'Niigata', 'nameJa' => '新潟県'],
            ['shortCode' => 'JP-16', 'nameEn' => 'Toyama', 'nameJa' => '富山県'],
            ['shortCode' => 'JP-17', 'nameEn' => 'Ishikawa', 'nameJa' => '石川県'],
            ['shortCode' => 'JP-18', 'nameEn' => 'Fukui', 'nameJa' => '福井県'],
            ['shortCode' => 'JP-19', 'nameEn' => 'Yamanashi', 'nameJa' => '山梨県'],
            ['shortCode' => 'JP-20', 'nameEn' => 'Nagano', 'nameJa' => '長野県'],
            ['shortCode' => 'JP-21', 'nameEn' => 'Gifu', 'nameJa' => '岐阜県'],
            ['shortCode' => 'JP-22', 'nameEn' => 'Shizuoka', 'nameJa' => '静岡県'],
            ['shortCode' => 'JP-23', 'nameEn' => 'Aichi', 'nameJa' => '愛知県'],
            ['shortCode' => 'JP-24', 'nameEn' => 'Mie', 'nameJa' => '三重県'],
            ['shortCode' => 'JP-25', 'nameEn' => 'Shiga', 'nameJa' => '滋賀県'],
            ['shortCode' => 'JP-26', 'nameEn' => 'Kyoto', 'nameJa' => '京都府'],
            ['shortCode' => 'JP-27', 'nameEn' => 'Osaka', 'nameJa' => '大阪府'],
            ['shortCode' => 'JP-28', 'nameEn' => 'Hyogo', 'nameJa' => '兵庫県'],
            ['shortCode' => 'JP-29', 'nameEn' => 'Nara', 'nameJa' => '奈良県'],
            ['shortCode' => 'JP-30', 'nameEn' => 'Wakayama', 'nameJa' => '和歌山県'],
            ['shortCode' => 'JP-31', 'nameEn' => 'Tottori', 'nameJa' => '鳥取県'],
            ['shortCode' => 'JP-32', 'nameEn' => 'Shimane', 'nameJa' => '島根県'],
            ['shortCode' => 'JP-33', 'nameEn' => 'Okayama', 'nameJa' => '岡山県'],
            ['shortCode' => 'JP-34', 'nameEn' => 'Hiroshima', 'nameJa' => '広島県'],
            ['shortCode' => 'JP-35', 'nameEn' => 'Yamaguchi', 'nameJa' => '山口県'],
            ['shortCode' => 'JP-36', 'nameEn' => 'Tokushima', 'nameJa' => '徳島県'],
            ['shortCode' => 'JP-37', 'nameEn' => 'Kagawa', 'nameJa' => '香川県'],
            ['shortCode' => 'JP-38', 'nameEn' => 'Ehime', 'nameJa' => '愛媛県'],
            ['shortCode' => 'JP-39', 'nameEn' => 'Kochi', 'nameJa' => '高知県'],
            ['shortCode' => 'JP-40', 'nameEn' => 'Fukuoka', 'nameJa' => '福岡県'],
            ['shortCode' => 'JP-41', 'nameEn' => 'Saga', 'nameJa' => '佐賀県'],
            ['shortCode' => 'JP-42', 'nameEn' => 'Nagasaki', 'nameJa' => '長崎県'],
            ['shortCode' => 'JP-43', 'nameEn' => 'Kumamoto', 'nameJa' => '熊本県'],
            ['shortCode' => 'JP-44', 'nameEn' => 'Oita', 'nameJa' => '大分県'],
            ['shortCode' => 'JP-45', 'nameEn' => 'Miyazaki', 'nameJa' => '宮崎県'],
            ['shortCode' => 'JP-46', 'nameEn' => 'Kagoshima', 'nameJa' => '鹿児島県'],
            ['shortCode' => 'JP-47', 'nameEn' => 'Okinawa', 'nameJa' => '沖縄県'],
        ];
    }
}