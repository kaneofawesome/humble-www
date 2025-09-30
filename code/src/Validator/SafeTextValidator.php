<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class SafeTextValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof SafeText) {
            throw new UnexpectedTypeException($constraint, SafeText::class);
        }

        // null and empty values are valid
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        // Check for dangerous patterns
        if ($this->containsDangerousPatterns($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
            return;
        }

        // Check for HTML if not allowed
        if (!$constraint->allowHtml && $this->containsHtml($value)) {
            $this->context->buildViolation('HTML tags are not allowed in this field.')
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
            return;
        }

        // Check character whitelist
        if (!$this->hasOnlyAllowedCharacters($value, $constraint)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }
    }

    private function containsDangerousPatterns(string $value): bool
    {
        // Common XSS and injection patterns
        $dangerousPatterns = [
            '/<script[\s\S]*?<\/script>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/data:text\/html/i',
            '/vbscript:/i',
            '/<iframe[\s\S]*?<\/iframe>/i',
            '/<object[\s\S]*?<\/object>/i',
            '/<embed[\s\S]*?<\/embed>/i',
            '/<link[\s\S]*?>/i',
            '/<meta[\s\S]*?>/i',
            '/\{[\s\S]*\}/i', // Potential template injection
            '/\$\{[\s\S]*?\}/i', // Template literals
            '/<%[\s\S]*?%>/i', // Server-side includes
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    private function containsHtml(string $value): bool
    {
        return $value !== strip_tags($value);
    }

    private function hasOnlyAllowedCharacters(string $value, SafeText $constraint): bool
    {
        // Build allowed character pattern
        $allowedChars = 'a-zA-Z0-9\s'; // Always allow alphanumeric and spaces

        // Add allowed special characters
        $escapedSpecialChars = array_map('preg_quote', $constraint->allowedSpecialChars);
        $allowedChars .= implode('', $escapedSpecialChars);

        // Add line breaks if allowed
        if ($constraint->allowLineBreaks) {
            $allowedChars .= '\r\n';
        }

        $pattern = '/^[' . $allowedChars . ']+$/';

        return preg_match($pattern, $value) === 1;
    }
}