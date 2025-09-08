<?php

namespace Database\Factories;

use App\Enums\BulkDownloadStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BulkDownloadJob>
 */
class BulkDownloadJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'user_id' => User::factory(),
            'status' => BulkDownloadStatusEnum::PENDING->value,
            'format' => 'pdf',
            'total_files' => 10,
            'processed_files' => 0,
            'file_path' => null,
            'expires_at' => null,
        ];
    }
}
