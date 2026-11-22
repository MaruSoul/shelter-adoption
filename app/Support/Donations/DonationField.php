<?php

namespace App\Support\Donations;

/**
 * Поле способу допомоги (IBAN, посилання на банку тощо): як його показати у формі панелі,
 * як перевірити й чи давати відвідувачу кнопку «Копіювати».
 */
final readonly class DonationField
{
    /**
     * @param  array<int, mixed>  $rules  правила перевірки Laravel (без required – його додає $required)
     */
    public function __construct(
        public string $key,
        public string $label,
        public bool $required = true,
        public array $rules = [],
        public string $placeholder = '',
        public string $hint = '',
        public bool $copyable = false,
        public string $inputType = 'text',
    ) {}

    /** @return array<int, mixed> */
    public function validationRules(): array
    {
        return [$this->required ? 'required' : 'nullable', 'string', 'max:255', ...$this->rules];
    }
}
