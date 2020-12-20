<?php


namespace App\Rules;


use App\Database\Repositories\MonthRepository;
use App\Exceptions\ItemNotFound;
use Illuminate\Contracts\Validation\Rule;

class MonthUnknown implements Rule
{
    private string $failedValue;

    public function __construct(
        private MonthRepository $repository
    ) {}

    public function passes($attribute, $value): bool
    {
        foreach ($value as $row) {
            try {
                $this->repository->findOne($row);
            } catch (ItemNotFound) {
                $this->failedValue = $row;
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return "The month {$this->failedValue} is unknown.";
    }

}
