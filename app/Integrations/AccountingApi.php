<?php

namespace App\Integrations;

use Illuminate\Support\Collection;

interface AccountingApi
{
    /**
     * @param  Collection<int, \App\Models\Invoice>  $invoices
     * @return array<int, InvoiceResult>
     */
    public function createInvoices(Collection $invoices): array;
}
