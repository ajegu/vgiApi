<?php


namespace App\Database\Repositories;


use App\Database\Indexes\InvertedIndex;
use App\Database\ClientFacade;
use App\Mappers\LocaleMapper;
use App\Models\Locale;
use DateTime;

class LocaleRepository implements RepositoryInterface
{
    public function __construct(
        private ClientFacade $clientFacade,
        private InvertedIndex $reversedIndex,
        private LocaleMapper $mapper
    )
    {
    }

    public function findAll(): array
    {
        $items = $this->clientFacade->findByPk(Locale::ENTITY_NAME, $this->reversedIndex);
        return array_map(function(array $item) {
            return $this->mapper->mapItemToLocale($item);
        }, $items);
    }

    public function findOne(string $id): Locale
    {
        $item = $this->clientFacade->findOneByPkAndSk($id, Locale::ENTITY_NAME);
        return $this->mapper->mapItemToLocale($item);
    }

    /**
     * @param Locale $object
     * @return Locale $locale
     */
    public function create(mixed $object): Locale
    {
        $object->setCreatedAt(new DateTime());

        $item = $this->mapper->mapLocaleToItem($object);
        $this->clientFacade->save($item);
        return $object;
    }

    /**
     * @param Locale $lastObject
     * @param Locale $nextObject
     * @return Locale
     */
    public function update(mixed $lastObject, mixed $nextObject): Locale
    {
        $lastObject->setUpdatedAt(new DateTime());

        $isUpdated = false;
        if ($nextObject->getName() !== $lastObject->getName()) {
            $lastObject->setName($nextObject->getName());
            $isUpdated = true;
        }

        if ($isUpdated) {
            $item = $this->mapper->mapLocaleToItem($lastObject);
            $this->clientFacade->save($item);
        }

        return $lastObject;
    }

    /**
     * @param Locale $object
     */
    public function delete(mixed $object): void
    {
        $this->clientFacade->delete($object->getId(), Locale::ENTITY_NAME);
    }


}
