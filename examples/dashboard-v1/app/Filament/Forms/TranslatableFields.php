<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TranslatableFields
{
    /**
     * @return list<TextInput|Textarea>
     */
    public static function text(string $field, string $label, bool $textarea = false): array
    {
        $locales = ['en' => 'English', 'es' => 'Spanish'];

        return collect($locales)->map(function (string $localeLabel, string $locale) use ($field, $label, $textarea) {
            $component = $textarea
                ? Textarea::make("{$field}.{$locale}")
                : TextInput::make("{$field}.{$locale}");

            return $component
                ->label("{$label} ({$localeLabel})")
                ->required($locale === 'en')
                ->maxLength($textarea ? null : 255);
        })->all();
    }

    /**
     * @param  list<string>  $fields
     * @param  list<string>  $locales
     * @return array<string, mixed>
     */
    public static function collapse(array $data, array $fields, array $locales = ['en', 'es']): array
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = collect($data[$field])
                    ->only($locales)
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->all();

                continue;
            }

            $translations = [];

            foreach ($locales as $locale) {
                $key = "{$field}.{$locale}";

                if (array_key_exists($key, $data)) {
                    $translations[$locale] = $data[$key];
                    unset($data[$key]);
                }
            }

            if ($translations !== []) {
                $data[$field] = $translations;
            }
        }

        return $data;
    }
}
