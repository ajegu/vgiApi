<?php


namespace App\Mappers;


use App\Helpers\DateTimeHelper;
use App\Models\Locale;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class LocaleMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper
    ) {}

    public function mapItemToLocale(array $item): Locale
    {
        return new Locale(
            id: $item['pk'],
            name: $item['name'],
            createdAt: $this->dateTimeHelper->createFromString($item['createdAt']),
            updatedAt: $this->dateTimeHelper->createFromString($item['updatedAt']),
        );
    }

    #[ArrayShape(['pk' => "string", 'sk' => "string", 'name' => "string"])]
    public function mapLocaleToItem(Locale $locale): array
    {
        return [
            'pk' => $locale->getId(),
            'sk' => Locale::ENTITY_NAME,
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
