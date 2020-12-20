<?php


namespace App\Mappers;


use App\Database\Indexes\BaseIndex;
use App\Helpers\DateTimeHelper;
use App\Models\Category;

class CategoryMapper extends AbstractMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper,
        private LocalizedTextMapper $textMapper,
        private BaseIndex $baseIndex,
    ) {}

    public function mapItemToCategory(array $item): Category
    {
        $names = array_map(function(array $item) {
            return $this->textMapper->mapItemToLocalizedText($item);
        }, $item['names']);

        return new Category(
            id: $item[$this->baseIndex->getPartitionKey()],
            names: $names,
            createdAt: $this->dateTimeHelper->createFromString($item['createdAt']),
            updatedAt: $this->dateTimeHelper->createFromString($item['updatedAt']),
        );
    }

    public function mapRequestDataToCategory(array $data): Category
    {
        $names = $this->mapRequestDataToLocalizedText($data['names']);

        return new Category(
            $data['id'],
            $names
        );
    }

    public function mapCategoryToItem(Category $category): array
    {
        return [
            $this->baseIndex->getPartitionKey() => $category->getId(),
            $this->baseIndex->getSortKey() => Category::ENTITY_NAME,
            'createdAt' => $this->dateTimeHelper->convertToString($category->getCreatedAt()),
            'updatedAt' => $this->dateTimeHelper->convertToString($category->getUpdatedAt())
        ];
    }
}
