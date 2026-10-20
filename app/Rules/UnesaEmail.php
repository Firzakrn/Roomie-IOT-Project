<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UnesaEmail implements Rule
{
    public function __construct(
        protected string $domain = 'unesa.ac.id'
    ) {}

    public function passes($attribute, $value): bool
    {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $emailDomain = strtolower(substr(strrchr($value, '@'), 1));
        $allowed = strtolower($this->domain);

        // Persis unesa.ac.id ATAU subdomain *.unesa.ac.id
        return $emailDomain === $allowed
            || str_ends_with($emailDomain, '.'.$allowed);
    }

    public function message(): string
    {
        return 'Email harus menggunakan domain UNESA (@unesa.ac.id atau subdomain-nya).';
    }
}
