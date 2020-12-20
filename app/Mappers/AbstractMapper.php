<?php


namespace App\Mappers;


use App\Models\LocalizedText;

abstract class AbstractMapper
{
    /**
     * @param array $values
     * @return LocalizedText[]
     */
    protected function mapRequestDataToLocalizedText(array $values): array
    {
        $text = [];
        foreach ($values as $value) {
            $text[] = new LocalizedText(
                name: $value['name'] ?? '',
                localeId: $value['localeId'] ?? '',
            );
        }

        return $text;
    }
}
