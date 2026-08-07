# 27 — Regras de Execução do Agente

> Este é o documento de comportamento. Qualquer agente de IA
> (Antigravity, OpenCode, Claude Code, ou outro) que trabalhar neste
> projeto deve seguir estas regras acima de qualquer instinto próprio
> de "resolver sozinho".

## Regra 1 — Nunca inventar o que não está definido
Se, durante a construção de qualquer fase, o agente encontrar uma
decisão de negócio, nome de campo, rota ou regra que NÃO está descrita
em nenhum documento de `/docs`, ele deve:
1. Parar a implementação daquele ponto específico
2. Registrar a dúvida claramente em `STATUS-DO-PROJETO.md`, seção
   "Pendências e bloqueios conhecidos"
3. Perguntar objetivamente ao operador humano antes de prosseguir
4. Nunca presumir a resposta mais "óbvia" e seguir sem confirmação

Isso vale mesmo que a resposta pareça trivial — é exatamente esse tipo
de suposição pequena e não registrada que causa inconsistência entre
fases construídas em sessões diferentes.

## Regra 2 — Ler antes de escrever
Antes de qualquer linha de código em qualquer sessão, o agente deve ler,
nesta ordem:
1. `STATUS-DO-PROJETO.md` (onde parou, o que falta)
2. O(s) documento(s) de `/docs` relevantes à fase atual
3. `08-MODELO-DE-DADOS.md` e `09-CONTRATOS-DE-ROTAS.md` (nomes exatos)

## Regra 3 — Nunca pular fase
A ordem das fases definida em `FASES-DE-IMPLANTACAO.md` deve ser
seguida estritamente, a menos que o operador humano explicitamente
instrua o contrário por escrito.

## Regra 4 — Atualizar a documentação viva imediatamente
Ao concluir qualquer item (não apenas ao final de uma fase inteira), o
agente atualiza `STATUS-DO-PROJETO.md` e, se aplicável,
`CHANGELOG.md`. Nunca deixar para atualizar "depois" — se a sessão for
interrompida, o próximo agente precisa do estado real.

## Regra 5 — Documentar decisões técnicas novas
Qualquer decisão técnica tomada durante a construção que não estava
prevista nos documentos originais deve virar um novo ADR em
`06-DECISOES-DE-ARQUITETURA-ADR.md`, não apenas viver implícita no
código.

## Regra 6 — Nunca alterar nomes já definidos
Nomes de tabelas, colunas e rotas já definidos em
`08-MODELO-DE-DADOS.md` e `09-CONTRATOS-DE-ROTAS.md` são autoritativos.
Se uma alteração for genuinamente necessária, o agente deve primeiro
atualizar o documento (explicando o motivo) e só então alterar o
código — nunca o inverso.

## Regra 7 — Consistência de responsividade e segurança em toda entrega
Nenhuma tela é considerada entregue sem passar pelos critérios de
`15-RESPONSIVIDADE.md` e `18-SEGURANCA-CHECKLIST-OWASP.md`, mesmo que
o pedido específico da sessão não tenha mencionado isso explicitamente
— esses dois documentos se aplicam a toda e qualquer tela, sempre.

## Regra 8 — Definition of Done é inegociável
Uma fase não é "concluída" até satisfazer integralmente
`22-DEFINITION-OF-DONE.md`. Otimismo não substitui verificação.

## Regra 9 — Perguntas objetivas, não abertas
Quando o agente precisar perguntar algo ao operador humano (Regra 1),
a pergunta deve ser objetiva e, sempre que possível, com opções
concretas — nunca "o que você acha que deveria acontecer aqui?" solto,
e sim "encontrei X não definido; a opção A seria [...], a opção B seria
[...]; qual devo seguir?"

