<?php


namespace App\Models;


use App\Helpers\DateTimeHelper;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

class LocalizedText implements JsonSerializable
{
    const ENTITY_NAME = 'LocalizedText';

    public function __construct(
        private string $name,
        private string $localeId,
        private ?DateTime $createdAt = null,
        private ?DateTime $updatedAt = null,
    ) {}

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
     * @return string
     */
    public function getLocaleId(): string
    {
        return $this->localeId;
    }

    /**
     * @param string $localeId
     */
    public function setLocaleId(string $localeId): void
    {
        $this->localeId = $localeId;
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

    #[ArrayShape(['id' => "string", 'text' => "string", 'createdAt' => "mixed", 'updatedAt' => "mixed"])]
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'localeId' => $this->getLocaleId(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format(DateTimeHelper::DATE_FORMAT) : '',
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format(DateTimeHelper::DATE_FORMAT) : '',
        ];
    }


}
