# 03 — Regras de Negócio

Estas regras foram desenhadas para "blindar" tanto a empresa (gestor)
quanto o colaborador (liderado) — nenhuma cobrança automática é justa
sem registrar os fatores fora do controle do liderado, e nenhuma
alteração de prazo/prioridade pode ser feita unilateralmente pelo
liderado.

## RN-01 — Cálculo de atraso
Uma tarefa é "atrasada" quando a data/hora atual ultrapassa o campo
`prazo_em` da tarefa, calculado no fuso horário cadastrado do liderado
responsável (não do gestor). O sistema envia aviso automático 2h antes
do vencimento. Ao ultrapassar o prazo, o status derivado muda
automaticamente — nenhum humano decide isso manualmente.

## RN-02 — Alteração de prazo e prioridade
Somente o gestor pode alterar diretamente prazo ou prioridade de uma
tarefa. O liderado pode registrar uma "solicitação de alteração" com
justificativa obrigatória; essa solicitação gera um evento de histórico
e uma notificação ao gestor, que aprova ou recusa. Nenhuma alteração
ocorre sem essa aprovação.

## RN-03 — Tarefa sem responsável
Tarefas podem ser criadas sem responsável definido (status "Não
atribuída"). Enquanto nesse estado, não contam como carga de trabalho
de ninguém nem geram cobrança de prazo.

## RN-04 — Reprovação categorizada
Toda reprovação exige um motivo categorizado: "não atende ao
solicitado", "escopo mudou", "informação incompleta" ou "outro".
Apenas reprovações por "não atende ao solicitado" entram na métrica de
desempenho do liderado — isso evita penalizar o colaborador por
mudanças de ideia do próprio gestor.

## RN-05 — Bloqueio de tarefa
Ao marcar uma tarefa como bloqueada, o liderado é obrigado a informar o
motivo e de quem/o que ela depende (outro liderado, o próprio gestor,
ou um terceiro externo). O tempo em estado bloqueado não conta como
atraso do responsável, mas permanece visível e rastreável pelo gestor —
evitando tanto a penalização injusta quanto o uso do bloqueio como
desculpa oculta.

## RN-06 — Escalonamento por inação
Uma tarefa com prioridade Crítica que não mude de status por mais de 4h
gera alerta automático ao **gestor**, não ao liderado — a lógica é que
o acompanhamento de itens críticos é responsabilidade compartilhada, e
o painel do gestor já expõe isso.

## RN-07 — Cobrança de aprovação parada
Uma tarefa em "Aguardando aprovação" por mais de 3 dias sem resposta do
gestor gera notificação automática de cobrança ao gestor. O status não
muda sozinho — apenas o gestor decide.

## RN-08 — Integridade de comentários
Comentários são imutáveis após o envio (sem edição ou exclusão). Um
erro se corrige com um novo comentário, preservando a integridade do
histórico como prova de comunicação para ambos os lados.

## RN-09 — Visibilidade entre liderados
Um liderado nunca visualiza tarefas atribuídas a outro liderado. Apenas
o gestor tem visão consolidada de toda a equipe.

## RN-10 — Ciclo de conclusão
O liderado nunca marca uma tarefa como definitivamente concluída. Ele
"solicita conclusão" (→ Aguardando aprovação); apenas o gestor,
aprovando, move a tarefa para "Concluída". Se reprovada, a tarefa volta
ao liderado, mantendo o vínculo histórico da tentativa anterior.

## RN-11 — Retenção de usuários desligados
Usuários nunca são apagados fisicamente. Ao desativar um usuário, o
sistema deve alertar o gestor sobre tarefas abertas vinculadas a ele
para reatribuição manual — nada fica "órfão" silenciosamente.

## RN-12 — Nada é apagado de verdade
Tarefas canceladas, usuários desativados, nenhum registro sai fisicamente
do banco — tudo é soft delete, preservando o histórico completo para
consulta futura ("meses depois, será possível entender exatamente o que
aconteceu").

## RN-13 — Escopo da equipe
Cada liderado pertence a um gestor por `manager_id`. Dashboard, relatórios, API, perfil e ferramentas do Copiloto devem filtrar sempre por esse vínculo. Gestores não podem consultar recursos de outra equipe.

## RN-14 — Uso seguro de IA
Provider externo só recebe contexto após confirmação administrativa de ZDR. Sem confirmação, a chamada externa é bloqueada antes do prompt. Logs registram metadados técnicos, nunca prompts, respostas, anexos ou segredos.

## RN-15 — Métricas gerenciais
Tempo de ciclo é `completed_at - created_at`. Atraso de entrega compara a conclusão ao prazo. A taxa de reprovação de desempenho considera somente `rejection_category = nao_atende`; demais categorias são mantidas como indicadores operacionais.
