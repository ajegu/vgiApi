<?php


namespace App\Database\Indexes;


interface IndexInterface
{
    public function getName(): ?string;
    public function getPartitionKey(): string;
    public function getSortKey(): string;
}
