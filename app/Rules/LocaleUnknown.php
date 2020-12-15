<?php


namespace App\Rules;


use App\Database\Repositories\LocaleRepository;
use App\Exceptions\ItemNotFound;
use Illuminate\Contracts\Validation\Rule;

class LocaleUnknown implements Rule
{
    public function __construct(
        private LocaleRepository $repository
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
        return 'The locale must be known.';
    }

}
