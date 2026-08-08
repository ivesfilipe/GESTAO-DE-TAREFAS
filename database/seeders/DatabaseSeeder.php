<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $gestor = User::factory()->create([
            'name' => 'Gestor',
            'email' => 'test@example.com',
            'role' => 'gestor',
            'activated_at' => now(),
            'invited_at' => now(),
        ]);

        $liderados = User::factory(4)->liderado()->create();

        Task::factory(5)->withAssignee($liderados[0])->create(['created_by' => $gestor->id]);
        Task::factory(3)->withAssignee($liderados[1])->emAndamento()->create(['created_by' => $gestor->id]);
        Task::factory(2)->withAssignee($liderados[2])->urgente()->create(['created_by' => $gestor->id]);
        Task::factory(2)->vencida()->withAssignee($liderados[0])->create(['created_by' => $gestor->id]);
        Task::factory(1)->aguardandoAprovacao()->withAssignee($liderados[3])->create(['created_by' => $gestor->id]);
        Task::factory(1)->concluida()->withAssignee($liderados[1])->create(['created_by' => $gestor->id]);
    }
}
