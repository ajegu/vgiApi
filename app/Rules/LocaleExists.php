<?php


namespace App\Rules;


use App\Database\Repositories\LocaleRepository;
use App\Exceptions\ItemNotFound;
use Illuminate\Contracts\Validation\Rule;

class LocaleExists implements Rule
{
    public function __construct(
        private LocaleRepository $repository
    ) {}

    public function passes($attribute, $value): bool
    {
        try {
            $this->repository->findOne($value);
        } catch (ItemNotFound) {
            return true;
        }

        return false;
    }

    public function message(): string
    {
        return 'Item already exists.';
    }

}
