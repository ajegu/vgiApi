<?php


namespace App\Mappers;


use App\Database\Indexes\BaseIndex;
use App\Helpers\DateTimeHelper;
use App\Models\Season;

class SeasonMapper extends AbstractMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper,
        private LocalizedTextMapper $textMapper,
        private BaseIndex $baseIndex,
    ) {}

    public function mapItemToSeason(array $item): Season
    {
        $names = array_map(function(array $item) {
            return $this->textMapper->mapItemToLocalizedText($item);
        }, $item['names']);

        return new Season(
            id: $item[$this->baseIndex->getPartitionKey()],
            names: $names,
            createdAt: $this->dateTimeHelper->createFromString($item['createdAt']),
            updatedAt: $this->dateTimeHelper->createFromString($item['updatedAt']),
        );
    }

    public function mapRequestDataToSeason(array $data): Season
    {
        $names = $this->mapRequestDataToLocalizedText($data['names']);

        return new Season(
            $data['id'],
            $names
        );
    }

    public function mapSeasonToItem(Season $season): array
    {
        return [
            $this->baseIndex->getPartitionKey() => $season->getId(),
            $this->baseIndex->getSortKey() => Season::ENTITY_NAME,
            'createdAt' => $this->dateTimeHelper->convertToString($season->getCreatedAt()),
            'updatedAt' => $this->dateTimeHelper->convertToString($season->getUpdatedAt())
        ];
    }
}
