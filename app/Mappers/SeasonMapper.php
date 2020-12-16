<?php


namespace App\Mappers;


use App\Helpers\DateTimeHelper;
use App\Models\LocalizedText;
use App\Models\Season;
use JetBrains\PhpStorm\ArrayShape;

class SeasonMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper,
        private LocalizedTextMapper $textMapper
    ) {}

    public function mapItemToSeason(array $item): Season
    {
        $names = array_map(function(array $item) {
            return $this->textMapper->mapItemToLocalizedText($item);
        }, $item['names']);

        return new Season(
            id: $item['pk'],
            names: $names,
            createdAt: $this->dateTimeHelper->createFromString($item['createdAt']),
            updatedAt: $this->dateTimeHelper->createFromString($item['updatedAt']),
        );
    }

    public function mapRequestDataToSeason(array $data): Season
    {
        $names = array_map(function(array $nameData) {
            return new LocalizedText(
                $nameData['name'],
                $nameData['localeId'],
            );
        }, $data['names']);

        return new Season(
            $data['id'],
            $names
        );
    }

    #[ArrayShape(['pk' => "string", 'sk' => "string", 'createdAt' => "string", 'updatedAt' => "string"])]
    public function mapSeasonToItem(Season $season): array
    {
        return [
            'pk' => $season->getId(),
            'sk' => Season::ENTITY_NAME,
            'createdAt' => $this->dateTimeHelper->convertToString($season->getCreatedAt()),
            'updatedAt' => $this->dateTimeHelper->convertToString($season->getUpdatedAt())
        ];
    }
}
