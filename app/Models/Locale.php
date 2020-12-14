<?php


namespace App\Models;


use App\Helpers\DateTimeHelper;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

class Locale implements JsonSerializable
{
    const ENTITY_NAME = 'Locale';

    /**
     * Locale constructor.
     * @param string $id
     * @param string $name
     * @param DateTime|null $createdAt
     * @param DateTime|null $updatedAt
     */
    public function __construct(
        private string $id,
        private string $name,
        private ?DateTime $createdAt = null,
        private ?DateTime $updatedAt = null,
    )
    {}


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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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


    #[ArrayShape(['id' => "string", 'name' => "string", 'createdAt' => "\DateTime", 'updatedAt' => "\DateTime"])]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format(DateTimeHelper::DATE_FORMAT) : '',
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format(DateTimeHelper::DATE_FORMAT) : '',
        ];
    }

}
