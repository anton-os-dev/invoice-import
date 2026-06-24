<?php

use App\Enums\BatchStatus;
use App\Enums\InvoiceStatus;
use App\Models\ImportBatch;
use App\Services\BatchProcessor;

it('posts valid invoices and marks batch completed', function () {
    $batch = ImportBatch::factory()->create();

    $batch->invoices()->create([
        'invoice_number' => 'INV-100',
        'invoice_date'   => '2026-01-15',
        'amount'         => 250,
        'status'         => InvoiceStatus::Valid,
    ]);

    app(BatchProcessor::class)->process($batch);

    $invoice = $batch->invoices()->first();

    expect($invoice->status)->toBe(InvoiceStatus::Posted);
    expect($invoice->external_ref)->not->toBeNull();
    expect($batch->fresh()->status)->toBe(BatchStatus::Completed);
});
