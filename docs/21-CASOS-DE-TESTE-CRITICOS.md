# 21 — Casos de Teste Críticos

Estes cenários NUNCA podem falhar — são a linha vermelha do sistema.
Cada um deve virar um teste automatizado nomeado de forma equivalente.

1. **Liderado não vê tarefa de outro liderado**, mesmo acessando a URL
   do detalhe diretamente (deve retornar 403).
2. **Liderado não consegue aprovar a própria tarefa** (rota de
   aprovação deve ser inacessível ao papel liderado).
3. **Liderado não altera prazo/prioridade diretamente** — apenas cria
   uma `change_request`.
4. **Comentário não pode ser editado ou apagado** após criado, mesmo
   via requisição direta à API/rota.
5. **Reprovação sem categoria é rejeitada pela validação** — campo
   obrigatório.
6. **Apenas reprovação "não atende ao solicitado" conta para métrica**
   de desempenho — as outras três categorias não afetam o número.
7. **Tarefa marcada como bloqueada não é contabilizada como atrasada**
   mesmo que o prazo já tenha passado.
8. **Cálculo de atraso respeita o fuso horário do liderado
   responsável**, não o do gestor nem o do servidor.
9. **Usuário desativado não consegue logar**, mas seus dados/comentários
   antigos continuam visíveis no histórico das tarefas.
10. **Nenhuma tabela permite exclusão física** — testar que
    `forceDelete()` não é chamado em nenhum fluxo da aplicação (apenas
    `delete()` com soft delete ativo).
11. **Toda mudança de status gera evento de histórico correspondente**
    — nenhuma transição "silenciosa".
12. **Token de convite expirado não permite definição de senha.**
13. **Upload de arquivo fora dos tipos permitidos (ex: .exe) é
    rejeitado**, independentemente da extensão informada pelo usuário
    (validação por mime type real).
14. **Escalonamento de tarefa crítica (RN-06) só dispara para o
    gestor**, nunca notifica outro liderado.
15. **Cobrança de aprovação parada (RN-07) não altera o status da
    tarefa** — apenas notifica.

