<?php


namespace App\Mappers;


use App\Helpers\DateTimeHelper;
use App\Models\LocalizedText;
use JetBrains\PhpStorm\ArrayShape;

class LocalizedTextMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper
    ) {}

    public function mapItemToLocalizedText(array $item): LocalizedText
    {
        $sortKeyValues = explode('#', $item['sk']);

        return new LocalizedText(
            name: $item['name'],
            localeId: $sortKeyValues[1],
            createdAt: $this->dateTimeHelper->createFromString($item['createdAt']),
            updatedAt: $this->dateTimeHelper->createFromString($item['updatedAt']),
        );
    }

    #[ArrayShape(['pk' => "string", 'sk' => "string", 'name' => "string", 'createdAt' => "string", 'updatedAt' => "string"])]
    public function mapLocalizedTextToItem(LocalizedText $text, string $partitionKey, string $attribute): array
    {
        return [
            'pk' => $partitionKey,
            'sk' => LocalizedText::ENTITY_NAME . '#' . $text->getLocaleId() . '#' . $attribute,
            'name' => $text->getName(),
            'createdAt' => $this->dateTimeHelper->convertToString($text->getCreatedAt()),
            'updatedAt' => $this->dateTimeHelper->convertToString($text->getUpdatedAt()),
        ];
    }
}
