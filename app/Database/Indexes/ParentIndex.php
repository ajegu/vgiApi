<?php


namespace App\Database\Indexes;


class ParentIndex implements IndexInterface
{
    public function __construct(
        private BaseIndex $baseIndex,
    ) {}

    public function getName(): ?string
    {
        return 'ParentIndex';
    }

    public function getPartitionKey(): string
    {
        return 'PK02';
    }

    public function getSortKey(): string
    {
        return $this->baseIndex->getPartitionKey();
    }

}
