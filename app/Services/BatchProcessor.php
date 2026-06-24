<?php

namespace App\Services;

use App\Enums\BatchStatus;
use App\Enums\InvoiceStatus;
use App\Integrations\AccountingApi;
use App\Models\ImportBatch;

class BatchProcessor
{
    private const CHUNK_SIZE = 50;

    public function __construct(private AccountingApi $accounting) {}

    public function process(ImportBatch $batch): void
    {
        $batch->update(['status' => BatchStatus::Processing]);

        $batch->invoices()
            ->where('status', InvoiceStatus::Valid)
            ->chunkById(self::CHUNK_SIZE, function ($invoices) {
                $results = $this->accounting->createInvoices($invoices);

                foreach ($invoices as $invoice) {
                    $result = $results[$invoice->id] ?? null;

                    if ($result === null) {
                        continue;
                    }

                    if ($result->success) {
                        $invoice->update([
                            'status'       => InvoiceStatus::Posted,
                            'external_ref' => $result->externalRef,
                            'api_response' => $result->rawResponse,
                        ]);
                    } else {
                        $invoice->update([
                            'status'       => InvoiceStatus::Failed,
                            'api_response' => array_merge($result->rawResponse, ['error' => $result->error]),
                        ]);
                    }
                }
            });

        $this->refreshBatchStatus($batch);
    }

    private function refreshBatchStatus(ImportBatch $batch): void
    {
        $posted = $batch->invoices()->where('status', InvoiceStatus::Posted)->count();
        $failed = $batch->invoices()->where('status', InvoiceStatus::Failed)->count();

        $status = match (true) {
            $failed === 0 && $posted > 0 => BatchStatus::Completed,
            $posted === 0 && $failed > 0 => BatchStatus::Failed,
            $failed > 0 && $posted > 0   => BatchStatus::PartiallyCompleted,
            default                      => BatchStatus::Validated,
        };

        $batch->update([
            'status'         => $status,
            'processed_rows' => $posted,
            'failed_rows'    => $failed,
        ]);
    }
}
