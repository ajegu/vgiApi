<?php


namespace App\Mappers;


use App\Database\Indexes\BaseIndex;
use App\Database\Indexes\ParentIndex;
use App\Helpers\DateTimeHelper;
use App\Models\Food;
use App\Models\Month;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class FoodMapper extends AbstractMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper,
        private LocalizedTextMapper $textMapper,
        private BaseIndex $baseIndex,
        private ParentIndex $parentIndex,
    ) {}

    public function mapItemToFood(array $item, array $localizedTextItemList, array $foodMonthItemList): Food
    {
        $names = [];
        $descriptions = [];
        foreach ($localizedTextItemList as $localizedTextItem) {
            $parts = explode('#', $localizedTextItem[$this->baseIndex->getSortKey()]);
            switch ($parts[2]) {
                case 'name':
                    $names[] = $this->textMapper->mapItemToLocalizedText($localizedTextItem);
                    break;
                case 'description':
                    $descriptions[] = $this->textMapper->mapItemToLocalizedText($localizedTextItem);
                    break;
                default:
                    throw new RuntimeException("LocalizedText is not mapped!", $localizedTextItem);
            }
        }

        $prefix = Food::ENTITY_NAME . Month::ENTITY_NAME . '#';
        $monthIdList = [];
        foreach ($foodMonthItemList as $foodMonthItem) {
            $pk = $foodMonthItem[$this->baseIndex->getPartitionKey()];
            if (str_starts_with($pk, $prefix)) {
                $monthId = str_replace($prefix, '', $pk);
                if (!in_array($monthId, $monthIdList)) {
                    $monthIdList[] = $monthId;
                }
            }
        }

        return new Food(
            id: $item[$this->baseIndex->getPartitionKey()],
            names: $names,
            descriptions: $descriptions,
            imageName: $item['imageName'],
            categoryId: $item[$this->parentIndex->getPartitionKey()],
            monthIdList: $monthIdList,
            createdAt: $this->dateTimeHelper->createFromString($item['createdAt']),
            updatedAt: $this->dateTimeHelper->createFromString($item['updatedAt']),
        );
    }

    public function mapRequestDataToFood(array $data): Food
    {
        return new Food(
            id: $data['id'] ?? Uuid::uuid4(),
            names: $this->mapRequestDataToLocalizedText($data['names']),
            descriptions: $this->mapRequestDataToLocalizedText($data['descriptions']),
            imageName: $data['imageName'],
            categoryId: $data['categoryId'],
            monthIdList: $data['monthIdList'],
            image: $data['image'],
        );
    }

    public function mapFoodToItem(Food $food): array
    {
        return [
            $this->baseIndex->getPartitionKey() => $food->getId(),
            $this->baseIndex->getSortKey() => Food::ENTITY_NAME,
            $this->parentIndex->getPartitionKey() => $food->getCategoryId(),
            'imageName' => $food->getImageName(),
            'createdAt' => $this->dateTimeHelper->convertToString($food->getCreatedAt()),
            'updatedAt' => $this->dateTimeHelper->convertToString($food->getUpdatedAt())
        ];
    }
}
