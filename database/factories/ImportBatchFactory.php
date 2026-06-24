<?php

namespace Database\Factories;

use App\Enums\BatchStatus;
use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportBatch>
 */
class ImportBatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'original_filename' => 'test.csv',
            'status'            => BatchStatus::Pending,
            'total_rows'        => 0,
            'valid_rows'        => 0,
            'processed_rows'    => 0,
            'failed_rows'       => 0,
        ];
    }
}
