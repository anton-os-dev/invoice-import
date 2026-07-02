<div class="max-w-4xl mx-auto p-6 space-y-6 text-sm">
    <div class="pl-6">
        <h2 class="text-lg font-semibold">Import invoices (CSV)</h2>
    </div>
    @if (!$this->batch)
    <div class="pl-6">
        <input type="file" wire:model="file" accept=".csv"
               class="block w-full border rounded p-2" />

        @error('file')
        <p class="text-red-600  mt-1">{{ $message }}</p>
        @enderror

        <div wire:loading wire:target="file" class="text-gray-500 mt-2">
            Uploading…
        </div>

        <a href="{{ route('sample.download') }}"
           class="mt-4 block text-sm text-indigo-300 underline">
            Download sample CSV
        </a>

        <button wire:click="import" wire:loading.attr="disabled"
                class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded disabled:opacity-50">
            Import
        </button>
    </div>
    @else
        <div class="rounded-lg shadow p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold">{{ $this->batch->original_filename }}</h3>
                    <p class="text-gray-500">
                        Status: {{ $this->batch->status->value }} ·
                        {{ $this->batch->total_rows }} rows ·
                        {{ $this->batch->valid_rows }} valid ·
                        {{ $this->invalidCount }} invalid ·
                        {{ $this->batch->processed_rows }} posted ·
                        {{ $this->batch->failed_rows }} failed
                    </p>
                </div>
                <div class="space-x-2">
                    <button wire:click="validateBatch" wire:loading.attr="disabled"
                            class="px-3 py-2 bg-green-600 rounded">Validate</button>
                    <button wire:click="process" wire:loading.attr="disabled"
                            class="px-3 py-2 bg-green-600 text-white rounded">Process</button>
                    <button wire:click="resetImport" wire:loading.attr="disabled"
                            class="px-3 py-2 bg-green-600 text-white rounded">Reset</button>
                </div>
            </div>

            <table class="w-full border-t">
                <thead class="text-left text-gray-500">
                <tr>
                    <th class="py-2">Number</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($this->batch->invoices as $invoice)
                    <tr class="border-t">
                        <td class="py-2">{{ $invoice->invoice_number ?: '—' }}</td>
                        <td>{{ $invoice->invoice_date?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $invoice->amount ?? '—' }}</td>
                        <td>{{ $invoice->status->value }}</td>
                        <td class="text-gray-600">
                            @if ($invoice->external_ref)
                                {{ $invoice->external_ref }}
                            @endif
                            @if ($invoice->validation_errors)
                                {{ implode(', ', $invoice->validation_errors) }}
                            @endif
                            @if ($invoice->status === \App\Enums\InvoiceStatus::Failed)
                                {{ $invoice->api_response['error'] ?? '' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
