<?php


namespace App\Database\Repositories;


use App\Database\ClientFacade;
use App\Database\Indexes\BaseIndex;
use App\Database\Indexes\InvertedIndex;
use App\Database\Indexes\ParentIndex;
use App\Exceptions\ItemNotFound;
use App\Helpers\DateTimeHelper;
use App\Mappers\FoodMapper;
use App\Mappers\LocalizedTextMapper;
use App\Models\Category;
use App\Models\Food;
use App\Models\LocalizedText;
use App\Models\Month;
use DateTime;
use JetBrains\PhpStorm\Pure;

class FoodRepository extends AbstractRepository
{
    #[Pure]
    public function __construct(
        private ClientFacade $clientFacade,
        private FoodMapper $mapper,
        private LocalizedTextMapper $textMapper,
        private BaseIndex $baseIndex,
        private InvertedIndex $invertedIndex,
        private ParentIndex $parentIndex,
    ) {
        parent::__construct(
            $this->clientFacade,
            $this->textMapper,
            $this->baseIndex,
        );
    }

    /**
     * @return Food[]
     */
    public function findAll(): array
    {
        $items = $this->clientFacade->findByPk(Food::ENTITY_NAME, $this->invertedIndex);
        return array_map(function(array $item) {

            $localizedTextItemList = $this->clientFacade->findByPkAndSk(
                pkValue: $item[$this->baseIndex->getPartitionKey()],
                skValue: LocalizedText::ENTITY_NAME
            );

            $foodMonthItemList = $this->findFoodMonth($item[$this->baseIndex->getPartitionKey()]);

            return $this->mapper->mapItemToFood($item, $localizedTextItemList, $foodMonthItemList);
        }, $items);
    }

    /**
     * @param string $id
     * @return Food
     * @throws ItemNotFound
     */
    public function findOne(string $id): mixed
    {
        $items = $this->clientFacade->findByPk($id);
        if (empty($items)) {
            throw new ItemNotFound("Item not found with PK: '{$id}'");
        }

        $localizedTextItemList = [];
        $foodItem = [];
        foreach ($items as $item) {
            $sortKey = $item[$this->baseIndex->getSortKey()];
            if (str_starts_with($sortKey, LocalizedText::ENTITY_NAME)) {
                $localizedTextItemList[] = $item;
            } elseif (str_starts_with(Food::ENTITY_NAME, $sortKey)) {
                $foodItem = $item;
            }
        }

        $partitionKey = $foodItem[$this->baseIndex->getPartitionKey()];
        $foodMonthItemList = $this->findFoodMonth($partitionKey);

        return $this->mapper->mapItemToFood($foodItem, $localizedTextItemList, $foodMonthItemList);
    }

    /**
     * @param Food $object
     * @return Food
     */
    public function create(mixed $object): mixed
    {
        $object->setCreatedAt(new DateTime());
        $item = $this->mapper->mapFoodToItem($object);
        $this->clientFacade->save($item);
        $this->saveLocalizedTextList($object->getNames(), $object->getId(), 'name');
        $this->saveLocalizedTextList($object->getDescriptions(), $object->getId(), 'description');
        foreach ($object->getMonthIdList() as $monthId) {
            $this->createFoodMonth($monthId, $object);
        }

        return $object;
    }

    /**
     * @param Food $lastObject
     * @param Food $nextObject
     * @return mixed
     */
    public function update(mixed $lastObject, mixed $nextObject): mixed
    {
        $this->updateNames($lastObject->getNames(), $nextObject->getNames(), $lastObject->getId());
        $this->updateNames($lastObject->getDescriptions(), $nextObject->getDescriptions(), $lastObject->getId());
        $monthIdList = $this->updateFoodMonth($lastObject->getMonthIdList(), $nextObject->getMonthIdList(), $lastObject);
        $lastObject->setMonthIdList($monthIdList);

        $updated = false;
        if ($lastObject->getCategoryId() !== $nextObject->getCategoryId()) {
            $lastObject->setCategoryId($nextObject->getCategoryId());
            $updated = true;
        }

        if ($updated) {
            $lastObject->setUpdatedAt(new DateTime());
            $item = $this->mapper->mapFoodToItem($lastObject);
            $this->clientFacade->save($item);
        }

        return $lastObject;
    }

    /**
     * @param Food $object
     */
    public function delete(mixed $object): void
    {
        foreach ($object->getMonthIdList() as $monthId) {
            $this->deleteFoodMonth($monthId, $object);
        }

        $this->deleteLocalizedTextList($object->getNames(), $object->getId(), 'name');
        $this->deleteLocalizedTextList($object->getDescriptions(), $object->getId(), 'description');

        $this->clientFacade->delete(
            $object->getId(),
            Food::ENTITY_NAME
        );
    }

    public function updateImage(Food $food, string $imageName): Food
    {
        $food->setImageName($imageName);
        $food->setUpdatedAt(new DateTime());
        $item = $this->mapper->mapFoodToItem($food);
        $this->clientFacade->save($item);

        return $food;
    }

    /**
     * @param Category $category
     * @return Food[]
     * @throws ItemNotFound
     */
    public function findAllByCategory(Category $category): array
    {
        $itemList = $this->clientFacade->findByPk(
            $category->getId(),
            $this->parentIndex
        );

        $foodList = [];
        foreach ($itemList as $item) {
            if (Food::ENTITY_NAME === $item[$this->baseIndex->getSortKey()]) {
                $foodList[] = $this->findOne($item[$this->baseIndex->getPartitionKey()]);
            }
        }

        return $foodList;
    }

    /**
     * @param Month $month
     * @return Food[]
     * @throws ItemNotFound
     */
    public function findAllByMonth(Month $month): array
    {
        $itemList = $this->clientFacade->findByPk(
            Food::ENTITY_NAME . Month::ENTITY_NAME . '#' . $month->getId()
        );

        $foodList = [];
        foreach ($itemList as $item) {
            $foodList[] = $this->findOne($item[$this->parentIndex->getPartitionKey()]);
        }

        return $foodList;
    }

    private function findFoodMonth(string $partitionKey): array
    {
        $sk = Food::ENTITY_NAME . Month::ENTITY_NAME;

        return $this->clientFacade->findByPkAndSk(
            $partitionKey,
            $sk,
            $this->parentIndex
        );
    }

    private function createFoodMonth(string $monthId, Food $food): void
    {
        foreach ($food->getNames() as $name) {
            $item = [
                $this->baseIndex->getPartitionKey() => Food::ENTITY_NAME . Month::ENTITY_NAME . '#' . $monthId,
                $this->baseIndex->getSortKey() => $name->getLocaleId() . '#' . $name->getName(),
                $this->parentIndex->getPartitionKey() => $food->getId(),
                'createdAt' => (new DateTime())->format(DateTimeHelper::DATE_FORMAT)
            ];

            $this->clientFacade->save($item);
        }

    }

    private function updateFoodMonth(array $lastMonthIdList, array $nextMonthIdList, Food $food): array
    {
        $foodMonthList = [];

        // delete last months
        foreach ($lastMonthIdList as $lastId) {
            if (!in_array($lastId, $nextMonthIdList)) {
                $this->deleteFoodMonth($lastId, $food);
            } else {
                $foodMonthList[] = $lastId;
            }
        }

        // add next months
        foreach ($nextMonthIdList as $nextId) {
            if (!in_array($nextId, $lastMonthIdList)) {
                $this->createFoodMonth($nextId, $food);
                $foodMonthList[] = $nextId;
            }
        }

        return $foodMonthList;
    }

    private function deleteFoodMonth(string $monthId, Food $food): void
    {
        $partitionKey = Food::ENTITY_NAME . Month::ENTITY_NAME . '#' . $monthId;
        foreach ($food->getNames() as $name) {
            $sortKey = $name->getLocaleId() . '#' . $name->getName();
            $this->clientFacade->delete($partitionKey, $sortKey);
        }
    }
}
