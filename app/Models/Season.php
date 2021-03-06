<?php


namespace App\Models;


use App\Helpers\DateTimeHelper;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

class Season implements JsonSerializable
{
    const ENTITY_NAME = 'Season';

    public function __construct(
        private string $id,
        private array $names,
        private ?DateTime $createdAt = null,
        private ?DateTime $updatedAt = null,
    ) {}

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @param array $names
     */
    public function setNames(array $names): void
    {
        $this->names = $names;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $createdAt
     */
    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     */
    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    #[ArrayShape(['id' => "string", 'name' => "string", 'createdAt' => "string", 'updatedAt' => "string"])]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'names' => $this->getNames(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format(DateTimeHelper::DATE_FORMAT) : '',
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format(DateTimeHelper::DATE_FORMAT) : '',
        ];
    }

}
