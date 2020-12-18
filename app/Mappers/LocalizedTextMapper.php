<?php


namespace App\Mappers;


use App\Database\Indexes\BaseIndex;
use App\Helpers\DateTimeHelper;
use App\Models\LocalizedText;

class LocalizedTextMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper,
        private BaseIndex $baseIndex,
    ) {}

    public function mapItemToLocalizedText(array $item): LocalizedText
    {
        $sortKeyValues = explode('#', $item[$this->baseIndex->getSortKey()]);

        return new LocalizedText(
            name: $item['name'],
            localeId: $sortKeyValues[1],
            createdAt: $this->dateTimeHelper->createFromString($item['createdAt']),
            updatedAt: $this->dateTimeHelper->createFromString($item['updatedAt']),
        );
    }

    public function mapLocalizedTextToItem(LocalizedText $text, string $partitionKey, string $attribute): array
    {
        return [
            $this->baseIndex->getPartitionKey() => $partitionKey,
            $this->baseIndex->getSortKey() => LocalizedText::ENTITY_NAME . '#' . $text->getLocaleId() . '#' . $attribute,
            'name' => $text->getName(),
            'createdAt' => $this->dateTimeHelper->convertToString($text->getCreatedAt()),
            'updatedAt' => $this->dateTimeHelper->convertToString($text->getUpdatedAt()),
        ];
    }
}
