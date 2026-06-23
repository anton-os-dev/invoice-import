<?php

namespace App\Integrations;

use Illuminate\Support\Collection;

interface AccountingApi
{
    public function authenticate(): AuthResult;

    public function createInvoices(Collection $invoices): array;
}
