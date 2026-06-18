<?php

namespace App\Integrations;

use App\Models\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FakeAccountingApi implements AccountingApi
{
    private array $existing = [];

    public function createInvoices(Collection $invoices): array
    {
        $results = [];

        foreach ($invoices as $invoice) {
            $results[$invoice->id] = $this->handleOne($invoice);
        }

        return $results;
    }

    private function handleOne(Invoice $invoice): InvoiceResult
    {
        if (in_array($invoice->invoice_number, $this->existing, true)) {
            return new InvoiceResult(
                success: false,
                error: "Invoice {$invoice->invoice_number} already exists",
                rawResponse: ['code' => 'DUPLICATE', 'message' => 'Invoice number already exists'],
            );
        }

        if ($invoice->amount === null || $invoice->amount <= 0) {
            return new InvoiceResult(
                success: false,
                error: 'Amount must be greater than zero',
                rawResponse: ['code' => 'VALIDATION', 'message' => 'Invalid amount'],
            );
        }

        $ref = 'ACC-' . Str::upper(Str::random(8));
        $this->existing[] = $invoice->invoice_number;

        return new InvoiceResult(
            success: true,
            externalRef: $ref,
            rawResponse: ['code' => 'OK', 'id' => $ref, 'status' => 'posted'],
        );
    }
}
