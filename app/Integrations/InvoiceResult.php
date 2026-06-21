<?php

namespace App\Integrations;

class InvoiceResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $externalRef = null,
        public readonly ?string $error = null,
        public readonly array $rawResponse = [],
    ) {}
}
