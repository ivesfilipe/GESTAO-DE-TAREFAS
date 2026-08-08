<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $faker = \Faker\Factory::create('pt_BR');

        return [
            'title' => $faker->sentence(3),
            'description' => $faker->paragraph(),
            'created_by' => User::factory()->gestor(),
            'assigned_to' => null,
            'priority' => $faker->randomElement(['normal', 'importante', 'urgente', 'critica']),
            'status' => 'nao_atribuida',
            'due_at' => now()->addDays($faker->numberBetween(1, 30)),
            'original_due_at' => now()->addDays($faker->numberBetween(1, 30)),
        ];
    }

    public function withAssignee(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user?->id ?? User::factory()->liderado(),
            'status' => 'nova',
        ]);
    }

    public function emAndamento(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'em_andamento']);
    }

    public function aguardandoAprovacao(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'aguardando_aprovacao']);
    }

    public function concluida(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'concluida',
            'completed_at' => now(),
        ]);
    }

    public function bloqueada(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'bloqueada',
            'block_reason' => 'Aguardando fornecedor',
            'blocked_on' => 'Fornecedor XPTO',
        ]);
    }

    public function urgente(): static
    {
        return $this->state(fn (array $attributes) => ['priority' => 'urgente']);
    }

    public function critica(): static
    {
        return $this->state(fn (array $attributes) => ['priority' => 'critica']);
    }

    public function vencida(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => now()->subDays(2),
            'original_due_at' => now()->subDays(2),
        ]);
    }

    public function venceHoje(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => now()->addHours(4),
            'original_due_at' => now()->addHours(4),
        ]);
    }
}
