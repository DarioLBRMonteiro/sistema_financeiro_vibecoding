# Guia de Manutenção e Evolução - Gestão Financeira Simples

Este documento contém as diretrizes técnicas e operacionais para manutenção, correção e evolução do sistema **Gestão Financeira Simples**. Ele deve ser consultado por desenvolvedores e assistentes de Inteligência Artificial antes de qualquer modificação no código-fonte.

---

## 1. Visão Geral do Sistema

### 1.1 O que o sistema faz
O **Gestão Financeira Simples** é uma aplicação web de controle orçamentário pessoal. Ele permite registrar entradas (receitas) e saídas (despesas), categorizar movimentações, alternar o status dos lançamentos ("Pago" ou "Pendente"), filtrar o histórico mensalmente e visualizar um painel orçamentário (*dashboard*) com indicadores de saldo e gráficos em tempo real.

### 1.2 Para quem foi criado
Pessoas físicas (Perfil funcional: **Usuário Final**) que buscam uma ferramenta prática, ágil e visualmente objetiva para gerenciar seu orçamento pessoal diário, sem complicação ou terminologia contábil complexa.

### 1.3 Problemas que resolve
Centraliza a gestão financeira familiar/pessoal, calcula automaticamente receitas, despesas e saldo líquido do mês selecionado, alerta visualmente em vermelho quando o saldo fica negativo e mantém o histórico das movimentações mesmo quando categorias antigas são excluídas.

### 1.4 Módulos Principais
1. **Autenticação e Sessão:** Cadastro, Login, Logout, Solicitação e Redefinição de Senha via Token.
2. **Dashboard / Painel Financeiro:** Cartões KPI (Receitas, Despesas, Saldo com Alerta Negativo), seletor temporal de mês/ano e gráficos JS locais de Fluxo de Caixa e Despesas por Categoria.
3. **Extrato e Lançamentos (CRUD):** Listagem histórica de movimentações com busca por descrição, filtro por categoria, navegação mês a mês e modais de criação, edição e exclusão lógica (*soft delete*).
4. **Categorias Personalizadas:** Gerenciamento de categorias criadas pelo próprio usuário e exibição de categorias padrão do sistema.
5. **Perfil e Segurança:** Alteração de senha por usuário logado e gravação de logs de auditoria.

---

## 2. Stack Tecnológica e Ambientes

### 2.1 Especificações da Stack
* **Linguagem Back-end:** PHP 8.x nativo (sem frameworks como Laravel, Symfony ou Slim).
* **Banco de Dados:** MySQL 8.x com acesso exclusivo via **PDO** e Prepared Statements obrigatórios.
* **Front-end:** HTML5, CSS3, JavaScript puro (Vanilla JS), **Bootstrap 5** (CSS/JS locais) e **Chart.js v4.x** (carregado localmente via arquivo UMD em `public/assets/js/chart.umd.js`).
* **Gerenciadores de Pacotes / Bundlers:** Não utilizados em tempo de execução (proibido o uso de Composer, NPM, Webpack ou CDNs externas).
* **Padrão Arquitetural:** MVC (Model-View-Controller) com Front Controller em `public/index.php` e reescrita de URL via `.htaccess`.

### 2.2 Ambientes
* **Desenvolvimento (Local):** Servidor Apache/PHP (ex: XAMPP) acessível via `http://localhost/sistema_financeiro` (ou porta local como `http://localhost:8080/sistema_financeiro`). Erros do PHP/PDO são gravados silenciosamente em `logs/error.log`.
* **Produção:** Servidor Apache com suporte a `.htaccess`, isolamento rígido de diretórios internos, suporte a HTTPS (`cookie_secure`) e `'env' => 'producao'` em `config/config.php` (para supressão total de erros técnicos em tela).

---

## 3. Como Rodar Localmente

1. **Clonar / Copiar o Projeto:** Posicionar a pasta do projeto dentro do diretório do servidor web (ex: `C:\xampp\htdocs\sistema_financeiro`).
2. **Configurar o Banco de Dados:**
   * Iniciar o serviço MySQL.
   * Importar o arquivo DDL `database/schema.sql` via MySQL CLI ou phpMyAdmin:
     ```bash
     mysql -u root -p gestao_financeira < database/schema.sql
     ```
3. **Configurar as Credenciais:**
   * Ajustar o arquivo `config/config.php` informando a URL base e credenciais do MySQL:
     ```php
     return [
         'app_name' => 'Gestão Financeira Simples',
         'app_url'  => 'http://localhost/sistema_financeiro',
         'env'      => 'desenvolvimento',
         'db' => [
             'host'    => '127.0.0.1',
             'port'    => '3306',
             'dbname'  => 'gestao_financeira',
             'user'    => 'root',
             'pass'    => '',
             'charset' => 'utf8mb4',
         ],
         // ...
     ];
     ```
4. **Executar no Navegador:** Iniciar o Apache (ex: pelo painel do XAMPP) e acessar a URL configurada em `app_url`.

---

## 4. Mapa de Pastas do Projeto

```text
├── app/
│   ├── controllers/      # Processamento de requisições, controle de fluxo e chamadas de views
│   ├── models/           # Consultas PDO SQL, isolamento por usuario_id e regras de dados
│   ├── views/            # Visões HTML renderizadas no servidor
│   │   ├── auth/         # Telas de login, cadastro, recuperação e redefinição de senha
│   │   ├── categorias/   # Tela de gerenciamento de categorias
│   │   ├── dashboard/    # Tela do painel financeiro principal
│   │   ├── extrato/      # Tela de histórico de lançamentos e modais
│   │   ├── perfil/       # Tela de alteração de senha
│   │   └── templates/    # Templates reutilizáveis (header, footer, sidebar)
│   └── helpers/          # Helpers de suporte (Auth, CSRF, Logger, Sanitizer)
├── config/               # Arquivos de configuração (config.php e database.php)
├── database/             # Script SQL de DDL e carga inicial de categorias padrão (schema.sql)
├── logs/                 # Arquivos de log do sistema (error.log, security.log, email_simulation.log)
├── public/               # Raiz pública acessível pelo navegador
│   ├── assets/           # Arquivos estáticos (css, js, imagens)
│   └── index.php         # Front Controller e roteamento central da aplicação
└── docs/                 # Documentação técnica e de manutenção do projeto
```

### Cuidados por Diretório
* **`app/controllers/`:** Toda action protegida deve invocar `Auth::requireAuth()`. Toda action POST deve validar token CSRF via `CSRF::validateToken($_POST['csrf_token'] ?? '')`.
* **`app/models/`:** Toda consulta SQL que acesse lançamentos ou categorias personalizadas deve conter obrigatoriamente `WHERE usuario_id = :usuario_id`.
* **`app/views/`:** Todos os arquivos de view devem iniciar com `defined('APP_EXEC') or die('Acesso direto não permitido.');` e utilizar `Sanitizer::e($valor)` para imprimir variáveis dinâmicas.
* **`config/`, `logs/`, `app/`, `database/`, `docs/`:** Possuem proteção de acesso direto via arquivos `.htaccess` (`Deny from all`). Nunca remover esses arquivos!

---

## 5. Banco de Dados e Persistência

### 5.1 Onde ficam os scripts de banco
* O arquivo DDL de criação do banco, tabelas e carga de categorias padrão está localizado em `database/schema.sql`.

### 5.2 Estrutura das Tabelas
1. **`usuarios`**: Guarda `id`, `nome`, `email` (único), `senha` (hash Bcrypt), `token_recuperacao` e `token_expiracao`.
2. **`categorias`**: Guarda `id`, `usuario_id` (`NULL` para categorias padrão do sistema), `nome`, `tipo` (`RECEITA`/`DESPESA`) e `deleted_at`.
3. **`lancamentos`**: Guarda `id`, `usuario_id`, `categoria_id`, `descricao`, `valor`, `data_movimentacao`, `tipo`, `status`, `forma_pagamento` e `deleted_at`.

### 5.3 Regras Importantes de Banco
* **Soft Delete (Exclusão Lógica):** Registros de `lancamentos` e `categorias` jamais são apagados com `DELETE FROM`. A exclusão atualiza a coluna `deleted_at = NOW()`.
* **Integridade Histórica:** Ao listar o extrato em `Lancamento::buscarExtrato()`, a busca por categoria é feita através de `LEFT JOIN categorias c ON l.categoria_id = c.id`, omitindo o filtro `deleted_at` na tabela de categorias para que lançamentos passados mantenham o nome da categoria visível.

---

## 6. Autenticação, Autorização e Usuários

* **Gestão de Sessão:** O ID do usuário logado fica em `$_SESSION['user_id']` e seu nome em `$_SESSION['user_nome']`.
* **Restrição de Rotas:** 
  * Rotas privadas chamam `Auth::requireAuth()` (redirecionam para `/login` se não autenticado).
  * Rotas públicas/visitantes (`/login`, `/cadastro`, etc.) chamam `Auth::requireGuest()` (redirecionam para `/dashboard` se já autenticado).
* **Perfis de Usuário:** Perfil único (**Usuário Final**). Não existe usuário administrador com acesso global aos dados de terceiros.

---

## 7. Como Adicionar uma Nova Tela

1. **Criar o método no Controller:** No controller adequado em `app/controllers/` (ou crie um novo), adicione o método com a verificação `Auth::requireAuth()`.
2. **Adicionar a Rota no Front Controller (`public/index.php`):** Registre a nova URL no bloco `if/elseif` de roteamento:
   ```php
   } elseif ($requestUri === '/nova-rota') {
       require_once dirname(__DIR__) . '/app/controllers/ExemploController.php';
       (new ExemploController())->index();
   }
   ```
3. **Criar a View:** Crie a visão em `app/views/<modulo>/index.php`. Inclua a verificação `defined('APP_EXEC') or die('Acesso direto não permitido.');` no topo e reutilize os templates `header.php`, `sidebar.php` e `footer.php`.
4. **Adicionar ao Menu:** Se a tela for de navegação principal, adicione o link correspondente em `app/views/templates/sidebar.php`.

---

## 8. Como Adicionar um Novo Campo em um Cadastro

1. **Banco de Dados:** Adicionar a nova coluna na tabela correspondente em `database/schema.sql` (e executar o `ALTER TABLE` no banco local).
2. **Model:** Atualizar os métodos de `inserir`, `atualizar` e `buscar` no Model correspondente em `app/models/` para incluir o novo parâmetro no Prepared Statement.
3. **Controller:** Atualizar o método do Controller (`app/controllers/`) para receber, higienizar e validar o novo campo no back-end.
4. **View / Formulário:** Adicionar o campo de input HTML na View correspondente com o escape `Sanitizer::e()`.
5. **Listagem:** Se o campo devendo aparecer em listas/tabelas, atualizar a View da tabela.
6. **Documentação:** Atualizar `docs/FSD.md` e este guia de manutenção.

---

## 9. Como Adicionar uma Nova Regra de Negócio

1. **Consultar o FSD:** Leia `docs/FSD.md` para garantir que a nova regra não viola premissas globais.
2. **Localização:** Regras de manipulação de dados pertencem ao Model; regras de controle de fluxo e validação pertencem ao Controller.
3. **Manter o Isolamento:** Assegurar que a regra preserve a restrição por `usuario_id = :usuario_id`.
4. **Testes:** Testar os cenários de sucesso, limites de borda e tentativas de entradas inválidas.

---

## 10. Como Testar Alterações

* **Validação Sintática (PHP Lint):**
  ```bash
  C:\xampp\php\php.exe -l caminho/do/arquivo.php
  ```
* **Testes Manuais de Navegação e Regras:**
  1. Testar o fluxo positivo da nova funcionalidade.
  2. Testar comportamento com dados inválidos (strings vazias, valores negativos, HTML indevido).
  3. Testar a navegação autenticada vs visitante.
  4. Testar o isolamento entre dois usuários cadastrados diferentes.
* **Inspeção de Logs:** Verificar `logs/error.log` e `logs/security.log` para confirmar que nenhum erro silencioso ou exceção foi disparada.
* **Registro de Erros:** Caso um bug seja descoberto durante as alterações, documente a causa e a solução em `docs/ERROS.md`.

---

## 11. Cuidados de Segurança (Obrigatórios)

* **Multitenancy Rígido:** Nunca execute uma consulta SQL em `lancamentos` ou `categorias` personalizadas sem a cláusula `WHERE usuario_id = :usuario_id`.
* **Prepared Statements:** Nunca concatene variáveis de entrada diretamente em strings SQL (`PDO::prepare` + `bindValue` sempre).
* **Escape XSS:** Toda variável dinamicamente renderizada nas views HTML deve usar `Sanitizer::e($valor)`.
* **Proteção CSRF:** Toda requisição POST deve validar o token CSRF com `CSRF::validateToken($_POST['csrf_token'] ?? '')`.
* **Proteção de Acesso Direto:** Todos os arquivos PHP das pastas internas devem iniciar com `defined('APP_EXEC') or die('Acesso direto não permitido.');`.
* **Proteção de Pastas:** Manter os arquivos `.htaccess` (`Deny from all`) nas pastas `app/`, `config/`, `database/`, `docs/` e `logs/`.
* **Criptografia de Senhas:** Manter exclusivamente `password_hash($senha, PASSWORD_BCRYPT)`.

---

## 12. Como Registrar Progresso

Toda alteração efetuada no sistema deve ser documentada atualizando:
1. `docs/STATUS.md`: Atualizar a fase/tarefa corrente e indicar o próximo passo.
2. `docs/ERROS.md`: Caso ocorra e seja corrigido algum bug relevante, registrar a data, sintoma, causa, solução e como evitar.

---

## 13. O que NÃO Fazer

* ❌ **NÃO** instalar Composer, NPM ou frameworks PHP (Laravel/Symfony). O projeto é PHP nativo.
* ❌ **NÃO** utilizar CDNs de CSS/JS externas para execução do sistema (estão permitidos apenas fontes/ícones do Google).
* ❌ **NÃO** utilizar `DELETE FROM` em lançamentos ou categorias. O sistema utiliza exclusão lógica (*soft delete*).
* ❌ **NÃO** desativar verificações de segurança ou CSRF para "facilitar testes".
* ❌ **NÃO** versionar arquivos com credenciais reais de produção.
* ❌ **NÃO** exibir mensagens técnicas de erro ou exceções PDO diretamente na tela do usuário.
