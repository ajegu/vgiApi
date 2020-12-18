<?php


namespace App\Database\Repositories;


use App\Database\ClientFacade;
use App\Database\Indexes\BaseIndex;
use App\Database\Indexes\InvertedIndex;
use App\Exceptions\ItemNotFound;
use App\Mappers\LocalizedTextMapper;
use App\Mappers\SeasonMapper;
use App\Models\LocalizedText;
use App\Models\Season;
use DateTime;
use JetBrains\PhpStorm\Pure;

class SeasonRepository extends AbstractRepository
{
    #[Pure]
    public function __construct(
        private ClientFacade $clientFacade,
        private BaseIndex $baseIndex,
        private InvertedIndex $invertedIndex,
        private SeasonMapper $mapper,
        private LocalizedTextMapper $textMapper
    ) {
        parent::__construct($this->clientFacade, $this->textMapper, $baseIndex);
    }

    /**
     * @return Season[]
     */
    public function findAll(): array
    {
        $items = $this->clientFacade->findByPk(Season::ENTITY_NAME, $this->invertedIndex);
        return array_map(function(array $item) {
            $item = array_merge($item, [
                'names' => $this->clientFacade->findByPkAndSk(
                    pkValue: $item[$this->baseIndex->getPartitionKey()],
                    skValue: LocalizedText::ENTITY_NAME
                )
            ]);
            return $this->mapper->mapItemToSeason($item);
        }, $items);
    }

    /**
     * @param string $id
     * @return Season
     * @throws ItemNotFound
     */
    public function findOne(string $id): Season
    {
        $items = $this->clientFacade->findByPk($id);
        if (empty($items)) {
            throw new ItemNotFound("Item not found with PK: '{$id}'");
        }
        $seasonItem = ['names' => []];
        foreach ($items as $item) {
            if ($item[$this->baseIndex->getSortKey()] === Season::ENTITY_NAME) {
                $seasonItem = array_merge($seasonItem, $item);
            } else {
                $seasonItem['names'][] = $item;
            }
        }
        return $this->mapper->mapItemToSeason($seasonItem);
    }

    /**
     * @param Season $object
     * @return Season
     */
    public function create(mixed $object): mixed
    {
        $object->setCreatedAt(new DateTime());

        $monthItem = $this->mapper->mapSeasonToItem($object);
        $this->clientFacade->save($monthItem);

        foreach ($object->getNames() as $name) {
            $name->setCreatedAt(new DateTime());
            $nameItem = $this->textMapper->mapLocalizedTextToItem($name, $object->getId(), 'name');
            $this->clientFacade->save($nameItem);
        }

        return $object;
    }

    /**
     * @param Season $lastObject
     * @param Season $nextObject
     * @return Season
     */
    public function update(mixed $lastObject, mixed $nextObject): mixed
    {
        $lastNames = $this->updateNames($lastObject->getNames(), $nextObject->getNames(), $lastObject->getId());
        $lastObject->setNames($lastNames);
        return $lastObject;
    }

    /**
     * @param Season $object
     */
    public function delete(mixed $object): void
    {
        foreach ($object->getNames() as $name) {
            $item = $this->textMapper->mapLocalizedTextToItem($name, $object->getId(), 'name');
            $this->clientFacade->delete(
                $item[$this->baseIndex->getPartitionKey()],
                $item[$this->baseIndex->getSortKey()]
            );
        }

        $this->clientFacade->delete($object->getId(), Season::ENTITY_NAME);
    }

}
