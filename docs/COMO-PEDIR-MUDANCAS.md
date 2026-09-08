# Como Pedir Mudanças no Sistema - Guia para o Usuário

Este guia foi criado para ajudar você a solicitar alterações, novas funcionalidades, correções de bugs e ajustes no sistema **Gestão Financeira Simples** de forma clara e segura ao trabalhar com assistentes de Inteligência Artificial (IA).

---

## 1. Como se Comunicar com a IA

Para obter os melhores resultados sem quebrar a segurança ou o funcionamento do sistema, siga estas recomendações simples:

1. **Inicie o chat pedindo a leitura do contexto:** Sempre peça à IA para ler os arquivos de documentação vivos do projeto antes de começar a codificar.
2. **Explique o que você quer em termos simples:** Descreva o problema que deseja resolver ou a funcionalidade que deseja adicionar.
3. **Peça um plano antes de executar:** Solicite que a IA explique o que pretende alterar antes de mexer nos arquivos.
4. **Valide a entrega:** Peça à IA para explicar como testar e confirmar a alteração no navegador.

---

## 2. Checklist Antes de Aceitar uma Alteração

Antes de considerar uma mudança pronta ou aprovada, confirme se a IA seguiu estes pontos:

* [ ] Preservou a stack nativa em PHP 8.x e MySQL (sem instalar frameworks ou Composer/NPM).
* [ ] Manteve o isolamento por usuário (`usuario_id = :usuario_id`) nas consultas do banco.
* [ ] Manteve a proteção contra injeção SQL (PDO Prepared Statements).
* [ ] Manteve a proteção contra XSS (`Sanitizer::e()`) e CSRF (`CSRF::validateToken()`).
* [ ] Seguiu as cores, fontes e arredondamentos (4px) do Design System em `docs/DESIGN.md`.
* [ ] Atualizou o arquivo `docs/STATUS.md`.
* [ ] Registrou eventuais problemas resolvidos em `docs/ERROS.md`.

---

## 3. Prompts Prontos para Copiar e Usar

Abaixo estão modelos de solicitações prontas que você pode copiar e colar ao conversar com a IA.

### 1. Adicionar um campo em um cadastro
> "Por favor, leia integralmente os arquivos `AGENTS.md`, `docs/MANUTENCAO.md`, `docs/FSD.md`, `docs/DESIGN.md` e `docs/STATUS.md`. Quero adicionar um novo campo 'Observações' (texto opcional) no cadastro de lançamentos financeiros. Atualize o script do banco (`database/schema.sql`), a model `Lancamento.php`, o controller `LancamentosController.php` e a view do extrato/modal (`app/views/extrato/index.php`). Garanta que o campo seja sanitizado e protegido contra XSS."

### 2. Criar uma nova tela
> "Por favor, leia `AGENTS.md`, `docs/MANUTENCAO.md`, `docs/FSD.md` e `docs/DESIGN.md`. Quero criar uma nova tela chamada 'Metas Financeiras' para o usuário definir objetivos de economia mensal. Crie o controller `MetasController.php`, a view em `app/views/metas/index.php`, a rota no Front Controller `public/index.php` e adicione o link no menu lateral (`sidebar.php`). Respeite a autenticação e a proteção CSRF."

### 3. Corrigir um erro (Bugfix)
> "Por favor, leia `AGENTS.md`, `docs/MANUTENCAO.md`, `docs/STATUS.md` e `docs/ERROS.md`. Ocorreu o seguinte erro ao clicar em [descreva o botão ou tela]: [cole o erro ou mensagem da tela]. Investigue o arquivo de log em `logs/error.log`, identifique a causa raiz e corrija mantendo o padrão do projeto. Atualize `docs/ERROS.md` com o diagnóstico e a solução."

### 4. Alterar uma regra de negócio
> "Por favor, leia `AGENTS.md`, `docs/MANUTENCAO.md`, `docs/FSD.md` e `docs/STATUS.md`. Preciso alterar a regra de cálculo do Saldo do Mês no Dashboard para considerar apenas os lançamentos com status 'PAGO', ignorando os lançamentos 'PENDENTE'. Atualize a consulta no model `Lancamento.php` e explique o impacto dessa mudança nos KPI cards."

### 5. Ajustar o visual (Design System)
> "Por favor, leia `AGENTS.md`, `docs/MANUTENCAO.md` e `docs/DESIGN.md`. Ajuste os botões e os campos da tela de cadastro de usuários (`app/views/auth/cadastro.php`) para seguir rigorosamente o Design System Admin Logic: bordas de 4px (`0.25rem`), cor primária `#0f2d7b` e fonte Inter."

### 6. Criar um relatório ou filtro
> "Por favor, leia `AGENTS.md`, `docs/MANUTENCAO.md`, `docs/FSD.md` e `docs/STATUS.md`. Adicione um novo filtro na tela de Extrato (`app/views/extrato/index.php`) para permitir filtrar os lançamentos pela Forma de Pagamento (Dinheiro, Pix, Débito, Crédito). Atualize o `LancamentosController.php` e o model `Lancamento.php` preservando o isolamento de dados por usuário."

### 7. Revisar a segurança após uma alteração
> "Por favor, leia `AGENTS.md`, `docs/MANUTENCAO.md` e `docs/STATUS.md`. Faça uma revisão de segurança na funcionalidade que acabamos de alterar. Verifique se há riscos de SQL Injection, XSS, quebra de CSRF ou vazamento de dados entre usuários diferentes. Teste com o PHP Lint e confirme se tudo está protegido."

### 8. Preparar para Commit e Versionamento
> "Por favor, verifique o `git status`, confirme que nenhum arquivo com credenciais ou dados sensíveis será commitado e faça o commit das nossas alterações com uma mensagem clara sobre o que foi feito. Depois, lembre-me dos comandos de `git push`."
