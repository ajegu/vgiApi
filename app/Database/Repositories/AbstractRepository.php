<?php


namespace App\Database\Repositories;


use App\Database\ClientFacade;
use App\Database\Indexes\BaseIndex;
use App\Mappers\LocalizedTextMapper;
use App\Models\LocalizedText;
use DateTime;

abstract class AbstractRepository implements RepositoryInterface
{
    public function __construct(
        private ClientFacade $clientFacade,
        private LocalizedTextMapper $textMapper,
        private BaseIndex $baseIndex,
    ) {}

    /**
     * @param LocalizedText[] $textList
     * @param string $partitionKey
     * @param string $attribute
     */
    protected function saveLocalizedTextList(array $textList, string $partitionKey, string $attribute): void
    {
        foreach ($textList as $text) {
            $text->setCreatedAt(new DateTime());
            $item = $this->textMapper->mapLocalizedTextToItem($text, $partitionKey, $attribute);
            $this->clientFacade->save($item);
        }
    }

    /**
     * @param LocalizedText[] $lastNames
     * @param LocalizedText[] $nextNames
     * @param string $pk
     * @return array
     */
    protected function updateNames(array $lastNames, array $nextNames, string $pk): array
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
                $this->clientFacade->delete(
                    $item[$this->baseIndex->getPartitionKey()],
                    $item[$this->baseIndex->getSortKey()]
                );
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
                $nextName->setUpdatedAt(new DateTime());
                $nameItem = $this->textMapper->mapLocalizedTextToItem($nextName, $pk, 'name');
                $this->clientFacade->save($nameItem);
                $lastNames[] = $nextName;
            }
        }

        return $lastNames;
    }

    /**
     * @param LocalizedText[] $textList
     * @param string $partitionKey
     * @param string $attribute
     */
    protected function deleteLocalizedTextList(array $textList, string $partitionKey, string $attribute): void
    {
        foreach ($textList as $text) {
            $text->setCreatedAt(new DateTime());
            $item = $this->textMapper->mapLocalizedTextToItem($text, $partitionKey, $attribute);
            $this->clientFacade->delete(
                $item[$this->baseIndex->getPartitionKey()],
                $item[$this->baseIndex->getSortKey()],
            );
        }
    }
}
