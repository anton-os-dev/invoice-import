<?php

namespace App\Services;

use App\Enums\BatchStatus;
use App\Enums\InvoiceStatus;
use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Support\Carbon;

class BatchImporter
{
    public function import(User $user, string $path, string $originalFilename): ImportBatch
    {
        $batch = $user->importBatches()->create([
            'original_filename' => $originalFilename,
            'status'            => BatchStatus::Pending,
        ]);

        $total = 0;

        foreach ($this->readRows($path) as $row) {
            $batch->invoices()->create([
                'invoice_number' => $row['invoice_number'] ?? null,
                'invoice_date'   => $this->parseDate($row['invoice_date'] ?? null),
                'amount'         => $this->parseAmount($row['amount'] ?? null),
                'currency'       => $row['currency'] ?? 'USD',
                'status'         => InvoiceStatus::Pending,
            ]);
            $total++;
        }

        $batch->update(['total_rows' => $total]);

        return $batch;
    }

    /**
     * @return array<int, array<string, string>>  строки файла как ассоциативные массивы
     */
    private function readRows(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return $rows;
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            return $rows;
        }

        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($header)) {
                continue;
            }
            $rows[] = array_combine($header, $data);
        }

        fclose($handle);

        return $rows;
    }

    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseAmount(?string $value): ?float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $clean = str_replace([',', ' '], '', $value);

        return is_numeric($clean) ? (float) $clean : null;
    }
}
