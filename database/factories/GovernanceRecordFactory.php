<?php

namespace Database\Factories;

use App\Models\GovernanceDocument;
use App\Models\GovernanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GovernanceRecord>
 */
class GovernanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'governance_document_id' => GovernanceDocument::factory(),
            'action' => fake()->randomElement(['created', 'reviewed', 'approved', 'published', 'updated', 'archived']),
            'remarks' => fake()->optional()->sentence(),
            'recorded_at' => now()->subDays(fake()->numberBetween(0, 120)),
            'recorded_by' => User::factory(),
        ];
    }
}
