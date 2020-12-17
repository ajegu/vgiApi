<?php


namespace App\Database\Repositories;


use App\Database\ClientFacade;
use App\Database\Indexes\InvertedIndex;
use App\Exceptions\ItemNotFound;
use App\Mappers\LocalizedTextMapper;
use App\Mappers\CategoryMapper;
use App\Models\LocalizedText;
use App\Models\Category;
use DateTime;
use JetBrains\PhpStorm\Pure;

class CategoryRepository extends AbstractRepository
{
    #[Pure]
    public function __construct(
        private ClientFacade $clientFacade,
        private InvertedIndex $invertedIndex,
        private CategoryMapper $mapper,
        private LocalizedTextMapper $textMapper
    ) {
        parent::__construct($this->clientFacade, $this->textMapper);
    }

    /**
     * @return Category[]
     */
    public function findAll(): array
    {
        $items = $this->clientFacade->findByPk(Category::ENTITY_NAME, $this->invertedIndex);
        return array_map(function(array $item) {
            $item = array_merge($item, [
                'names' => $this->clientFacade->findByPkAndSk($item['pk'], LocalizedText::ENTITY_NAME)
            ]);
            return $this->mapper->mapItemToCategory($item);
        }, $items);
    }

    /**
     * @param string $id
     * @return Category
     * @throws ItemNotFound
     */
    public function findOne(string $id): Category
    {
        $items = $this->clientFacade->findByPk($id);
        if (empty($items)) {
            throw new ItemNotFound("Item not found with PK: '{$id}'");
        }
        $categoryItem = ['names' => []];
        foreach ($items as $item) {
            if ($item['sk'] === Category::ENTITY_NAME) {
                $categoryItem = array_merge($categoryItem, $item);
            } else {
                $categoryItem['names'][] = $item;
            }
        }
        return $this->mapper->mapItemToCategory($categoryItem);
    }

    /**
     * @param Category $object
     * @return Category
     */
    public function create(mixed $object): mixed
    {
        $object->setCreatedAt(new DateTime());

        $monthItem = $this->mapper->mapCategoryToItem($object);
        $this->clientFacade->save($monthItem);

        foreach ($object->getNames() as $name) {
            $name->setCreatedAt(new DateTime());
            $nameItem = $this->textMapper->mapLocalizedTextToItem($name, $object->getId(), 'name');
            $this->clientFacade->save($nameItem);
        }

        return $object;
    }

    /**
     * @param Category $lastObject
     * @param Category $nextObject
     * @return Category
     */
    public function update(mixed $lastObject, mixed $nextObject): mixed
    {
        $lastNames = $this->updateNames($lastObject->getNames(), $nextObject->getNames(), $lastObject->getId());
        $lastObject->setNames($lastNames);
        return $lastObject;
    }

    /**
     * @param Category $object
     */
    public function delete(mixed $object): void
    {
        foreach ($object->getNames() as $name) {
            $item = $this->textMapper->mapLocalizedTextToItem($name, $object->getId(), 'name');
            $this->clientFacade->delete($item['pk'], $item['sk']);
        }

        $this->clientFacade->delete($object->getId(), Category::ENTITY_NAME);
    }

}
