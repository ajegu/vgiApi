<?php


namespace App\Database\Indexes;


class InvertedIndex implements IndexInterface
{
    public function getName(): string
    {
        return 'InvertedIndex';
    }

    public function getPartitionKey(): string
    {
        return 'sk';
    }

}
