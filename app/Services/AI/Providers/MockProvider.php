<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\AIProviderInterface;
use App\DTO\AI\AIRequest;
use App\DTO\AI\AIResponse;

class MockProvider implements AIProviderInterface
{
    public function name(): string
    {
        return 'mock';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function complete(AIRequest $request): AIResponse
    {
        $content = $this->generateContent($request);

        return new AIResponse(
            content: $content,
            finishReason: 'stop',
            promptTokens: $this->estimateTokens($request->system.$request->user),
            completionTokens: $this->estimateTokens($content),
        );
    }

    private function generateContent(AIRequest $request): string
    {
        $lower = mb_strtolower($request->user.' '.$request->system);

        if (str_contains($lower, 'cobrança') || str_contains($lower, 'cobranca') || str_contains($lower, 'mensagem de cobrança')) {
            $taskTitle = $this->extractTaskTitle($request->user.' '.$request->system);

            return "Rascunho de cobrança (modo simulação):\n\nPreciso de uma posição sobre: {$taskTitle}\n\nO prazo registrado está próximo e ainda não há atualização conclusiva na tarefa.\n\nMe retorne com:\n- status atual;\n- eventual pendência;\n- previsão de conclusão.\n\nEste rascunho não foi enviado automaticamente.";
        }

        if (str_contains($lower, 'rascunho') || str_contains($lower, 'delega') || str_contains($lower, 'responsável') || str_contains($lower, 'assignee')) {
            return json_encode([
                'title' => 'Obter retorno sobre a demanda solicitada',
                'task_type' => 'demanda',
                'priority' => 'normal',
                'due_at_suggestion' => 3,
                'due_at_reason' => 'Prazo padrão para acompanhamento de demanda',
                'recommended_assignee_id' => null,
                'assignee_reason' => 'Sugestão baseada na menor carga de tarefas ativas',
                'description' => "Objetivo: concluir a demanda com clareza.\n\nContexto: ação necessária para manter o fluxo.\n\nEntregáveis:\n- Resultado principal concluído\n- Evidências registradas\n- Comunicação com as partes interessadas",
                'acceptance_criteria' => [
                    'Resultado concluído e revisado',
                    'Evidências anexadas',
                    'Partes interessadas informadas',
                ],
                'expected_evidence' => [
                    'Print ou arquivo de comprovação',
                    'Link ou referência do resultado',
                ],
                'checkpoints' => [
                    'Entender escopo',
                    'Executar atividade principal',
                    'Revisar e registrar',
                ],
                'missing_information' => [
                    'Responsável exato, caso não selecionado',
                    'Prazo crítico, caso exista',
                ],
                'confidence' => 'media',
            ], JSON_UNESCAPED_UNICODE);
        }

        if (str_contains($lower, 'sugira tarefas') || str_contains($lower, 'suggestions')) {
            return json_encode([
                'suggestions' => [
                    [
                        'category' => 'demanda',
                        'title' => 'Revisar prioridades do trimestre',
                        'task_type' => 'demanda',
                        'objective' => 'Alinhar entregas com objetivos do time.',
                        'reason' => 'Pouca movimentação em tarefas estratégicas.',
                        'periodicity' => null,
                        'priority' => 'normal',
                    ],
                    [
                        'category' => 'responsabilidade',
                        'title' => 'Atualizar documentação de processos',
                        'task_type' => 'responsabilidade',
                        'objective' => 'Manter base de conhecimento atualizada.',
                        'reason' => 'Documentação antiga pode gerar retrabalho.',
                        'periodicity' => 'mensal',
                        'priority' => 'importante',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE);
        }

        if (str_contains($lower, 'risco') || str_contains($lower, 'radar') || str_contains($lower, 'atrasada')) {
            return "Radar de risco (modo simulação):\n- Atenção para tarefas sem movimentação há mais de 3 dias\n- Tarefas atrasadas devem ser revisitadas em reunião de priorização\n- Nenhuma ação automática será tomada.";
        }

        if (str_contains($lower, 'resumo') || str_contains($lower, 'perfil') || str_contains($lower, 'performance')) {
            return "Resumo gerencial (modo simulação):\n- Carga atual distribuída entre os liderados\n- Entregas no prazo em nível estável\n- Recomendação: revisar tarefas sem atualização há mais de uma semana.";
        }

        if (str_contains($lower, 'descricao') || str_contains($lower, 'descrição') || str_contains($lower, 'briefing')) {
            return "Descrição sugerida (modo simulação):\n\nObjetivo: concluir a entrega com qualidade e clareza.\n\nContexto: esta tarefa conecta-se ao fluxo do setor e impacta as próximas etapas.\n\nEntregáveis:\n- Resultado principal concluído e revisado\n- Evidências ou anexos registrados\n- Comunicação com as partes interessadas\n\nCritério de sucesso: qualquer pessoa do time entende o resultado apenas lendo esta tarefa.";
        }

        if (str_contains($lower, 'divid') || str_contains($lower, 'passo') || str_contains($lower, 'subtarefa')) {
            return "Sugestão de divisão (modo simulação):\n1. Levantar requisitos e informações necessárias\n2. Executar a atividade principal\n3. Revisar resultado e validar com as partes interessadas\n4. Registrar conclusão e documentar aprendizados.";
        }

        return 'Resposta em modo simulação: nenhuma informação real foi enviada a API externa. Configure GROQ_API_KEY ou outro provider para ativar respostas geradas por IA.';
    }

    private function extractTaskTitle(string $text): string
    {
        if (preg_match('/Tarefa:\s*(.+)/u', $text, $matches)) {
            return trim($matches[1]);
        }

        return 'a tarefa em questão';
    }

    private function estimateTokens(string $text): int
    {
        return (int) ceil(mb_strlen($text) / 4);
    }
}
