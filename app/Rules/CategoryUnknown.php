<?php


namespace App\Rules;


use App\Database\Repositories\CategoryRepository;
use App\Exceptions\ItemNotFound;
use Illuminate\Contracts\Validation\Rule;

class CategoryUnknown implements Rule
{
    public function __construct(
        private CategoryRepository $repository
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
        return 'The category must be known.';
    }

}
