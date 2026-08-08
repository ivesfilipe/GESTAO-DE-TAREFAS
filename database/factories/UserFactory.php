<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $faker = \Faker\Factory::create('pt_BR');

        return [
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'liderado',
            'timezone' => 'America/Sao_Paulo',
            'is_active' => true,
            'activated_at' => now(),
            'invited_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function gestor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'gestor',
        ]);
    }

    public function liderado(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'liderado',
        ]);
    }
}
