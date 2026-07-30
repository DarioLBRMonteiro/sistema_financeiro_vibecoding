# Plano de Desenvolvimento - Gestão Financeira Simples

Este plano define as fases de implementação incremental para o sistema **Gestão Financeira Simples**, em conformidade com as diretrizes do Documento de Especificação Funcional (FSD) e o Guia de Design System (DESIGN).

---

## Fase 1 - Infraestrutura e Base do Projeto
* **Objetivo:** Estabelecer a estrutura inicial de arquivos e diretórios, o banco de dados, os arquivos de configuração, helpers basais de segurança/logs e os arquivos vivos de controle.
* **Arquivos e Diretórios:**
  * `config/config.php`
  * `config/database.php`
  * `database/schema.sql`
  * `app/helpers/Sanitizer.php`, `app/helpers/Logger.php`, `app/helpers/CSRF.php`, `app/helpers/Auth.php`
  * `public/index.php`
  * `.htaccess` (raiz, config, logs)
  * `public/assets/css/`, `public/assets/js/`, `public/assets/images/`
* **Checklist:**
  * [x] Criar estrutura física inicial de pastas do projeto.
  * [x] Criar e configurar o DDL no arquivo `database/schema.sql`.
  * [x] Implementar `config/config.php` e `config/database.php`.
  * [x] Implementar helpers de sanitização, logs, segurança CSRF e validação inicial de sessão.
  * [x] Configurar arquivos de reescrita de URL e proteção de pastas `.htaccess`.
  * [x] Configurar o Front Controller `public/index.php` básico.
  * [x] Copiar assets de imagens (`cursoemvideo-logo.ico`) para `public/assets/images/`.
  * [x] Baixar ou estruturar os arquivos locais do Bootstrap 5 e Chart.js nas pastas correspondentes de assets.
* **Critérios de Pronto:**
  * Estrutura de diretórios criada com sucesso.
  * O script DDL do banco de dados executa sem erros em um ambiente MySQL 8.x.
  * O Front Controller responde a requisições iniciais sem expor erros na tela.
  * O acesso aos arquivos de `config/` e `logs/` via navegador é bloqueado.

---

## Fase 2 - Layout Base e Templates (Design System)
* **Objetivo:** Desenvolver o layout base da aplicação utilizando Bootstrap 5 local e aplicar o design system *Admin Logic* especificado em `docs/DESIGN.md`.
* **Arquivos e Diretórios:**
  * `public/assets/css/admin-logic.css`
  * `app/views/templates/header.php`
  * `app/views/templates/footer.php`
  * `app/views/templates/sidebar.php`
  * `public/assets/js/app.js`
* **Checklist:**
  * [ ] Escrever o CSS customizado em `public/assets/css/admin-logic.css` aplicando os tokens de cores, tipografia (Public Sans/Inter), espaçamentos e shapes definidos em `docs/DESIGN.md`.
  * [ ] Criar o cabeçalho (`header.php`), barra lateral de navegação (`sidebar.php`) e rodapé (`footer.php`) com responsividade e suporte a numerais tabulares para dados numéricos.
  * [ ] Criar o arquivo `app.js` para manipulação comum de UI e inicializações dinâmicas.
* **Critérios de Pronto:**
  * Layout base responsivo e renderizando perfeitamente no navegador.
  * Cores, fontes e arredondamentos de 4px aplicados corretamente nos botões, inputs e status pills (100px) conforme o DESIGN.md.
  * Ausência de referências a CDNs externas de CSS/JS de execução no código dos templates.

---

## Fase 3 - Módulo de Autenticação e Sessão
* **Objetivo:** Implementar o fluxo completo de cadastro, login, redefinição de senha e alteração de senha por usuário autenticado, com logs de auditoria e contingências de segurança.
* **Arquivos e Diretórios:**
  * `app/models/Usuario.php`
  * `app/controllers/AuthController.php`
  * `app/views/auth/login.php`, `app/views/auth/cadastro.php`, `app/views/auth/recuperar-senha.php`, `app/views/auth/redefinir-senha.php`
  * `logs/security.log`, `logs/email_simulation.log`
* **Checklist:**
  * [ ] Implementar a model `Usuario.php` para operações com a tabela `usuarios` usando PDO e prepared statements.
  * [ ] Implementar `AuthController.php` com ações para login (com `session_regenerate_id(true)`), logout, cadastro e recuperação de senha.
  * [ ] Criar as telas de login, cadastro, recuperação e redefinição de senha com validações rigorosas (tamanho da senha, e-mail único, etc.) revalidadas no back-end.
  * [ ] Adicionar simulação de envio de e-mails de redefinição de senha salvando os tokens em `logs/email_simulation.log`.
  * [ ] Integrar proteção CSRF a todos os formulários e rotas POST.
  * [ ] Registrar logs de auditoria de segurança (tentativas de login, redefinição de senha) em `logs/security.log`.
* **Critérios de Pronto:**
  * Cadastro de novos usuários funcional com criptografia `PASSWORD_BCRYPT`.
  * Login bloqueando e-mails ou senhas incorretas e redirecionando corretamente para o dashboard.
  * Recuperação de senha gerando tokens com expiração de 1 hora e gravando com sucesso no arquivo de log de simulação.
  * Validação de token CSRF barrando requisições não autorizadas com HTTP 403 e gravando em log.

---

## Fase 4 - Dashboard e Extrato (Leitura)
* **Objetivo:** Construir o painel orçamentário principal e a página de extrato financeiro histórico para exibição dos dados de lançamentos do mês selecionado com isolamento total de usuário.
* **Arquivos e Diretórios:**
  * `app/models/Lancamento.php`
  * `app/controllers/DashboardController.php`, `app/controllers/LancamentosController.php`
  * `app/views/dashboard/index.php`, `app/views/extrato/index.php`
* **Checklist:**
  * [ ] Criar a model `Lancamento.php` implementando métodos de leitura com cláusula obrigatória `usuario_id = :usuario_id` e filtrando registros ativos (`deleted_at IS NULL`).
  * [ ] Implementar `DashboardController.php` calculando a soma de receitas, despesas e saldo líquido do mês.
  * [ ] Desenvolver a tela de Dashboard com cartões KPI (receita, despesa, saldo) e lógica para destacar saldo negativo na cor vermelha (`#ba1a1a`).
  * [ ] Renderizar os gráficos usando `Chart.js` local (Fluxo de Caixa dos últimos meses e Gastos por Categoria do mês vigente).
  * [ ] Desenvolver a tela de Extrato com a listagem de lançamentos na tabela, com paginação/filtro temporal, busca textual e filtro por categoria.
* **Critérios de Pronto:**
  * O saldo mensal calculado reflete fielmente as receitas e despesas filtradas.
  * O destaque visual do saldo negativo (vermelho de erro) funciona corretamente em tempo de execução.
  * Gráficos são exibidos e populados dinamicamente com dados reais do usuário logado.
  * Extrato funciona com navegação mês a mês e isolamento rigoroso de registros por usuário.

---

## Fase 5 - CRUD de Lançamentos e Modais
* **Objetivo:** Permitir a inserção, edição e exclusão lógica (*soft delete*) de lançamentos financeiros por meio de modais interativos integrados.
* **Arquivos e Diretórios:**
  * `app/controllers/LancamentosController.php` (ações de mutação)
  * Modais inseridos em `app/views/extrato/index.php` ou templates associados
* **Checklist:**
  * [ ] Implementar ações de inserção e atualização em `Lancamento.php` (Model) e `LancamentosController.php`, com validações estritas de inputs (valor positivo, tipos permitidos, data válida).
  * [ ] Desenvolver o modal de cadastro de novo lançamento e o modal de edição de lançamento preenchendo os dados existentes.
  * [ ] Desenvolver o modal de confirmação de exclusão exibindo a descrição e valor do lançamento.
  * [ ] Implementar a exclusão lógica (*Soft Delete*) atualizando a coluna `deleted_at = NOW()`.
* **Critérios de Pronto:**
  * Cadastro e edição de lançamentos funcionam com validação de dados no servidor e no cliente.
  * A exclusão lógica atualiza o banco de dados sem apagar fisicamente a linha, e o lançamento deixa de aparecer na listagem regular.
  * O modal de confirmação de exclusão previne exclusões acidentais.

---

## Fase 6 - Categorias Personalizadas e Exclusão com Modal
* **Objetivo:** Adicionar funcionalidade para criação e remoção lógica de categorias personalizadas do usuário, assegurando a integridade dos dados históricos nos lançamentos antigos.
* **Arquivos e Diretórios:**
  * `app/models/Categoria.php`
  * `app/controllers/CategoriasController.php`
  * `app/views/categorias/index.php`
* **Checklist:**
  * [ ] Implementar a model `Categoria.php` para gerenciar categorias personalizadas (`usuario_id = :usuario_id`) e categorias padrão do sistema (`usuario_id IS NULL`).
  * [ ] Desenvolver a tela de Categorias dividida em inserção de nova categoria e listagem das categorias existentes (bloqueando a exclusão de categorias padrão).
  * [ ] Criar o modal de confirmação de exclusão de categoria personalizada alertando que lançamentos passados manterão o nome da categoria no histórico.
  * [ ] Implementar a exclusão lógica da categoria e verificar que no extrato ela continua aparecendo por meio de um `LEFT JOIN` nas consultas históricas.
* **Critérios de Pronto:**
  * Inserção de categorias personalizadas validando duplicidade de nome e tipo de categoria ativa de forma case-insensitive.
  * Exclusão lógica de categoria concluída, com a categoria sumindo das opções de cadastro de novos lançamentos.
  * Lançamentos históricos vinculados a categorias excluídas continuam a exibir seu nome na tabela do extrato.

---

## Fase 7 - Perfil e Segurança da Conta
* **Objetivo:** Permitir ao usuário autenticado a alteração de sua própria senha de acesso, com validações rigorosas e log de segurança detalhado.
* **Arquivos e Diretórios:**
  * `app/controllers/PerfilController.php`
  * `app/views/perfil/senha.php`
  * `logs/security.log`
* **Checklist:**
  * [ ] Criar a página ou modal de alteração de senha em `app/views/perfil/senha.php`.
  * [ ] Implementar `PerfilController.php` validando a senha atual do usuário (`password_verify`), a força da nova senha (alfanumérica, min. 8 caracteres) e a confirmação de senha.
  * [ ] Salvar a nova senha criptografada com `password_hash` no banco.
  * [ ] Registrar o evento de sucesso de alteração de senha em `logs/security.log`.
* **Critérios de Pronto:**
  * A alteração de senha só ocorre se a senha atual digitada estiver correta.
  * O sistema impede senhas fáceis ou curtas, revalidando tudo no servidor.
  * A operation gera uma linha detalhada de auditoria no log de segurança contendo o ID do usuário e o IP.

---

## Fase 8 - Testes Finais e Validação de Segurança
* **Objetivo:** Realizar auditorias no sistema contra falhas como injeção SQL, XSS, CSRF, verificar o isolamento completo de dados de usuários diferentes e testar contingência de logs de erro.
* **Checklist:**
  * [ ] Executar testes manuais de injeção SQL nos inputs de busca e cadastro.
  * [ ] Testar escape de HTML (XSS) injetando scripts no campo descrição do lançamento.
  * [ ] Validar a proteção contra CSRF enviando formulários com tokens alterados.
  * [ ] Garantir o isolamento de dados forçando o acesso de IDs de lançamentos de outros usuários via GET/POST para verificar que o sistema barra a visualização/edição.
  * [ ] Verificar que erros de banco de dados não são exibidos em tela no ambiente de produção e que o arquivo `logs/error.log` é gravado adequadamente.
* **Critérios de Pronto:**
  * Nenhuma vulnerabilidade crítica (SQLi, XSS, CSRF) detectada.
  * O isolamento de dados entre usuários bloqueia qualquer acesso cruzado.
  * Erros internos do PHP e MySQL são devidamente suprimidos em tela e reportados nos arquivos de logs protegidos.
