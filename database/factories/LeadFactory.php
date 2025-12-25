<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['new', 'in_progress', 'won', 'lost'];

        return [
            'name' => $this->faker->company(),
            'entity' => $this->faker->randomElement([
                'Sales',
                'Marketing',
                'Project Management',
                'Finance',
                'Support',
                'Operations',
            ]),
            'status' => $this->faker->randomElement($statuses),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'notes' => $this->faker->optional()->sentence(10),
        ];
    }
}