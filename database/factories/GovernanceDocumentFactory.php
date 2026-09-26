<?php

namespace Database\Factories;

use App\Models\GovernanceDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GovernanceDocument>
 */
class GovernanceDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'reference_no' => strtoupper(fake()->bothify('GOV-####-??')),
            'document_type' => fake()->randomElement(['policy', 'regulation', 'minutes', 'resolution', 'communication', 'special_resolution', 'board_reviewed']),
            'category' => fake()->randomElement(GovernanceDocument::allowedCategories()),
            'version' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement(['draft', 'active', 'archived', 'expired']),
            'issued_at' => now()->subDays(fake()->numberBetween(1, 400)),
            'effective_at' => now()->subDays(fake()->numberBetween(1, 300)),
            'expires_at' => now()->addDays(fake()->numberBetween(30, 400)),
            'file_path' => 'governance/'.fake()->uuid().'.pdf',
            'notes' => fake()->optional()->paragraph(),
            'uploaded_by' => User::factory(),
        ];
    }
}
