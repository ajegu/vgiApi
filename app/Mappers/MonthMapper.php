<?php


namespace App\Mappers;


use App\Helpers\DateTimeHelper;
use App\Models\LocalizedText;
use App\Models\Month;
use JetBrains\PhpStorm\ArrayShape;

class MonthMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper,
        private LocalizedTextMapper $textMapper
    ) {}

    public function mapItemToMonth(array $item): Month
    {
        $names = array_map(function(array $item) {
            return $this->textMapper->mapItemToLocalizedText($item);
        }, $item['names']);

        return new Month(
            id: $item['pk'],
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

    #[ArrayShape(['pk' => "string", 'sk' => "string", 'createdAt' => "string", 'updatedAt' => "string"])]
    public function mapMonthToItem(Month $month): array
    {
        return [
            'pk' => $month->getId(),
            'sk' => Month::ENTITY_NAME,
            'seasonId' => $month->getSeasonId(),
            'createdAt' => $this->dateTimeHelper->convertToString($month->getCreatedAt()),
            'updatedAt' => $this->dateTimeHelper->convertToString($month->getUpdatedAt())
        ];
    }
}
