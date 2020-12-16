<?php


namespace App\Models;


use App\Helpers\DateTimeHelper;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

class Month implements JsonSerializable
{
    const ENTITY_NAME = 'Month';

    public function __construct(
        private string $id,
        private array $names,
        private string $seasonId,
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
     * @return LocalizedText[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @param LocalizedText[] $names
     */
    public function setNames(array $names): void
    {
        $this->names = $names;
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }

    /**
     * @param string $seasonId
     */
    public function setSeasonId(string $seasonId): void
    {
        $this->seasonId = $seasonId;
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


    #[ArrayShape(['id' => "string", 'names' => "array[]", 'createdAt' => "string", 'updatedAt' => "string"])]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'names' => array_map(fn(LocalizedText $text) => $text->jsonSerialize(),  $this->getNames()),
            'seasonId' => $this->getSeasonId(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format(DateTimeHelper::DATE_FORMAT) : '',
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format(DateTimeHelper::DATE_FORMAT) : '',
        ];
    }


}
