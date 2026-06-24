<?php

use App\Enums\InvoiceStatus;
use App\Models\ImportBatch;
use App\Services\BatchValidator;

it('marks invoices valid or invalid based on required fields', function () {
    $batch = ImportBatch::factory()->create();

    $batch->invoices()->create([
        'invoice_number' => 'INV-001',
        'invoice_date'   => '2026-01-15',
        'amount'         => 500,
        'status'         => InvoiceStatus::Pending,
    ]);

    $batch->invoices()->create([
        'invoice_number' => '',
        'invoice_date'   => null,
        'amount'         => 0,
        'status'         => InvoiceStatus::Pending,
    ]);

    app(BatchValidator::class)->validate($batch);

    $valid   = $batch->invoices()->where('status', InvoiceStatus::Valid)->count();
    $invalid = $batch->invoices()->where('status', InvoiceStatus::Invalid)->count();

    expect($valid)->toBe(1);
    expect($invalid)->toBe(1);
});
