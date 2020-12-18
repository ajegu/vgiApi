<?php


namespace App\Mappers;


use App\Database\Indexes\BaseIndex;
use App\Helpers\DateTimeHelper;
use App\Models\Locale;
use JetBrains\PhpStorm\Pure;

class LocaleMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper,
        private BaseIndex $baseIndex,
    ) {}

    public function mapItemToLocale(array $item): Locale
    {
        return new Locale(
            id: $item[$this->baseIndex->getPartitionKey()],
            name: $item['name'],
            createdAt: $this->dateTimeHelper->createFromString($item['createdAt']),
            updatedAt: $this->dateTimeHelper->createFromString($item['updatedAt']),
        );
    }

    public function mapLocaleToItem(Locale $locale): array
    {
        return [
            $this->baseIndex->getPartitionKey() => $locale->getId(),
            $this->baseIndex->getSortKey() => Locale::ENTITY_NAME,
            'name' => $locale->getName(),
            'createdAt' => $this->dateTimeHelper->convertToString($locale->getCreatedAt()),
            'updatedAt' => $this->dateTimeHelper->convertToString($locale->getUpdatedAt()),
        ];
    }

    #[Pure]
    public function mapRequestDataToLocale(array $data): Locale
    {
        return new Locale(
            id: $data['id'],
            name: $data['name'],
        );
    }
}
