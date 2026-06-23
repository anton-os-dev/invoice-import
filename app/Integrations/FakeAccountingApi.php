<?php

namespace App\Integrations;

use App\Models\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FakeAccountingApi implements AccountingApi
{
    private const VALID_COMPANY  = 'test-corp';
    private const VALID_LOGIN    = 'test-user';
    private const VALID_PASSWORD = 'test-pass';

    private ?string $sessionId = null;

    private array $existing = [];

    public function __construct(
        private string $company,
        private string $login,
        private string $password,
    ) {}

    public function authenticate(): AuthResult
    {
        $ok = $this->company === self::VALID_COMPANY
            && $this->login === self::VALID_LOGIN
            && $this->password === self::VALID_PASSWORD;

        if (! $ok) {
            return new AuthResult(success: false, error: 'Invalid company, login or password');
        }

        $this->sessionId = 'SESS-' . Str::upper(Str::random(12));

        return new AuthResult(success: true, sessionId: $this->sessionId);
    }

    public function createInvoices(Collection $invoices): array
    {
        if ($this->sessionId === null) {
            $auth = $this->authenticate();

            if (! $auth->success) {
                $results = [];
                foreach ($invoices as $invoice) {
                    $results[$invoice->id] = new InvoiceResult(
                        success: false,
                        error: 'Authentication failed: ' . $auth->error,
                        rawResponse: ['code' => 'AUTH_FAILED'],
                    );
                }
                return $results;
            }
        }

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
