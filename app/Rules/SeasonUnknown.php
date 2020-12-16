<?php


namespace App\Rules;


use App\Database\Repositories\SeasonRepository;
use App\Exceptions\ItemNotFound;
use Illuminate\Contracts\Validation\Rule;

class SeasonUnknown implements Rule
{
    public function __construct(
        private SeasonRepository $repository
    ) {}

    public function passes($attribute, $value): bool
    {
        try {
            $this->repository->findOne($value);
        } catch (ItemNotFound) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return 'The season must be known.';
    }

}
