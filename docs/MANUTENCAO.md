# Guia de Manutenção e Evolução - Gestão Financeira Simples

Este documento contém as diretrizes técnicas, arquiteturais e operacionais para manutenção, correção de bugs e evolução do sistema **Gestão Financeira Simples**. Qualquer desenvolvedor ou assistente de IA deve obrigatoriamente consultar este guia antes de efetuar modificações no código-fonte.

---

## 1. Visão Geral do Sistema

### 1.1 O que o sistema faz
O **Gestão Financeira Simples** é uma aplicação web para controle orçamentário pessoal. Ele permite registrar receitas (entradas) e despesas (saídas), categorizar movimentações, alternar o status dos lançamentos ("Pago" ou "Pendente"), aplicar filtros temporais e textuais no extrato histórico e visualizar um painel orçamentário (*dashboard*) com indicadores de saldo e gráficos em tempo real.

### 1.2 Para quem foi criado
Pessoas físicas (Perfil funcional: **Usuário Final**) que buscam uma ferramenta intuitiva, ágil e objetiva para gerenciar o orçamento pessoal diário, sem complicação ou terminologia contábil avançada.

### 1.3 Problemas que resolve
Centraliza a gestão financeira pessoal, calcula automaticamente totalizadores de receita, despesa e saldo líquido do mês selecionado, destaca visualmente em vermelho quando o saldo fica negativo e preserva a integridade histórica dos lançamentos mesmo quando categorias são excluídas.

### 1.4 Módulos Principais
1. **Autenticação e Sessão:** Cadastro de usuário, Login, Logout, Solicitação e Redefinição de Senha via Token Hexadecimal.
2. **Dashboard / Painel Financeiro:** Cartões KPI (Receitas, Despesas, Saldo Líquido com Alerta Negativo), navegação temporal por mês/ano e gráficos JS locais (Fluxo de Caixa e Gastos por Categoria).
3. **Extrato e Lançamentos (CRUD):** Listagem histórica de movimentações com busca por descrição, filtro por categoria, navegação mês a mês e modais de criação, edição e exclusão lógica (*soft delete*).
4. **Categorias Personalizadas:** Gerenciamento de categorias criadas pelo próprio usuário e exibição de categorias padrão do sistema.
5. **Perfil e Segurança:** Alteração de senha por usuário logado e registro de auditoria em logs de segurança.

### 1.5 Arquitetura Geral
O sistema adota o padrão **MVC (Model-View-Controller)** aliado ao padrão **Front Controller**:
* Todas as requisições passam por `public/index.php` via reescrita de URL do Apache (`.htaccess`).
* As rotas acionam Controllers em `app/controllers/`.
* Os Controllers invocam os Models em `app/models/` para regras de dados e consultas SQL.
* Os Controllers renderizam as Views em `app/views/` injetando os dados necessários.

---

## 2. Stack Tecnológica e Ambientes

### 2.1 Especificações da Stack
* **Linguagem Back-end:** PHP 8.x nativo (sem frameworks como Laravel, Symfony ou Slim).
* **Banco de Dados:** MySQL 8.x acessado exclusivamente via **PDO** com *prepared statements* obrigatórios.
* **Front-end:** HTML5, CSS3, JavaScript puro (Vanilla JS), **Bootstrap 5** (CSS e JS locais) e **Chart.js v4.x** (carregado via arquivo UMD local em `public/assets/js/chart.umd.js`).
* **Gerenciadores de Pacotes / Bundlers:** Não utilizados em tempo de execução (proibido o uso de Composer, NPM, Webpack ou CDNs externas para scripts e estilos; apenas CDN de fontes/ícones do Google é permitida).

### 2.2 Ambientes de Execução
* **Desenvolvimento (Local):** Executado em Apache/PHP (ex: XAMPP) acessível via `http://localhost/sistema_financeiro`. Mensagens técnicas de exceção PDO ou PHP são gravadas silenciosamente no arquivo `logs/error.log` e nunca exibidas no navegador.
* **Produção:** Servidor Apache com isolamento de diretórios sensíveis via `.htaccess` (`Deny from all`), HTTPS ativo (`cookie_secure`), e `'env' => 'producao'` em `config/config.php` para supressão total de mensagens técnicas na tela.

### 2.3 Arquivo de Configuração
As variáveis de ambiente e conexões ficam contidas exclusivamente no arquivo `config/config.php` (não utilizar arquivos `.env`).

---

## 3. Como Rodar Localmente

### 3.1 Requisitos Mínimos
* PHP 8.x com a extensão `pdo_mysql` habilitada.
* Servidor Web Apache com o módulo `mod_rewrite` ativado.
* Banco de Dados MySQL 8.x ou MariaDB equivalente.

### 3.2 Passo a Passo de Instalação

1. **Posicionar o Projeto:** Copiar a pasta do projeto para o diretório de documentos do servidor web:
   ```text
   C:\xampp\htdocs\sistema_financeiro
   ```

2. **Criar e Importar o Banco de Dados:**
   * Garantir que o MySQL esteja rodando.
   * Importar o arquivo DDL e a carga de dados iniciais contida em `database/schema.sql` via MySQL CLI ou phpMyAdmin:
     ```bash
     mysql -u root -p gestao_financeira < database/schema.sql
     ```

3. **Configurar as Credenciais de Acesso:**
   * Editar o arquivo `config/config.php` alinhando a `app_url` e as credenciais de banco:
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

4. **Verificar Permissões de Log:**
   * Certifique-se de que a pasta `logs/` possui permissão de escrita para a gravação dos arquivos `error.log`, `security.log` e `email_simulation.log`.

5. **Executar no Navegador:**
   * Acesse no navegador o endereço: `http://localhost/sistema_financeiro`.

---

## 4. Mapa de Pastas do Projeto

```text
├── app/
│   ├── controllers/      # Controladores de fluxo e processamento de requisições
│   ├── models/           # Classes de regra de dados e consultas PDO (isoladas por usuario_id)
│   ├── views/            # Telas e componentes HTML renderizados no servidor
│   │   ├── auth/         # Login, cadastro, recuperação e redefinir senha
│   │   ├── categorias/   # Gerenciamento de categorias personalizadas
│   │   ├── dashboard/    # Painel orçamentário principal e gráficos
│   │   ├── extrato/      # Histórico de lançamentos, filtros e modais CRUD
│   │   ├── perfil/       # Alteração de senha do usuário
│   │   └── templates/    # Templates reutilizáveis (header, footer, sidebar)
│   └── helpers/          # Helpers de suporte (Auth, CSRF, Logger, Sanitizer)
├── config/               # Arquivos de configuração (config.php e database.php)
├── database/             # Script SQL de DDL e carga inicial (schema.sql)
├── logs/                 # Arquivos de log (error.log, security.log, email_simulation.log)
├── public/               # Raiz pública exposta pelo Apache
│   ├── assets/           # CSS, JS (Bootstrap, Chart.js, app.js) e imagens
│   └── index.php         # Front Controller e Roteador central da aplicação
└── docs/                 # Documentação técnica e de manutenção do projeto
```

### Regras e Cuidados por Diretório
* **`app/controllers/`:** Métodos de rotas protegidas devem chamar `Auth::requireAuth()`. Métodos POST devem validar CSRF via `CSRF::validateToken($_POST['csrf_token'] ?? '')`.
* **`app/models/`:** Toda instrução SQL que manipule `lancamentos` ou `categorias` personalizadas deve obrigatoriamente incluir `WHERE usuario_id = :usuario_id`.
* **`app/views/`:** Todos os arquivos de view devem declarar a trava no topo:
  ```php
  defined('APP_EXEC') or die('Acesso direto não permitido.');
  ```
  Toda variável impressa no HTML deve ser escapada com `Sanitizer::e($valor)`.
* **`app/`, `config/`, `database/`, `docs/`, `logs/`:** Possuem proteção contra acesso HTTP direto via `.htaccess` (`Deny from all`). Nunca remover esses arquivos.

---

## 5. Banco de Dados e Persistência

### 5.1 Arquivo DDL
A estrutura das tabelas e a carga inicial de dados estão localizadas em `database/schema.sql`.

### 5.2 Estrutura das Tabelas

1. **`usuarios`**:
   * `id` (INT UNSIGNED AUTO_INCREMENT PRIMARY KEY)
   * `nome` (VARCHAR 100)
   * `email` (VARCHAR 150 UNIQUE)
   * `senha` (VARCHAR 255 - Hash Bcrypt)
   * `token_recuperacao` (VARCHAR 64 NULL)
   * `token_expiracao` (DATETIME NULL)
   * `created_at`, `updated_at` (TIMESTAMP)

2. **`categorias`**:
   * `id` (INT UNSIGNED AUTO_INCREMENT PRIMARY KEY)
   * `usuario_id` (INT UNSIGNED NULL - `NULL` representa Categoria Padrão do Sistema)
   * `nome` (VARCHAR 50)
   * `tipo` (ENUM: `'RECEITA'`, `'DESPESA'`)
   * `created_at`, `updated_at` (TIMESTAMP)
   * `deleted_at` (DATETIME NULL - Exclusão Lógica)

3. **`lancamentos`**:
   * `id` (INT UNSIGNED AUTO_INCREMENT PRIMARY KEY)
   * `usuario_id` (INT UNSIGNED NOT NULL)
   * `categoria_id` (INT UNSIGNED NOT NULL)
   * `descricao` (VARCHAR 255)
   * `valor` (DECIMAL 10,2)
   * `data_movimentacao` (DATE)
   * `tipo` (ENUM: `'RECEITA'`, `'DESPESA'`)
   * `status` (ENUM: `'PAGO'`, `'PENDENTE'`)
   * `forma_pagamento` (ENUM: `'DINHEIRO'`, `'PIX'`, `'DEBITO'`, `'CREDITO'`)
   * `created_at`, `updated_at` (TIMESTAMP)
   * `deleted_at` (DATETIME NULL - Exclusão Lógica)

### 5.3 Regras Importantes de Persistência
* **Soft Delete (Exclusão Lógica):** Registros de lançamentos e categorias personalizadas nunca recebem `DELETE FROM`. A exclusão é efetuada atualizando a coluna `deleted_at = NOW()`.
* **Categorias Padrão vs. Personalizadas:** Categorias com `usuario_id IS NULL` são visíveis para todos os usuários e não podem ser excluídas. Categorias com `usuario_id = X` pertencem unicamente ao usuário X.
* **Integridade Histórica:** Ao consultar o extrato em `Lancamento::buscarExtrato()`, a junção com categorias usa `LEFT JOIN categorias c ON l.categoria_id = c.id` sem aplicar `deleted_at IS NULL` na tabela de categorias. Assim, se uma categoria for excluída, os lançamentos passados vinculados a ela continuarão exibindo seu nome no extrato.

---

## 6. Autenticação, Autorização e Usuários

### 6.1 Gerenciamento de Sessão e Criptografia
* O ID do usuário autenticado é mantido em `$_SESSION['user_id']` e seu nome em `$_SESSION['user_nome']`.
* As senhas são geradas com `password_hash($senha, PASSWORD_BCRYPT)` e checadas via `password_verify($senha, $hash)`.
* No login bem-sucedido, o ID da sessão é regenerado via `session_regenerate_id(true)` para evitar sequestro ou fixação de sessão.

### 6.2 Fluxo de Recuperação de Senha por Token
1. Usuário informa e-mail em `/recuperar-senha`.
2. O sistema gera um token de 64 caracteres hexadecimais (`bin2hex(random_bytes(32))`), define `token_expiracao = NOW() + INTERVAL 1 HOUR` e salva no banco.
3. Em ambiente local, a URL completa de redefinição (`/redefinir-senha?token=HEX`) é gravada no log `logs/email_simulation.log`.
4. Em `/redefinir-senha`, o token é validado. Se válido e não expirado, permite alterar a senha, limpa os campos de token e registra em `logs/security.log`.

### 6.3 Matriz de Permissões (RBAC)
O sistema possui perfil único (**Usuário Final**). Não existe perfil de administrador global.
* **Usuário Visitante:** Acessa apenas `/login`, `/cadastro`, `/recuperar-senha` e `/redefinir-senha`.
* **Usuário Autenticado:** Acessa `/dashboard`, `/extrato`, `/categorias` e `/perfil/senha`. Tentativas de acessar rotas de visitantes são redirecionadas para `/dashboard`.

---

## 7. Como Adicionar uma Nova Tela

Para adicionar uma nova funcionalidade no sistema, siga o roteiro de 5 passos abaixo:

### Passo 1: Criar o Controller
Crie o arquivo do controlador em `app/controllers/ExemploController.php`:
```php
<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/CSRF.php';
require_once __DIR__ . '/../helpers/Sanitizer.php';

class ExemploController {
    public function index() {
        Auth::requireAuth(); // Exige autenticação
        
        $usuarioId = $_SESSION['user_id'];
        $dados = []; // Buscar no Model se necessário

        require_once __DIR__ . '/../views/exemplo/index.php';
    }
}
```

### Passo 2: Registrar a Rota no Front Controller (`public/index.php`)
Edite o arquivo `public/index.php` adicionando a nova rota ao bloco de roteamento:
```php
} elseif ($requestUri === '/exemplo') {
    require_once dirname(__DIR__) . '/app/controllers/ExemploController.php';
    (new ExemploController())->index();
}
```

### Passo 3: Criar a View
Crie o arquivo de visão em `app/views/exemplo/index.php`:
```php
<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');
$pageTitle = 'Minha Nova Tela';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/sidebar.php';
?>

<main class="content-wrapper p-4">
    <h2><?= Sanitizer::e($pageTitle) ?></h2>
    <p>Conteúdo da nova funcionalidade aqui.</p>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
```

### Passo 4: Adicionar ao Menu Lateral
Se a nova tela fizer parte da navegação principal, edite `app/views/templates/sidebar.php` adicionando o item de menu correspondente.

### Passo 5: Atualizar Documentação
Atualize os arquivos `docs/FSD.md` e `docs/STATUS.md` documentando a nova funcionalidade.

---

## 8. Como Adicionar um Novo Campo em um Cadastro

Ao adicionar um novo campo a uma entidade existente (ex: adicionar `observacao` em `lancamentos`), siga a ordem por camadas:

### Passo 1: Banco de Dados
1. Edite o script DDL em `database/schema.sql` para refletir o estado final do schema.
2. Execute o comando `ALTER TABLE` no banco local de desenvolvimento:
   ```sql
   ALTER TABLE lancamentos ADD COLUMN observacao VARCHAR(255) DEFAULT NULL AFTER descricao;
   ```

### Passo 2: Model
Atualize o Model em `app/models/Lancamento.php`:
* Modifique as instruções `INSERT INTO` e `UPDATE` para incluir o novo parâmetro.
* Adicione o `bindValue` no PDO:
  ```php
  $stmt->bindValue(':observacao', Sanitizer::cleanText($dados['observacao'] ?? null));
  ```

### Passo 3: Controller
Atualize a action no Controller correspondente (ex: `LancamentosController.php`):
* Receba e higienize a variável de entrada via POST.
* Adicione as regras de validação necessárias antes de repassar ao Model.

### Passo 4: View (Formulário e Tabela)
1. Adicione o campo de formulário HTML nos modais de cadastro e edição (`app/views/extrato/index.php`):
   ```html
   <div class="mb-3">
       <label for="observacao" class="form-label">Observação</label>
       <input type="text" class="form-control" id="observacao" name="observacao" maxlength="255">
   </div>
   ```
2. Adicione a exibição da variável escapada na tabela de dados com `Sanitizer::e($item['observacao'])`.

### Passo 5: Documentação
Atualize a especificação funcional em `docs/FSD.md` e este guia de manutenção.

---

## 9. Como Alterar uma Regra de Negócio

1. **Consultar o FSD:** Leia `docs/FSD.md` para garantir que a alteração pretendida não quebra premissas globais do sistema.
2. **Respeitar a Separação de Camadas:**
   * Regras de cálculo, filtros de banco de dados e persistência pertencem à **Model**.
   * Regras de validação de formulário, mensagens de erro e redirecionamento de rotas pertencem ao **Controller**.
   * Formatação visual e apresentação de dados pertencem à **View**.
3. **Garantir Isolamento Rigoroso:** Assegure que qualquer nova consulta mantenha `WHERE usuario_id = :usuario_id` com o ID extraído unicamente de `$_SESSION['user_id']`.
4. **Tratamento de Exceções:** Em caso de exceção no Model ou Controller, registre a falha silenciosamente via `Logger::logError($msg)` e retorne uma mensagem amigável para a View.

---

## 10. Como Testar Alterações

### 10.1 Validação Sintática (PHP Lint)
Execute a checagem sintática no terminal para cada arquivo PHP modificado antes de realizar testes de tela:
```bash
C:\xampp\php\php.exe -l app/controllers/LancamentosController.php
```

### 10.2 Roteiro de Testes Manuais Funcionais
1. **Fluxo Positivo:** Cadastrar, editar e visualizar os dados criados.
2. **Entradas Inválidas e Injeções:** Tentar enviar strings vazias, valores negativos, scripts HTML/JS (`<script>alert(1)</script>`) e aspas simples `' OR 1=1 --`.
3. **Testar Isolamento (Multitenancy):** Criar dois usuários diferentes (Usuário A e Usuário B). Garantir que o Usuário A nunca visualize ou altere lançamentos ou categorias do Usuário B.
4. **Visualização do Saldo Negativo:** Cadastrar uma despesa superior às receitas no mês e confirmar se o saldo no Dashboard é exibido na cor vermelha (`#ba1a1a`).
5. **Modais de Confirmação de Exclusão:** Garantir que tanto lançamentos quanto categorias personalizadas exibam o modal de confirmação antes de executar o *soft delete*.
6. **Integridade Histórica de Categorias:** Cadastrar um lançamento com uma categoria personalizada, excluir essa categoria e verificar se o extrato continua exibindo o nome da categoria corretamente.
7. **Proteção CSRF:** Submeter formulário POST com token CSRF alterado/inválido e verificar se o sistema bloqueia o envio com código HTTP 403 e grava em `logs/security.log`.

### 10.3 Inspeção dos Arquivos de Log
Após os testes manuais, abra os arquivos de log para verificar se ocorreram falhas silenciosas:
* `logs/error.log`: Não deve conter nenhum novo registro de erro PDO ou warning PHP.
* `logs/security.log`: Deve conter os registros esperados de tentativas de login, CSRF ou alteração de senha.

---

## 11. Cuidados de Segurança Inegociáveis

1. **Multitenancy Rígido:** Toda operação SQL que envolva dados sensíveis deve ter a cláusula `WHERE usuario_id = :usuario_id` vinculada a `$_SESSION['user_id']`.
2. **Prepared Statements:** NUNCA concatene variáveis diretamente em strings SQL. Use sempre `PDO::prepare` com binding de parâmetros e mantenha `PDO::ATTR_EMULATE_PREPARES => false`.
3. **Prevenção de XSS:** Toda variável dinâmica renderizada em HTML deve ser envolvida por `Sanitizer::e($valor)`.
4. **Proteção CSRF:** Toda requisição de formulário POST deve validar o token CSRF com `CSRF::validateToken($_POST['csrf_token'] ?? '')`.
5. **Proteção contra Acesso Direto:** Todos os arquivos PHP das pastas internas devem iniciar com:
   ```php
   defined('APP_EXEC') or die('Acesso direto não permitido.');
   ```
6. **Proteção de Pastas Sensíveis:** As pastas `app/`, `config/`, `database/`, `docs/` e `logs/` devem conter arquivos `.htaccess` com a diretiva `Deny from all`.
7. **Supressão Total de Erros Técnicos:** Erros ou exceções PDO/PHP jamais devem ser exibidos no navegador do usuário final.

---

## 12. Como Atualizar STATUS.md e ERROS.md

Sempre que concluir uma manutenção, correção ou nova funcionalidade, é **obrigatório** atualizar os dois arquivos de acompanhamento conforme os protocolos descritos abaixo:

### 12.1 Atualização de `docs/STATUS.md`
1. Atualize o cabeçalho do arquivo com a data e hora locais da última modificação:
   ```markdown
   * **Última Atualização:** DD/MM/AAAA HH:MM (Local)
   ```
2. Atualize o status da fase ou tarefa atual (marque checkboxes `[x]` concluídos).
3. Especifique com clareza o **Próximo Passo Recomendado**.

### 12.2 Atualização de `docs/ERROS.md`
Se algum bug ou exceção foi encontrado e resolvido durante a manutenção, adicione um novo registro **no topo do histórico** em `docs/ERROS.md` respeitando estritamente o modelo:

```markdown
## DD/MM/AAAA - <Título Curto do Erro>

- Sintoma: <Descrever o comportamento ou mensagem que denunciou o erro>
- Causa: <Descrever a causa raiz técnica, erro de parâmetro ou divergência lógica>
- Solução aplicada: <Descrever os arquivos alterados e a modificação realizada>
- Como evitar no futuro: <Boas práticas e testes recomendados para impedir que se repita>
```

---

## 13. O que NÃO Fazer

* ❌ **NÃO** instale Composer, NPM ou frameworks PHP (Laravel/Symfony). O sistema é PHP nativo.
* ❌ **NÃO** utilize CDNs externas para CSS/JS (apenas a CDN de fontes/ícones do Google está liberada).
* ❌ **NÃO** utilize `DELETE FROM` em lançamentos ou categorias. O sistema trabalha exclusivamente com exclusão lógica (*soft delete*).
* ❌ **NÃO** desative proteções de CSRF ou autenticação sob o pretexto de "agilizar testes".
* ❌ **NÃO** exiba mensagens técnicas de exceções PDO ou PHP na tela do usuário.
* ❌ **NÃO** altere arquivos sem antes explicar o plano de implementação e validar com o usuário.
