<?php


namespace App\Database\Indexes;


use JetBrains\PhpStorm\Pure;

class InvertedIndex implements IndexInterface
{

    public function __construct(
        private BaseIndex $baseIndex
    ){}

    public function getName(): string
    {
        return 'InvertedIndex';
    }

    #[Pure]
    public function getPartitionKey(): string
    {
        return $this->baseIndex->getSortKey();
    }

    #[Pure]
    public function getSortKey(): string
    {
        return $this->baseIndex->getPartitionKey();
    }


}
