<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Validates that text contains only safe characters (no XSS or injection attempts)
 */
#[\Attribute]
class SafeText extends Constraint
{
    public string $message = 'This field contains invalid characters. Only letters, numbers, and basic punctuation are allowed.';

    public bool $allowHtml = false;
    public bool $allowLineBreaks = true;
    public array $allowedSpecialChars = ['.', ',', '!', '?', ':', ';', "'", '"', '-', '_', '(', ')', '/', '@', '&'];

    public function __construct(
        ?array $options = null,
        ?string $message = null,
        ?bool $allowHtml = null,
        ?bool $allowLineBreaks = null,
        ?array $allowedSpecialChars = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
        $this->allowHtml = $allowHtml ?? $this->allowHtml;
        $this->allowLineBreaks = $allowLineBreaks ?? $this->allowLineBreaks;
        $this->allowedSpecialChars = $allowedSpecialChars ?? $this->allowedSpecialChars;
    }
}