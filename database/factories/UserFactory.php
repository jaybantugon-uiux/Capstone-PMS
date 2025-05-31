<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => null, // All users start unverified by default
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role' => 'client', // Default role
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create admin user - now requires email verification
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'email' => 'admin.dru@gmail.com',
            'email_verified_at' => null, // Requires verification
        ]);
    }

    /**
     * Create employee user - now requires email verification
     */
    public function employee(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'emp',
            'email' => 'emp.dru@gmail.com',
            'email_verified_at' => null, // Requires verification
        ]);
    }

    /**
     * Create finance user - now requires email verification
     */
    public function finance(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'finance',
            'email' => 'finance.dru@gmail.com',
            'email_verified_at' => null, // Requires verification
        ]);
    }

    /**
     * Create project manager user - now requires email verification
     */
    public function projectManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'pm',
            'email' => 'pm.dru@gmail.com',
            'email_verified_at' => null, // Requires verification
        ]);
    }

    /**
     * Create site coordinator user - now requires email verification
     */
    public function siteCoordinator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'sc',
            'email' => 'sc.dru@gmail.com',
            'email_verified_at' => null, // Requires verification
        ]);
    }

    /**
     * Create client user - requires email verification
     */
    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'client',
            'email_verified_at' => null, // Requires verification
        ]);
    }

    /**
     * Create pre-verified admin (for seeding/testing)
     */
    public function adminVerified(): static
    {
        return $this->admin()->verified();
    }

    /**
     * Create pre-verified employee (for seeding/testing)
     */
    public function employeeVerified(): static
    {
        return $this->employee()->verified();
    }

    /**
     * Create pre-verified finance (for seeding/testing)
     */
    public function financeVerified(): static
    {
        return $this->finance()->verified();
    }

    /**
     * Create pre-verified project manager (for seeding/testing)
     */
    public function projectManagerVerified(): static
    {
        return $this->projectManager()->verified();
    }

    /**
     * Create pre-verified site coordinator (for seeding/testing)
     */
    public function siteCoordinatorVerified(): static
    {
        return $this->siteCoordinator()->verified();
    }

    /**
     * Create pre-verified client (for seeding/testing)
     */
    public function clientVerified(): static
    {
        return $this->client()->verified();
    }
}