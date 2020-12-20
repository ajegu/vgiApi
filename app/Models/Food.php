<?php


namespace App\Models;


use Illuminate\Http\UploadedFile;
use JetBrains\PhpStorm\ArrayShape;
use DateTime;
use App\Helpers\DateTimeHelper;
use JsonSerializable;

class Food implements JsonSerializable
{
    const ENTITY_NAME = 'Food';

    public function __construct(
        private string $id,
        private array $names,
        private array $descriptions,
        private string $imageName,
        private string $categoryId,
        private array $monthIdList,
        private ?DateTime $createdAt = null,
        private ?DateTime $updatedAt = null,
        private ?UploadedFile $image = null,
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
     * @return array
     */
    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    /**
     * @param array $descriptions
     */
    public function setDescriptions(array $descriptions): void
    {
        $this->descriptions = $descriptions;
    }

    /**
     * @return string
     */
    public function getImageName(): string
    {
        return $this->imageName;
    }

    /**
     * @param string $imageName
     */
    public function setImageName(string $imageName): void
    {
        $this->imageName = $imageName;
    }

    /**
     * @return string
     */
    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    /**
     * @param string $categoryId
     */
    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return array
     */
    public function getMonthIdList(): array
    {
        return $this->monthIdList;
    }

    /**
     * @param array $monthIdList
     */
    public function setMonthIdList(array $monthIdList): void
    {
        $this->monthIdList = $monthIdList;
    }

    /**
     * @return UploadedFile|null
     */
    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    /**
     * @param UploadedFile|null $image
     */
    public function setImage(?UploadedFile $image): void
    {
        $this->image = $image;
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

    #[ArrayShape(['id' => "string", 'names' => "array", 'descriptions' => "array", 'image' => "string", 'categoryId' => "string", 'monthsIds' => "string[]"])]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'names' => $this->getNames(),
            'descriptions' => $this->getDescriptions(),
            'image' => $this->getImageName(),
            'categoryId' => $this->getCategoryId(),
            'monthIdList' => array_map(fn(string $id) => $id, $this->getMonthIdList()),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format(DateTimeHelper::DATE_FORMAT) : '',
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format(DateTimeHelper::DATE_FORMAT) : '',
        ];
    }
}
