<?php


namespace App\Mappers;


use App\Database\Indexes\BaseIndex;
use App\Helpers\DateTimeHelper;
use App\Models\LocalizedText;
use App\Models\Month;

class MonthMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper,
        private LocalizedTextMapper $textMapper,
        private BaseIndex $baseIndex,
    ) {}

    public function mapItemToMonth(array $item): Month
    {
        $names = array_map(function(array $item) {
            return $this->textMapper->mapItemToLocalizedText($item);
        }, $item['names']);

        return new Month(
            id: $item[$this->baseIndex->getPartitionKey()],
            names: $names,
            seasonId: $item['seasonId'],
            createdAt: $this->dateTimeHelper->createFromString($item['createdAt']),
            updatedAt: $this->dateTimeHelper->createFromString($item['updatedAt']),
        );
    }

    public function mapRequestDataToMonth(array $data): Month
    {
        $names = array_map(function(array $nameData) {
            return new LocalizedText(
                $nameData['name'],
                $nameData['localeId'],
            );
        }, $data['names']);

        return new Month(
            $data['id'],
            $names,
            $data['seasonId']
        );
    }

    public function mapMonthToItem(Month $month): array
    {
        return [
            $this->baseIndex->getPartitionKey() => $month->getId(),
            $this->baseIndex->getSortKey() => Month::ENTITY_NAME,
            'seasonId' => $month->getSeasonId(),
            'createdAt' => $this->dateTimeHelper->convertToString($month->getCreatedAt()),
            'updatedAt' => $this->dateTimeHelper->convertToString($month->getUpdatedAt())
        ];
    }
}
