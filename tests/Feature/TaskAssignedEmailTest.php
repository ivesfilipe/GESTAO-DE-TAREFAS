<?php

namespace Tests\Feature;

use App\Mail\TaskAssignedMail;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TaskAssignedEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_envia_email_quando_tarefa_eh_criada_com_responsavel(): void
    {
        Mail::fake();

        $gestor = User::factory()->gestor()->create();
        $liderado = User::factory()->liderado()->create(['email' => 'liderado@teste.com']);

        $this->actingAs($gestor)
            ->post(route('tasks.store'), [
                'title' => 'Tarefa urgente',
                'description' => 'Descrição da tarefa',
                'priority' => 'critica',
                'due_at' => now()->addDay()->format('Y-m-d H:i'),
                'assigned_to' => $liderado->id,
            ])
            ->assertRedirect();

        Mail::assertQueued(TaskAssignedMail::class, function (TaskAssignedMail $mail) use ($liderado) {
            return $mail->hasTo($liderado->email)
                && $mail->user->id === $liderado->id
                && $mail->task->title === 'Tarefa urgente';
        });
    }

    public function test_nao_envia_email_quando_tarefa_eh_criada_sem_responsavel(): void
    {
        Mail::fake();

        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)
            ->post(route('tasks.store'), [
                'title' => 'Tarefa sem responsavel',
                'priority' => 'normal',
                'due_at' => now()->addDay()->format('Y-m-d H:i'),
            ])
            ->assertRedirect();

        Mail::assertNothingQueued();
    }

    public function test_envia_email_quando_tarefa_eh_atribuida(): void
    {
        Mail::fake();

        $gestor = User::factory()->gestor()->create();
        $liderado = User::factory()->liderado()->create(['email' => 'novo@liderado.com']);
        $task = Task::factory()->create(['assigned_to' => null, 'status' => 'nao_atribuida']);

        $this->actingAs($gestor)
            ->patch(route('tasks.assign', $task), ['assigned_to' => $liderado->id])
            ->assertRedirect();

        Mail::assertQueued(TaskAssignedMail::class, function (TaskAssignedMail $mail) use ($liderado, $task) {
            return $mail->hasTo($liderado->email)
                && $mail->user->id === $liderado->id
                && $mail->task->id === $task->id;
        });
    }

    public function test_nao_envia_email_para_liderado_inativo(): void
    {
        Mail::fake();

        $gestor = User::factory()->gestor()->create();
        $lideradoInativo = User::factory()->liderado()->create([
            'email' => 'inativo@teste.com',
            'is_active' => false,
        ]);

        $this->actingAs($gestor)
            ->post(route('tasks.store'), [
                'title' => 'Tarefa para inativo',
                'priority' => 'normal',
                'due_at' => now()->addDay()->format('Y-m-d H:i'),
                'assigned_to' => $lideradoInativo->id,
            ])
            ->assertRedirect();

        Mail::assertNothingQueued();
    }
}
