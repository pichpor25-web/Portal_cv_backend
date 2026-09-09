<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'location' => fake()->city(),
            'salary_min' => fake()->numberBetween(3000, 5000),
            'salary_max' => fake()->numberBetween(5500, 10000),
            'employment_type' => fake()->randomElement(['Full-time', 'Part-time', 'Contract', 'Remote']),
            'requirements' => fake()->paragraphs(2, true),
            'deadline' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}
