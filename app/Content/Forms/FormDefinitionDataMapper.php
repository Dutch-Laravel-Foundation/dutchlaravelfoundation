<?php

declare(strict_types=1);

namespace App\Content\Forms;

use App\Data\Forms\FormDefinitionData;
use App\Data\Forms\FormFieldData;
use Illuminate\Support\Enumerable;

final class FormDefinitionDataMapper
{
    /** @param array<string, mixed> $form */
    public function map(array $form): FormDefinitionData
    {
        $handle = $this->string($form['handle'] ?? null);
        $fields = [];

        foreach ($this->array($form['fields'] ?? null) as $field) {
            if (! is_array($field)) {
                continue;
            }

            $fields[] = new FormFieldData(
                handle: $this->string($field['handle'] ?? null),
                type: $this->string($field['type'] ?? null),
                display: $this->string($field['display'] ?? null),
                instructions: $this->nullableString($field['instructions'] ?? null),
                width: is_int($field['width'] ?? null) ? $field['width'] : null,
                ifConditions: $this->associativeArray($field['if'] ?? null),
                unlessConditions: $this->associativeArray($field['unless'] ?? null),
                config: $this->associativeArray($field['config'] ?? null),
            );
        }

        return new FormDefinitionData(
            handle: $handle,
            title: $this->string($form['title'] ?? null),
            action: "/!/forms/{$handle}",
            honeypot: $this->nullableString($form['honeypot'] ?? null),
            rules: $this->rules($form['rules'] ?? null),
            fields: $fields,
        );
    }

    /** @return array<string, array<int, string>> */
    private function rules(mixed $value): array
    {
        $rules = [];

        foreach ($this->array($value) as $handle => $fieldRules) {
            if (! is_string($handle)) {
                continue;
            }

            $rules[$handle] = array_values(array_filter($this->array($fieldRules), 'is_string'));
        }

        return $rules;
    }

    /** @return array<int|string, mixed> */
    private function array(mixed $value): array
    {
        if ($value instanceof Enumerable) {
            return $value->all();
        }

        return is_array($value) ? $value : [];
    }

    /** @return array<string, mixed> */
    private function associativeArray(mixed $value): array
    {
        $values = [];

        foreach ($this->array($value) as $key => $item) {
            if (is_string($key)) {
                $values[$key] = $item;
            }
        }

        return $values;
    }

    private function string(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
