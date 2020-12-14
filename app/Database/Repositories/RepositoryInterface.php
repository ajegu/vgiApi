<?php


namespace App\Database\Repositories;


use App\Exceptions\ItemNotFound;

interface RepositoryInterface
{
    public function findAll(): array;

    /**
     * @param string $id
     * @return mixed
     * @throws ItemNotFound
     */
    public function findOne(string $id): mixed;

    public function create(mixed $object): mixed;

    public function update(mixed $lastObject, mixed $nextObject): mixed;

    public function delete(mixed $object): void;
}
