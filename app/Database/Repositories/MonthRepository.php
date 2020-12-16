<?php


namespace App\Database\Repositories;


use App\Database\ClientFacade;
use App\Database\Indexes\InvertedIndex;
use App\Exceptions\ItemNotFound;
use App\Mappers\LocalizedTextMapper;
use App\Mappers\MonthMapper;
use App\Models\LocalizedText;
use App\Models\Month;
use DateTime;
use JetBrains\PhpStorm\Pure;

class MonthRepository extends AbstractRepository
{
    #[Pure]
    public function __construct(
        private ClientFacade $clientFacade,
        private InvertedIndex $reversedIndex,
        private MonthMapper $mapper,
        private LocalizedTextMapper $textMapper
    ) {
        parent::__construct($this->clientFacade, $this->textMapper);
    }

    public function findAll(): array
    {
        $items = $this->clientFacade->findByPk(Month::ENTITY_NAME, $this->reversedIndex);
        return array_map(function(array $item) {
            $item = array_merge($item, [
                'names' => $this->clientFacade->findByPkAndSk($item['pk'], LocalizedText::ENTITY_NAME)
            ]);
            return $this->mapper->mapItemToMonth($item);
        }, $items);
    }

    public function findOne(string $id): Month
    {
        $items = $this->clientFacade->findByPk($id);
        if (empty($items)) {
            throw new ItemNotFound("Item not found with PK: '{$id}'");
        }
        $monthItem = ['names' => []];
        foreach ($items as $item) {
            if ($item['sk'] === Month::ENTITY_NAME) {
                $monthItem = array_merge($monthItem, $item);
            } else {
                $monthItem['names'][] = $item;
            }
        }

        return $this->mapper->mapItemToMonth($monthItem);
    }

    /**
     * @param Month $object
     * @return Month
     */
    public function create(mixed $object): mixed
    {
        $object->setCreatedAt(new DateTime());

        $monthItem = $this->mapper->mapMonthToItem($object);
        $this->clientFacade->save($monthItem);

        foreach ($object->getNames() as $name) {
            $name->setCreatedAt(new DateTime());
            $nameItem = $this->textMapper->mapLocalizedTextToItem($name, $object->getId(), 'name');
            $this->clientFacade->save($nameItem);
        }

        return $object;
    }

    /**
     * @param Month $lastObject
     * @param Month $nextObject
     * @return Month
     */
    public function update(mixed $lastObject, mixed $nextObject): mixed
    {
        $lastNames = $this->updateNames($lastObject->getNames(), $nextObject->getNames(), $lastObject->getId());
        $lastObject->setNames($lastNames);

        if ($lastObject->getSeasonId() !== $nextObject->getSeasonId()) {
            $lastObject->setSeasonId($nextObject->getSeasonId());
            $lastObject->setUpdatedAt(new DateTime());

            $monthItem = $this->mapper->mapMonthToItem($lastObject);
            $this->clientFacade->save($monthItem);
        }

        return $lastObject;
    }

    /**
     * @param Month $object
     */
    public function delete(mixed $object): void
    {
        foreach ($object->getNames() as $name) {
            $item = $this->textMapper->mapLocalizedTextToItem($name, $object->getId(), 'name');
            $this->clientFacade->delete($item['pk'], $item['sk']);
        }

        $this->clientFacade->delete($object->getId(), Month::ENTITY_NAME);
    }

}
