<?php

namespace Database\Seeders;

use App\Actions\CreateTask;
use App\Models\ChangeRequest;
use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskHistoryEvent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $gestor = User::factory()->create([
            'name' => 'Ives Gestor',
            'email' => 'gestor@medicalthermo.com',
            'password' => Hash::make('senha123'),
            'role' => 'gestor',
            'activated_at' => now(),
            'invited_at' => now(),
        ]);

        $liderados = collect([
            'Ana Souza' => 'ana@medicalthermo.com',
            'Bruno Lima' => 'bruno@medicalthermo.com',
            'Carla Mendes' => 'carla@medicalthermo.com',
            'Diego Costa' => 'diego@medicalthermo.com',
        ])->map(fn ($email, $name) => User::factory()->liderado()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('senha123'),
            'activated_at' => now(),
            'invited_at' => now(),
        ]))->values();

        $make = fn (User $responsavel, array $overrides = []) => Task::factory()
            ->withAssignee($responsavel)
            ->create($overrides + ['created_by' => $gestor->id]);

        // --- Sem responsável (aguardando atribuição) ---
        Task::factory()->create([
            'created_by' => $gestor->id,
            'title' => 'Definir escopo do PMOC do Dasa',
            'priority' => 'importante',
            'due_at' => now()->addDays(4),
            'original_due_at' => now()->addDays(4),
        ]);
        Task::factory()->create([
            'created_by' => $gestor->id,
            'title' => 'Levantamento de câmaras frias - Faculdade Pitágoras',
            'priority' => 'normal',
            'due_at' => now()->addDays(10),
            'original_due_at' => now()->addDays(10),
        ]);

        // --- Novas (aguardando o liderado receber) ---
        $make($liderados[0], [
            'title' => 'Manutenção preventiva - ar-condicionado Câmara Municipal',
            'priority' => 'urgente',
            'due_at' => now()->addDays(2),
            'original_due_at' => now()->addDays(2),
            'description' => "Executar preventiva mensal conforme checklist PMOC.\nRegistrar leituras elétricas e de temperatura.",
        ]);
        $make($liderados[1], [
            'title' => 'Orçamento de peças para VRF - Ciências Médicas',
            'priority' => 'normal',
            'due_at' => now()->addDays(5),
            'original_due_at' => now()->addDays(5),
        ]);
        $make($liderados[2], [
            'title' => 'Visita técnica para vistoria de chiller',
            'priority' => 'importante',
            'due_at' => now()->addDays(3),
            'original_due_at' => now()->addDays(3),
        ]);

        // --- Recebidas ---
        $recebida = $make($liderados[0], [
            'title' => 'Troca de filtros - bloco B (Imunizar)',
            'priority' => 'normal',
            'due_at' => now()->addDays(6),
            'original_due_at' => now()->addDays(6),
            'status' => 'recebida',
        ]);
        $make($liderados[3], [
            'title' => 'Revisar contrato de locação de equipamentos',
            'priority' => 'normal',
            'due_at' => now()->addDays(8),
            'original_due_at' => now()->addDays(8),
            'status' => 'recebida',
        ]);

        // --- Em andamento ---
        $andamento1 = $make($liderados[1], [
            'title' => 'Instalação de splitão na recepção - Ferbasa',
            'priority' => 'urgente',
            'due_at' => now()->addDay(),
            'original_due_at' => now()->addDay(),
            'status' => 'em_andamento',
        ]);
        $andamento2 = $make($liderados[2], [
            'title' => 'PMOC mensal - Hospital Ciências Médicas',
            'priority' => 'importante',
            'due_at' => now()->addDays(4),
            'original_due_at' => now()->addDays(4),
            'status' => 'em_andamento',
        ]);
        $atrasada1 = $make($liderados[0], [
            'title' => 'Corrigir vazamento no evaporador - Dasa',
            'priority' => 'critica',
            'due_at' => now()->subDays(2),
            'original_due_at' => now()->subDays(2),
            'status' => 'em_andamento',
            'description' => 'Cliente cobrando posição. Prioridade máxima.',
        ]);
        $atrasada2 = $make($liderados[3], [
            'title' => 'Atualizar ART da obra do laboratório',
            'priority' => 'urgente',
            'due_at' => now()->subDay(),
            'original_due_at' => now()->subDay(),
            'status' => 'em_andamento',
        ]);
        $venceHoje = $make($liderados[1], [
            'title' => 'Enviar relatório fotográfico da semana',
            'priority' => 'urgente',
            'due_at' => now()->addHours(4),
            'original_due_at' => now()->addHours(4),
            'status' => 'em_andamento',
        ]);
        $make($liderados[2], [
            'title' => 'Comprar materiais elétricos para manutenção',
            'priority' => 'normal',
            'due_at' => now()->addDays(2),
            'original_due_at' => now()->addDays(2),
            'status' => 'em_andamento',
        ]);

        // --- Aguardando aprovação ---
        $make($liderados[0], [
            'title' => 'Limpeza técnica dos condensadores - Comércio',
            'priority' => 'importante',
            'due_at' => now()->addDay(),
            'original_due_at' => now()->addDay(),
            'status' => 'aguardando_aprovacao',
        ]);
        $make($liderados[3], [
            'title' => 'Parecer técnico sobre upgrade de câmara fria',
            'priority' => 'normal',
            'due_at' => now()->addDays(2),
            'original_due_at' => now()->addDays(2),
            'status' => 'aguardando_aprovacao',
        ]);

        // --- Bloqueadas ---
        $bloqueada = $make($liderados[1], [
            'title' => 'Reforma do duto principal - Câmara Municipal',
            'priority' => 'critica',
            'due_at' => now()->addDays(3),
            'original_due_at' => now()->addDays(3),
            'status' => 'bloqueada',
            'block_reason' => 'Aguardando liberação do local pela prefeitura',
            'blocked_on' => 'Setor de obras da prefeitura',
        ]);
        $make($liderados[2], [
            'title' => 'Integração do sensor de temperatura com painel',
            'priority' => 'normal',
            'due_at' => now()->addDays(7),
            'original_due_at' => now()->addDays(7),
            'status' => 'bloqueada',
            'block_reason' => 'Fornecedor não enviou o módulo IoT',
            'blocked_on' => 'Fornecedor XPTO',
        ]);

        // --- Reprovadas (com categoria e nota) ---
        $make($liderados[0], [
            'title' => 'Relatório de consumo energético - Maio',
            'priority' => 'normal',
            'due_at' => now()->subDays(1),
            'original_due_at' => now()->subDays(1),
            'status' => 'reprovada',
            'rejection_category' => 'info_incompleta',
            'rejection_note' => 'Faltaram os dados dos blocos C e D. Refazer com leituras completas.',
        ]);
        $make($liderados[3], [
            'title' => 'Desenho técnico do novo layout da oficina',
            'priority' => 'importante',
            'due_at' => now()->addDays(1),
            'original_due_at' => now()->addDays(1),
            'status' => 'reprovada',
            'rejection_category' => 'escopo_mudou',
            'rejection_note' => 'O cliente alterou o layout da sala; redesenhar com nova planta.',
        ]);

        // --- Concluídas ---
        $concluida1 = $make($liderados[0], [
            'title' => 'Manutenção corretiva do ar da sala de servidores',
            'priority' => 'critica',
            'status' => 'concluida',
            'due_at' => now()->subDays(3),
            'original_due_at' => now()->subDays(3),
            'completed_at' => now()->subDays(2),
            'approved_by' => $gestor->id,
        ]);
        $concluida2 = $make($liderados[1], [
            'title' => 'Treinamento da equipe sobre novo checklist PMOC',
            'priority' => 'normal',
            'status' => 'concluida',
            'due_at' => now()->subDays(5),
            'original_due_at' => now()->subDays(5),
            'completed_at' => now()->subDays(5),
            'approved_by' => $gestor->id,
        ]);
        $concluida3 = $make($liderados[2], [
            'title' => 'Cotação de preços para compressores',
            'priority' => 'importante',
            'status' => 'concluida',
            'due_at' => now()->subDays(1),
            'original_due_at' => now()->subDays(1),
            'completed_at' => now()->subHours(20),
            'approved_by' => $gestor->id,
        ]);

        $concluida1->forceFill(['created_at' => now()->subDays(6)])->saveQuietly();
        $concluida2->forceFill(['created_at' => now()->subDays(9)])->saveQuietly();
        $concluida3->forceFill(['created_at' => now()->subDays(4)])->saveQuietly();

        // --- Cancelada ---
        Task::factory()->withAssignee($liderados[3])->create([
            'created_by' => $gestor->id,
            'title' => 'Instalar exaustor no depósito (cancelada pelo cliente)',
            'priority' => 'normal',
            'status' => 'cancelada',
            'due_at' => now()->addDays(5),
            'original_due_at' => now()->addDays(5),
            'deleted_at' => now(),
        ]);

        // --- Recorrentes (cadência fixa, ex.: manutenção preventiva) ---
        (new CreateTask)->execute($gestor, [
            'title' => 'PMOC mensal - Dasa (rotina recorrente)',
            'description' => "Checklist completo de preventiva conforme contrato.\nRegistrar leituras e fotos no relatório.",
            'priority' => 'importante',
            'due_at' => now()->addDays(9)->format('Y-m-d H:i:s'),
            'assigned_to' => $liderados[0]->id,
            'recurrence_frequency' => 'mensal',
            'recurrence_next_at' => now()->addDays(39)->format('Y-m-d H:i:s'),
            'recurrence_series_id' => (string) Str::ulid(),
        ]);
        (new CreateTask)->execute($gestor, [
            'title' => 'Inspeção semanal de ferramental da oficina',
            'priority' => 'normal',
            'due_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'assigned_to' => $liderados[3]->id,
            'recurrence_frequency' => 'semanal',
            'recurrence_next_at' => now()->addDays(9)->format('Y-m-d H:i:s'),
            'recurrence_series_id' => (string) Str::ulid(),
        ]);

        // --- Solicitações de alteração pendentes ---
        ChangeRequest::create([
            'task_id' => $atrasada1->id,
            'requested_by' => $liderados[0]->id,
            'field' => 'due_at',
            'current_value' => $atrasada1->due_at->format('Y-m-d H:i:s'),
            'requested_value' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'justification' => 'Peça do evaporador só chega na quinta; preciso de prazo.',
            'status' => 'pendente',
            'created_at' => now()->subHours(5),
        ]);
        ChangeRequest::create([
            'task_id' => $venceHoje->id,
            'requested_by' => $liderados[1]->id,
            'field' => 'priority',
            'current_value' => 'urgente',
            'requested_value' => 'normal',
            'justification' => 'O relatório pode aguardar; prioridade realocada para a instalação da Ferbasa.',
            'status' => 'pendente',
            'created_at' => now()->subHours(2),
        ]);

        // --- Comentários ---
        Comment::create([
            'task_id' => $andamento1->id,
            'author_id' => $gestor->id,
            'body' => 'Bom dia! Consegue finalizar ainda hoje? O cliente pediu para antecipar.',
            'created_at' => now()->subHours(6),
        ]);
        Comment::create([
            'task_id' => $andamento1->id,
            'author_id' => $liderados[1]->id,
            'body' => 'Já instalei as unidades internas, falta só o teste de pressão. Acho que fecho até o fim da tarde.',
            'created_at' => now()->subHours(3),
        ]);
        Comment::create([
            'task_id' => $bloqueada->id,
            'author_id' => $liderados[1]->id,
            'body' => 'A prefeitura informou que libera o local na segunda-feira.',
            'created_at' => now()->subDay(),
        ]);
        Comment::create([
            'task_id' => $atrasada1->id,
            'author_id' => $liderados[0]->id,
            'body' => 'Solicitei a peça com urgência, chega quinta. Enviei a solicitação de novo prazo.',
            'created_at' => now()->subHours(4),
        ]);

        // --- Histórico inicial realista ---
        foreach ([$andamento1, $atrasada1, $bloqueada, $recebida] as $t) {
            TaskHistoryEvent::create([
                'task_id' => $t->id,
                'actor_id' => $gestor->id,
                'event_type' => 'created',
                'payload' => ['title' => $t->title, 'actor_name' => $gestor->name],
                'created_at' => $t->created_at,
            ]);
        }
    }
}
