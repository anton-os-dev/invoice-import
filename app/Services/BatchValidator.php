<?php

namespace App\Services;

use App\Enums\BatchStatus;
use App\Enums\InvoiceStatus;
use App\Models\ImportBatch;
use App\Models\Invoice;

class BatchValidator
{
    private const CHUNK_SIZE = 200;

    public function validate(ImportBatch $batch): void
    {
        $batch->invoices()
            ->where('status', InvoiceStatus::Pending)
            ->chunkById(self::CHUNK_SIZE, function ($invoices) {
                foreach ($invoices as $invoice) {
                    $errors = $this->validateInvoice($invoice);

                    $invoice->update([
                        'status'            => empty($errors) ? InvoiceStatus::Valid : InvoiceStatus::Invalid,
                        'validation_errors' => empty($errors) ? null : $errors,
                    ]);
                }
            });

        $validCount = $batch->invoices()->where('status', InvoiceStatus::Valid)->count();

        $batch->update([
            'status'     => BatchStatus::Validated,
            'valid_rows' => $validCount,
        ]);
    }

    /**
     * @return array<string, string>  поле => текст ошибки
     */
    private function validateInvoice(Invoice $invoice): array
    {
        $errors = [];

        if (blank($invoice->invoice_number)) {
            $errors['invoice_number'] = 'Invoice number is required';
        }

        if ($invoice->invoice_date === null) {
            $errors['invoice_date'] = 'Invoice date is required or invalid';
        }

        if ($invoice->amount === null) {
            $errors['amount'] = 'Amount is required';
        } elseif ($invoice->amount <= 0) {
            $errors['amount'] = 'Amount must be greater than zero';
        }

        return $errors;
    }
}
