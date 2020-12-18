<?php


namespace App\Database\Indexes;


class BaseIndex implements IndexInterface
{
    public function getName(): ?string {
        return null;
    }

    public function getPartitionKey(): string
    {
        return 'PK';
    }

    public function getSortKey(): string
    {
        return 'SK';
    }

}
