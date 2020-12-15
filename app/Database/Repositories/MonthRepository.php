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

class MonthRepository implements RepositoryInterface
{
    public function __construct(
        private ClientFacade $clientFacade,
        private InvertedIndex $reversedIndex,
        private MonthMapper $mapper,
        private LocalizedTextMapper $textMapper
    ) {}

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

        // TODO: Add season check
//        if (false) {
//            $lastObject->setUpdatedAt(new DateTime());
//
//            $monthItem = $this->mapper->mapMonthToItem($lastObject);
//            $this->clientFacade->save($monthItem);
//        }

        return $lastObject;
    }

    private function updateNames(array $lastNames, array $nextNames, string $pk): array
    {
        // check for deleted or updated names
        foreach ($lastNames as $key => $lastName) {
            $exists = false;
            foreach ($nextNames as $nextName) {
                if ($lastName->getLocaleId() === $nextName->getLocaleId()) {
                    $exists = true;
                    if ($lastName->getName() !== $nextName->getName()) {
                        $lastName->setName($nextName->getName());
                        $lastName->setUpdatedAt(new DateTime());
                        $nameItem = $this->textMapper->mapLocalizedTextToItem($lastName, $pk, 'name');
                        $this->clientFacade->save($nameItem);
                        $lastNames[] = $nextName;
                    }
                    break;
                }
            }

            if (!$exists) {
                unset($lastNames[$key]);
                $item = $this->textMapper->mapLocalizedTextToItem($lastName, $pk, 'name');
                $this->clientFacade->delete($item['pk'], $item['sk']);
            }
        }

        // check for added names
        foreach ($nextNames as $nextName) {
            $exists = false;
            foreach ($lastNames as $lastName) {
                if ($nextName->getLocaleId() === $lastName->getLocaleId()) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $nextName->setCreatedAt(new DateTime());
                $nameItem = $this->textMapper->mapLocalizedTextToItem($nextName, $pk, 'name');
                $this->clientFacade->save($nameItem);
                $lastNames[] = $nextName;
            }
        }

        return $lastNames;
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
