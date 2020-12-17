<?php


namespace App\Mappers;


use App\Helpers\DateTimeHelper;
use App\Models\Category;
use App\Models\LocalizedText;
use JetBrains\PhpStorm\ArrayShape;

class CategoryMapper
{
    public function __construct(
        private DateTimeHelper $dateTimeHelper,
        private LocalizedTextMapper $textMapper
    ) {}

    public function mapItemToCategory(array $item): Category
    {
        $names = array_map(function(array $item) {
            return $this->textMapper->mapItemToLocalizedText($item);
        }, $item['names']);

        return new Category(
            id: $item['pk'],
            names: $names,
            createdAt: $this->dateTimeHelper->createFromString($item['createdAt']),
            updatedAt: $this->dateTimeHelper->createFromString($item['updatedAt']),
        );
    }

    public function mapRequestDataToCategory(array $data): Category
    {
        $names = array_map(function(array $nameData) {
            return new LocalizedText(
                $nameData['name'],
                $nameData['localeId'],
            );
        }, $data['names']);

        return new Category(
            $data['id'],
            $names
        );
    }

    #[ArrayShape(['pk' => "string", 'sk' => "string", 'createdAt' => "string", 'updatedAt' => "string"])]
    public function mapCategoryToItem(Category $category): array
    {
        return [
            'pk' => $category->getId(),
            'sk' => Category::ENTITY_NAME,
            'createdAt' => $this->dateTimeHelper->convertToString($category->getCreatedAt()),
            'updatedAt' => $this->dateTimeHelper->convertToString($category->getUpdatedAt())
        ];
    }
}
