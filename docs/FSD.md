# DOCUMENTO DE ESPECIFICAÇÃO FUNCIONAL (FSD)

**Sistema:** Gestão Financeira Simples

**Versão:** 1.1

**Status:** Aprovado com Correções Aplicadas

**Localização Final Esperada:** `docs/FSD.md`

---

## 1. Visão Geral

### 1.1. Objetivo Principal

O **Gestão Financeira Simples** é uma aplicação web de controle orçamentário pessoal projetada para ser intuitiva, prática e visualmente objetiva. Seu objetivo é ajudar pessoas físicas a organizarem sua vida financeira diária — controlando entradas (receitas), saídas (despesas), movimentações pagas e pendências — oferecendo visibilidade em tempo real sobre saldos mensais e categorias de gastos, sem exigir conhecimentos ou termos contábeis complexos.

### 1.2. Resumo do Funcionamento

O sistema centraliza as informações financeiras em um painel (*dashboard*) que calcula automaticamente o total de receitas, despesas e o saldo do mês selecionado.

* Quando o saldo do mês for negativo, o sistema destaca visualmente o valor em cor de alerta (vermelho de erro).
* Os usuários podem cadastrar, editar, visualizar e excluir lançamentos financeiros, associando-os a categorias (padrão ou personalizadas) e formas de pagamento, alternando o status entre "Pago" e "Pendente".
* O extrato completo permite busca por texto, filtro por categoria e navegação temporal mês a mês.
* A navegação e componentes visuais seguem estritamente as diretrizes do Design System *Admin Logic* (`docs/DESIGN.md`).

### 1.3. Público Usuário

Pessoas físicas (Perfil: **Usuário Final**) que buscam facilidade e agilidade no controle orçamentário pessoal.

### 1.4. Contexto de Uso

Aplicação web responsiva acessada via navegadores desktop e dispositivos móveis, com autenticação individual para isolamento estrito dos registros de cada usuário.

---

## 2. Escopo da Primeira Versão

### 2.1. Funcionalidades Incluídas

1. Autenticação completa de usuários (Cadastro, Login, Logout, Solicitação de Recuperação de Senha, Redefinição de Senha via Token e Alteração de Senha por usuário logado).
2. Painel Financeiro (*Dashboard*) com seletor de mês/ano, cartões KPI (Receitas, Despesas, Saldo Líquido do Mês) e indicador visual de saldo negativo.
3. Gestão de Lançamentos (CRUD): Cadastro, Edição, Visualização e Exclusão Lógica (*Soft Delete*) de Receitas e Despesas.
4. Controle de Status do Lançamento: Alternância entre "Pago" e "Pendente".
5. Categorização e Formas de Pagamento: Associação de lançamentos a categorias e seleção de forma de pagamento (Dinheiro, Pix, Cartão de Débito, Cartão de Crédito).
6. Extrato e Histórico Financeiro: Listagem detalhada dos lançamentos com busca textual por descrição e filtro por categoria.
7. Gestão de Categorias Personalizadas: Criação e exclusão lógica (*soft delete*) de categorias criadas pelo próprio usuário, além do uso de categorias padrão do sistema.
8. Isolamento de Dados: Garantia de que cada usuário acesse exclusivamente seus próprios registros.

### 2.2. Funcionalidades Fora de Escopo

Ficam expressamente excluídos da primeira versão:

* Conexão ou sincronização bancária automática (Open Finance).
* Gráficos visuais complexos tridimensionais ou relatórios estatísticos avançados (gráficos básicos de barras de fluxo de caixa e rosca de categorias no dashboard são incluídos na primeira versão usando biblioteca externa JS local).
* Despesas parceladas ou lançamentos automáticos recorrentes.
* Suporte a múltiplas moedas ou conversão cambial.
* Aplicativos móveis nativos (iOS/Android).
* Exportação de relatórios para PDF, CSV ou Excel.
* APIs REST públicas, webhooks ou integrações externas.
* Painel administrativo global ou gestão multi-empresa.

---

## 3. Regras de Negócio Globais

1. **Isolamento de Dados:** Todas as operações no banco de dados para lançamentos e categorias personalizadas devem conter obrigatoriamente a cláusula de restrição `usuario_id = :usuario_id`.
2. **Cálculo do Saldo Mensal:** 
$$\text{Saldo do Mês} = \sum (\text{Receitas do Mês}) - \sum (\text{Despesas do Mês})$$


*Nota:* O status "Pendente" ou "Pago" não altera a inclusão do lançamento no cálculo do saldo do mês, refletindo a competência das movimentações registradas para o período.
3. **Alerta de Saldo Negativo:** Se o valor do Saldo do Mês for estritamente menor que R$ 0,00 ($< 0$), o valor deve ser exibido na cor de erro do Design System (`#ba1a1a`).
4. **Soft Delete (Exclusão Lógica):** Registros de lançamentos e categorias personalizadas nunca são apagados fisicamente do banco de dados (`DELETE FROM`). A exclusão é realizada atualizando a coluna `deleted_at` com o timestamp corrente (`NOW()`).
5. **Navegação Temporal Padrão:** Ao acessar o sistema, a data de referência inicial é o **mês e ano vigentes**. O usuário pode navegar para meses anteriores ou futuros através do seletor.

---

## 4. Perfis de Usuário, Permissões e Restrições

### 4.1. Definição do Perfil

O sistema possui um único perfil funcional: **Usuário Final**. Não há usuários administradores com visão global de dados.

### 4.2. Matriz de Permissões (RBAC)

| Ação / Funcionalidade | Usuário Visitante (Não Autenticado) | Usuário Final (Autenticado) |
| --- | --- | --- |
| Visualizar página de Login e Cadastro | **Permitido** | Redirecionado para `/dashboard` |
| Solicitar Recuperação / Redefinir Senha | **Permitido** | **Permitido** |
| Visualizar Dashboard e Extrato | Negado (Redireciona para `/login`) | **Permitido** (Apenas próprios dados) |
| Criar, Editar e Excluir Lançamentos | Negado | **Permitido** (Apenas próprios dados) |
| Criar e Excluir Categorias Personalizadas | Negado | **Permitido** (Apenas próprios dados) |
| Alterar a própria Senha (Perfil) | Negado | **Permitido** |
| Acessar dados de outros usuários | **Negado** | **Negado** |

---

## 5. Arquitetura e Decisões Técnicas

### 5.1. Stack Tecnológica

* **Linguagem Back-end:** PHP 8.x nativo (sem frameworks como Laravel ou Symfony).
* **Banco de Dados:** MySQL 8.x acessado via **PDO** com *prepared statements* obrigatórios.
* **Front-end:** HTML5, CSS3, JavaScript puro (Vanilla JS), **Bootstrap 5** e **Chart.js v4.x** (biblioteca de gráficos gratuita sob licença MIT, com arquivo JS minificado armazenado localmente em `public/assets/js/`).
* **Dependências Externas:** Proibido o uso de Composer, NPM, Webpack ou CDNs para CSS/JS de execução (com exceção da CDN do Google Fonts/Icons, que está excepcionalmente liberada para tipografias e ícones).

### 5.2. Padrão Arquitetural MVC e Front Controller

* **Front Controller:** Todas as requisições HTTP são centralizadas em `public/index.php` via reescrita de URL (`.htaccess`).
* **Estrutura MVC:**
* `app/models/`: Regras de dados, consultas PDO e isolamento por `usuario_id`.
* `app/controllers/`: Processamento das requisições, validações e controle de fluxo.
* `app/views/`: Interfaces HTML renderizadas no servidor.


* **Bloqueio de Acesso Directo:** Arquivos de visão e configuração contêm a verificação da constante `defined('APP_EXEC') or die('Acesso direto não permitido.');`.

### 5.3. Estrutura de Diretórios e Arquivos

```
gestao-financeira/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── CategoriasController.php
│   │   ├── DashboardController.php
│   │   ├── LancamentosController.php
│   │   └── PerfilController.php
│   ├── models/
│   │   ├── Categoria.php
│   │   ├── Lancamento.php
│   │   └── Usuario.php
│   ├── views/
│   │   ├── auth/
│   │   │   ├── cadastro.php
│   │   │   ├── login.php
│   │   │   ├── recuperar-senha.php
│   │   │   └── redefinir-senha.php
│   │   ├── categorias/
│   │   │   └── index.php
│   │   ├── dashboard/
│   │   │   └── index.php
│   │   ├── extrato/
│   │   │   └── index.php
│   │   ├── perfil/
│   │   │   └── senha.php
│   │   └── templates/
│   │       ├── footer.php
│   │       ├── header.php
│   │       └── sidebar.php
│   └── helpers/
│       ├── Auth.php
│       ├── CSRF.php
│       ├── Logger.php
│       └── Sanitizer.php
├── config/
│   ├── config.php
│   └── database.php
├── database/
│   └── schema.sql
├── logs/
│   ├── .htaccess (Deny from all)
│   ├── error.log
│   └── security.log
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── admin-logic.css
│   │   │   └── bootstrap.min.css
│   │   └── js/
│   │       ├── app.js
│   │       └── bootstrap.bundle.min.js
│   ├── .htaccess
│   └── index.php
└── docs/
    ├── DECISOES_TECNICAS.md
    ├── DESIGN.md
    ├── FSD.md
    └── PRD.md

```

---

## 6. Ambientes e Configurações Globais

### 6.1. Ambiência

O sistema suporta os ambientes de `desenvolvimento` e `producao`. No ambiente de desenvolvimento, mensagens de erro detalhadas podem ser gravadas no arquivo de log interno, mas nunca exibidas na tela do usuário.

### 6.2. Arquivo de Configuração (`config/config.php`)

Não é utilizado arquivo `.env`. Todas as configurações sensíveis ficam contidas em `config/config.php`, protegido contra acesso público via `.htaccess` e restrição do Apache.

```php
<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

return [
    'app_name' => 'Gestão Financeira Simples',
    'app_url'  => 'http://localhost/gestao-financeira',
    'env'      => 'desenvolvimento', // desenvolvimento | producao
    
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => '3306',
        'dbname'  => 'gestao_financeira',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    
    'session' => [
        'name'     => 'GFS_SESSID',
        'lifetime' => 7200, // 2 horas
    ]
];

```

---

## 7. Autenticação, Sessão e Proteção CSRF

### 7.1. Gestão de Sessão e Criptografia

* **Hash de Senhas:** As senhas são criptografadas antes do salvamento utilizando `password_hash($senha, PASSWORD_BCRYPT)`. A verificação é realizada via `password_verify($senha, $hash)`.
* **Configuração da Sessão:** A sessão é iniciada com parâmetros de segurança reforçados:
```php
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

```


* **Regeneração de ID:** O ID de sessão é regenerado via `session_regenerate_id(true)` imediatamente após o login bem-sucedido para mitigar ataques de fixação de sessão.

### 7.2. Proteção CSRF

Todas as requisições que alteram estado (POST) exigem a validação de um token CSRF individual gerado por sessão:

* O formulário inclui o campo oculto `<input type="hidden" name="csrf_token" value="...">`.
* O servidor valida o token antes de processar qualquer formulário via `CSRF::validateToken($_POST['csrf_token'])`. Se o token for inválido ou ausente, a requisição é abortada com HTTP 403 e o evento é registrado em `logs/security.log`.

---

## 8. Isolamento de Dados por Usuário

Para garantir que nenhum usuário visualize ou altere informações de terceiros:

1. **Identificador na Sessão:** O ID do usuário autenticado é mantido em `$_SESSION['user_id']`.
2. **Injeção de Parâmetros:** Toda operação de leitura, escrita, atualização ou exclusão lógica no Model deve obrigatoriamente concatenar e vincular o `usuario_id`:
```sql
-- Exemplo de busca de lançamentos
SELECT * FROM lancamentos 
WHERE usuario_id = :usuario_id 
  AND deleted_at IS NULL;

-- Exemplo de edição de lançamento
UPDATE lancamentos 
SET descricao = :descricao, valor = :valor 
WHERE id = :id 
  AND usuario_id = :usuario_id;

```



---

## 9. Modelo de Dados e DDL (MySQL 8.x)

### 9.1. DDL Completo em SQL

```sql
CREATE DATABASE IF NOT EXISTS `gestao_financeira` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `gestao_financeira`;

-- --------------------------------------------------------
-- Tabela: usuarios
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `token_recuperacao` VARCHAR(64) DEFAULT NULL,
  `token_expiracao` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela: categorias
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED DEFAULT NULL, -- NULL para categorias padrão do sistema
  `nome` VARCHAR(50) NOT NULL,
  `tipo` ENUM('RECEITA', 'DESPESA') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_categorias_usuario` 
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela: lancamentos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lancamentos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED NOT NULL,
  `categoria_id` INT UNSIGNED NOT NULL,
  `descricao` VARCHAR(255) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `data_movimentacao` DATE NOT NULL,
  `tipo` ENUM('RECEITA', 'DESPESA') NOT NULL,
  `status` ENUM('PAGO', 'PENDENTE') NOT NULL DEFAULT 'PAGO',
  `forma_pagamento` ENUM('DINHEIRO', 'PIX', 'DEBITO', 'CREDITO') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_lancamentos_usuario` 
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lancamentos_categoria` 
    FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Carga Inicial: Categorias Padrão (usuario_id = NULL)
-- --------------------------------------------------------
INSERT INTO `categorias` (`usuario_id`, `nome`, `tipo`) VALUES
(NULL, 'Salário', 'RECEITA'),
(NULL, 'Investimentos', 'RECEITA'),
(NULL, 'Outras Receitas', 'RECEITA'),
(NULL, 'Alimentação', 'DESPESA'),
(NULL, 'Moradia', 'DESPESA'),
(NULL, 'Transporte', 'DESPESA'),
(NULL, 'Saúde', 'DESPESA'),
(NULL, 'Lazer', 'DESPESA'),
(NULL, 'Outras Despesas', 'DESPESA');

```

### 9.2. Campos de Auditoria e Soft Delete

* **Auditoria:** Todas as tabelas incluem `created_at` e `updated_at`.
* **Soft Delete:** A exclusão de lançamentos e categorias personalizadas preenche `deleted_at = NOW()`. Registros com `deleted_at IS NOT NULL` são omitidos das telas normais da aplicação.

### 9.3. Integridade Histórica de Categorias Excluídas (LEFT JOIN)

Quando uma categoria personalizada for excluída via *soft delete*, os lançamentos históricos associados a ela **devem continuar exibindo o nome da categoria no Extrato**.
Para isso, a consulta SQL do Extrato faz um `LEFT JOIN` ignorando o filtro de `deleted_at` na tabela de categorias:

```sql
SELECT 
  l.id, l.descricao, l.valor, l.data_movimentacao, 
  l.tipo, l.status, l.forma_pagamento,
  c.nome AS categoria_nome
FROM lancamentos l
LEFT JOIN categorias c ON l.categoria_id = c.id
WHERE l.usuario_id = :usuario_id
  AND l.deleted_at IS NULL
  AND MONTH(l.data_movimentacao) = :mes
  AND YEAR(l.data_movimentacao) = :ano
ORDER BY l.data_movimentacao DESC, l.id DESC;

```

*Nota:* No dropdown de escolha de categoria para **novos cadastros e edições**, a consulta filtra apenas categorias ativas (`c.deleted_at IS NULL`).

---

## 10. Especificação de Módulos e Telas

### 10.1. Layout Base (Design System *Admin Logic*)

* **Cores Primárias:** Azul Escuro `#0f2d7b` / Container `#2c4593`.
* **Cores Funcionais:** Sucesso/Receita `#10b981`, Alerta `#f59e0b`, Erro/Despesa `#ba1a1a`.
* **Tipografia e Alinhamento:** Fonte de sistema com suporte a numerais tabulares (`font-variant-numeric: tabular-nums`).
* **Bordas:** Cantos suavizados de 4px (`0.25rem`) em botões e campos de entrada; Status pills com raio total (100px).

### 10.2. Tela 1: Login (`/login`)

* **Campos:** E-mail, Senha.
* **Ações:** Botão "Entrar", Link "Criar uma conta", Link "Esqueceu sua senha?".
* **Validações:** Verificar se campos foram preenchidos; credenciais válidas via `password_verify`.
* **Mensagem de Erro:** "E-mail ou senha inválidos" (mensagem genérica para evitar enumeração de usuários).

### 10.3. Tela 2: Cadastro de Usuário (`/cadastro`)

* **Campos:** Nome Completo, E-mail, Senha, Confirmar Senha.
* **Ações:** Botão "Cadastrar", Link "Já possui conta? Faça login".
* **Validações:** Nome obrigatório; e-mail válido e único no banco; senha com no mínimo 8 caracteres (contendo letras e números); confirmação idêntica à senha.

### 10.4. Tela 3: Solicitação de Recuperação de Senha (`/recuperar-senha`)

* **Campos:** E-mail cadastrado.
* **Ações:** Botão "Enviar Link de Recuperação", Link "Voltar ao Login".
* **Comportamento:** O sistema gera um token aleatório (64 caracteres hexadecimais), grava na tabela `usuarios` com validade de 1 hora (`token_expiracao = NOW() + INTERVAL 1 HOUR`) e exibe mensagem de sucesso informativa: *"Se o e-mail estiver cadastrado em nosso sistema, você receberá as instruções para redefinição de senha."*
* **Simulação de Envio de E-mail (Desenvolvimento):** Para fins de desenvolvimento local sem servidor SMTP ativo, o link com o token de redefinição de senha (`/redefinir-senha?token=HEX_TOKEN`) e o conteúdo do e-mail simulado serão gravados no arquivo de log local `logs/email_simulation.log` em cada solicitação de recuperação.

### 10.5. Tela 3.1: Redefinição de Senha via Token (`/redefinir-senha?token=HEX_TOKEN`)

* **Acesso:** Exclusivo via parâmetro `token` recebido na URL.
* **Campos:** Nova Senha, Confirmar Nova Senha, Campo Oculto (`token`).
* **Ações:** Botão "Salvar Nova Senha".
* **Validações:**
* O token informado deve existir na tabela `usuarios`.
* `token_expiracao` deve ser maior que o horário atual (`token_expiracao > NOW()`).
* A nova senha deve ter no mínimo 8 caracteres e coincidir com a confirmação.


* **Ação pós-sucesso:** Atualiza a coluna `senha` com novo hash, limpa `token_recuperacao` e `token_expiracao` para `NULL`, grava log de segurança e redireciona para `/login` com mensagem de sucesso: *"Senha redefinida com sucesso! Faça login com sua nova senha."*
* **Tratamento de Token Inválido:** Exibe mensagem de erro na tela: *"Link de redefinição de senha inválido ou expirado. Por favor, solicite uma nova recuperação."* e exibe botão para voltar à Tela 3.

### 10.6. Tela 4: Painel / Dashboard (`/dashboard`)

* **Cabeçalho Superior:** Nome do usuário logado, botão/menu do perfil para Alteração de Senha, botão "Sair" (Logout).
* **Barra de Navegação Temporal:**
* Seletor Mês/Ano com botões "Anterior" (`<`) e "Próximo" (`>`), padrão no mês atual.


* **Cartões KPI (Resumo do Mês):**
* **Total de Receitas:** Soma de todas as receitas do mês (`R$ 0,00`, em cor verde `#10b981`).
* **Total de Despesas:** Soma de todas as despesas do mês (`R$ 0,00`, em cor azul/grafite ou cinza).
* **Saldo do Mês:** Diferença (Receitas - Despesas).
* Se Saldo $\ge 0$: Texto em cor neutra/destaque padrão.
* Se Saldo $< 0$: Texto obrigatoriamente destacado na cor vermelha de erro (`#ba1a1a`) com ícone ou tag de alerta visual "Saldo Negativo".


* **Gráficos Visuais (Dashboard):**
  * **Gráfico de Fluxo de Caixa Mensal:** Gráfico de barras simples exibindo receitas e despesas acumuladas nos últimos meses (exemplo: últimos 6 meses). Implementado via biblioteca JS `Chart.js` carregada localmente.
  * **Gráfico de Gastos por Categoria:** Gráfico de rosca exibindo a distribuição de despesas do mês por categorias de despesa ativas do usuário. Implementado via biblioteca JS `Chart.js` carregada localmente.


* **Atalhos Rápidos:** Botão "+ Novo Lançamento" (abre modal de cadastro) e botão "Ver Extrato Completo".

### 10.7. Tela 5: Extrato de Lançamentos (`/extrato`)

* **Filtros e Busca:**
* Navegação Temporal (Mês/Ano).
* Campo de busca por texto (filtra por descrição do lançamento).
* Dropdown de filtro por Categoria.


* **Tabela de Lançamentos:**
* Colunas: Data, Descrição, Categoria, Forma de Pagamento, Status (Pill "Pago" em verde ou "Pendente" em amarelo/laranja), Valor (Verde com `+` para receita, Vermelho com `-` para despesa), Ações.
* Ações por linha: Botão Editar e Botão Excluir.


* **Estados Visuais:**
* *Tabela Vazia:* Exibe mensagem amigável *"Nenhum lançamento encontrado para o período ou filtros selecionados."* com botão para cadastrar novo lançamento.



### 10.8. Tela 5.1 e 5.2: Modais de Cadastro e Edição de Lançamento

* **Campos do Formulário:**
* Tipo (Radio / Buttons): `RECEITA` ou `DESPESA`.
* Descrição (Texto, máx. 255 caracteres).
* Valor (Numérico/Monetário com 2 casas decimais, maior que zero).
* Data do Lançamento (Data no formato YYYY-MM-DD).
* Categoria (Dropdown alimentado com categorias padrão + categorias personalizadas ativas do usuário).
* Status (Select: `PAGO` ou `PENDENTE`).
* Forma de Pagamento (Select: `DINHEIRO`, `PIX`, `DEBITO`, `CREDITO`).


* **Ações:** Botão "Salvar Lançamento" e Botão "Cancelar".

### 10.9. Tela 5.3: Modal de Confirmação de Exclusão de Lançamento

* **Interface:** Caixa de diálogo modal de confirmação.
* **Mensagem:** *"Tem certeza de que deseja excluir o lançamento '[Descrição]' no valor de R$ [Valor]? Esta ação moverá o registro para o histórico excluído."*
* **Ações:** Botão "Cancelar" e Botão "Confirmar Exclusão" (estilo danger / vermelho).

### 10.10. Tela 6: Gerenciamento de Categorias (`/categorias`)

* **Seção 1: Form para Nova Categoria Personalizada**
* Campos: Nome da Categoria (máx. 50 caracteres), Tipo (`RECEITA` ou `DESPESA`).
* Botão: "Adicionar Categoria".


* **Seção 2: Listagem de Categorias**
* **Categorias Padrão:** Exibidas com indicador "Padrão do Sistema" (Sem opção de exclusão).
* **Categorias Personalizadas:** Exibidas em lista com botão "Excluir".



### 10.11. Tela 6.1: Modal de Confirmação de Exclusão de Categoria

* **Acionamento:** Ao clicar no botão "Excluir" de qualquer categoria personalizada.
* **Interface:** Caixa de diálogo modal de confirmação.
* **Mensagem:** *"Tem certeza de que deseja excluir a categoria '[Nome da Categoria]'? Os lançamentos antigos vinculados a esta categoria continuarão visíveis no seu extrato histórico, mas ela não estará mais disponível para novos lançamentos."*
* **Ações:** Botão "Cancelar" e Botão "Confirmar Exclusão" (estilo danger / vermelho).

### 10.12. Tela 7: Alteração de Senha do Usuário Logado (`/perfil/senha` ou Modal no Perfil)

* **Acesso:** Menu do usuário autenticado no cabeçalho do sistema.
* **Campos:** Senha Atual, Nova Senha, Confirmar Nova Senha.
* **Ações:** Botão "Salvar Nova Senha" e Botão "Cancelar".
* **Validações:**
* Senha Atual deve corresponder ao hash do usuário no banco (`password_verify`).
* Nova Senha deve possuir no mínimo 8 caracteres (com letras e números).
* Nova Senha deve ser diferente da Senha Atual.
* Confirmar Nova Senha deve ser idêntica à Nova Senha.


* **Log de Segurança:** Em caso de sucesso, insere entrada em `logs/security.log`.

---

## 11. Fluxos Funcionais Detalhados

### 11.1. Fluxo 1: Autenticação, Cadastro e Redefinição de Senha

1. **Cadastro:** Usuário preenche formulário em `/cadastro`. O servidor valida unicidade do e-mail e força do hash. Se válido, insere o usuário e redireciona para `/login` com mensagem de sucesso.
2. **Login:** Usuário informa e-mail e senha em `/login`. O servidor busca o e-mail, verifica `password_verify`, regenera o ID da sessão e redireciona para `/dashboard`.
3. **Solicitação de Redefinição:** Usuário acessa `/recuperar-senha` e informa e-mail. Servidor gera token de 64 bytes hex, salva `token_recuperacao` e `token_expiracao` na tabela `usuarios` e simula o envio de e-mail registrando a URL completa `/redefinir-senha?token=HEX_TOKEN` em `logs/email_simulation.log`.
4. **Processamento do Token:** Usuário acessa o link. O sistema valida validade temporal e existência do token. Se válido, exibe a Tela 3.1. Ao submeter a nova senha, o hash é atualizado, o token é limpo, o evento é gravado em `logs/security.log` e o usuário é redirecionado ao login.

### 11.2. Fluxo 2: Gestão de Lançamentos (CRUD, Filtros e Navegação Temporal)

1. **Criação:** Na página `/extrato` ou `/dashboard`, o usuário clica em "+ Novo Lançamento". O modal é aberto. O usuário preenche os campos e clica em "Salvar". O servidor valida os dados obrigatoriamente (ver Seção 12), insere em `lancamentos` vincular ao `usuario_id` e recarrega a página.
2. **Navegação Temporal:** O usuário clica no seletor Mês/Ano. A requisição via GET (`/extrato?mes=08&ano=2026`) atualiza os dados da tabela e os KPIs do Dashboard para o mês selecionado.
3. **Edição:** O usuário clica em "Editar" na linha do lançamento. O modal abre pré-preenchido. O servidor valida a propriedade do registro (`usuario_id = :user_id`) antes de salvar as alterações.
4. **Exclusão Lógica:** O usuário clica em "Excluir". O modal de confirmação (Tela 5.3) é exibido. Após confirmação, a requisição envia o ID via POST (com token CSRF). O Model executa `UPDATE lancamentos SET deleted_at = NOW() WHERE id = :id AND usuario_id = :user_id`.

### 11.3. Fluxo 3: Gestão de Categorias e Exclusão com Modal

1. **Criação de Categoria Personalizada:** Em `/categorias`, o usuário digita o nome e seleciona o tipo (`RECEITA` ou `DESPESA`). Ao salvar, o registro é criado com `usuario_id = $_SESSION['user_id']`.
2. **Tentativa de Exclusão:** O usuário clica no botão "Excluir" ao lado da categoria personalizada.
3. **Exibição do Modal de Confirmação:** O sistema intercepta o evento e abre o Modal de Confirmação (Tela 6.1) avisando sobre a manutenção do histórico dos lançamentos antigos.
4. **Confirmação:** Ao clicar em "Confirmar Exclusão", o servidor processa a atualização `UPDATE categorias SET deleted_at = NOW() WHERE id = :id AND usuario_id = :user_id`. A categoria deixa de aparecer no cadastro de novos lançamentos, mas lançamentos antigos associados mantêm seu nome visível no extrato via `LEFT JOIN`.

### 11.4. Fluxo 4: Alteração de Senha de Usuário Autenticado

1. **Acesso:** Usuário autenticado acessa Tela 7 (Perfil / Alteração de Senha).
2. **Entrada de Dados:** Usuário preenche Senha Atual, Nova Senha e Confirmação.
3. **Validação no Servidor:**
* Busca a senha hash atual do usuário logado na tabela `usuarios`.
* Executa `password_verify($_POST['senha_atual'], $user['senha'])`. Se falso, retorna erro *"Senha atual incorreta."*.
* Executa validações de tamanho mínimo (8 caracteres) e correspondência com a confirmação.


4. **Persistência e Log:**
* Executa `UPDATE usuarios SET senha = :nova_senha_hash WHERE id = :user_id`.
* Registra no log de segurança: `[DATA HORATO] SECURITY: Alteração de senha bem-sucedida para usuario_id=[ID] | IP=[IP]`.
* Exibe mensagem de sucesso na tela: *"Sua senha foi alterada com sucesso!"*.



---

## 12. Validações e Regras de Negócio (Matriz Completa)

A tabela a seguir padroniza todas as validações obrigatórias que devem ser executadas no front-end e **obrigatoriamente revalidadas no back-end (servidor)**:

| Campo / Operação | Obrigatorio | Regra de Validação Front-end | Regra de Validação Back-end | Mensagem de Erro |
| --- | --- | --- | --- | --- |
| **Login - E-mail** | Sim | `type="email"`, required | `filter_var($email, FILTER_VALIDATE_EMAIL)` | "E-mail ou senha inválidos." |
| **Login - Senha** | Sim | `type="password"`, required | `!empty($senha)` | "E-mail ou senha inválidos." |
| **Cadastro - Nome** | Sim | `type="text"`, required, minlength=3 | `mb_strlen($nome) >= 3` | "O nome deve ter no mínimo 3 caracteres." |
| **Cadastro - E-mail** | Sim | `type="email"`, required | Validar formato e consultar duplicidade em `usuarios` | "E-mail inválido ou já cadastrado." |
| **Cadastro - Senha** | Sim | `type="password"`, required, minlength=8 | Regex / Mínimo 8 chars alfanuméricos | "A senha deve ter no mínimo 8 caracteres." |
| **Cadastro - Confirmação** | Sim | Campo idêntico à senha | `$senha === $confirmacao` | "As senhas não coincidem." |
| **Lançamento - Descrição** | Sim | `type="text"`, required, maxlength=255 | `!empty($desc) && mb_strlen($desc) <= 255` | "A descrição é obrigatória (máx. 255 caracteres)." |
| **Lançamento - Valor** | Sim | `type="number"`, step="0.01", min="0.01" | `is_numeric($valor) && $valor > 0` | "O valor deve ser um número positivo maior que zero." |
| **Lançamento - Data** | Sim | `type="date"`, required | Validar formato `YYYY-MM-DD` via `checkdate` | "Data inválida." |
| **Lançamento - Tipo** | Sim | Radio button: RECEITA/DESPESA | `in_array($tipo, ['RECEITA', 'DESPESA'])` | "Tipo de lançamento inválido." |
| **Lançamento - Categoria** | Sim | Select required | Verificar se ID existe e pertence ao usuário ou se é padrão | "Categoria inválida." |
| **Lançamento - Status** | Sim | Select required | `in_array($status, ['PAGO', 'PENDENTE'])` | "Status inválido." |
| **Lançamento - Forma Pagto** | Sim | Select required | `in_array($forma, ['DINHEIRO', 'PIX', 'DEBITO', 'CREDITO'])` | "Forma de pagamento inválida." |
| **Categoria - Nome** | Sim | `type="text"`, required, maxlength=50 | `!empty($nome) && mb_strlen($nome) <= 50` e validação case-insensitive que impede duplicidade de nome e tipo de categoria ativa (`deleted_at IS NULL`) para o mesmo usuário ou padrão | "O nome da categoria é obrigatório (máx. 50 caracteres) e deve ser único para o seu tipo." |
| **Perfil - Senha Atual** | Sim | `type="password"`, required | `password_verify($atual, $hash_banco)` | "A senha atual está incorreta." |

---

## 13. Sistema de Logs, Contingência e Segurança

### 13.1. Logs de Erro (`logs/error.log`)

Todas as exceções não tratadas, erros de PDO ou falhas internas de execução devem ser capturadas por blocos `try-catch` no controller/model e gravadas via classe `Logger`:

* **Formato:** `[YYYY-MM-DD HH:MM:SS] [ERROR] Mensagem detalhada | File: arquivo.php | Line: N`
* **Privacidade:** Detalhes de SQL ou mensagens de erro técnico jamais são exibidos na View. O usuário visualiza apenas uma mensagem amigável: *"Ocorreu um erro interno ao processar sua solicitação. Tente novamente mais tarde."*

### 13.2. Logs de Segurança (`logs/security.log`)

Eventos sensíveis são auditados no arquivo de segurança:

* **Eventos gravados:**
1. Tentativas frustradas de login (E-mail informado e IP).
2. Falhas de validação de Token CSRF (IP e Rota).
3. Solicitações e redefinições de senha via token.
4. Alterações de senha executadas por usuários autenticados.


* **Formato:** `[YYYY-MM-DD HH:MM:SS] [SECURITY] Evento | UserID: X | IP: Y`

### 13.3. Contingência e Sanitização contra XSS

* **Mecanismo Secundário de Log:** Se o arquivo `logs/error.log` não puder ser escrito por permissão do SO, o Helper de Logger dispara `error_log()` nativo do PHP.
* **Proteção contra XSS:** Todas as saídas de texto dinâmicas renderizadas nas Views HTML devem utilizar obrigatoriamente a função de escape `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')` ou o helper `Sanitizer::e($valor)`.

---

## 14. Critérios de Aceitação Técnica e Funcional

1. **Design System:** As interfaces devem respeitar exatamente o layout *Admin Logic* (`docs/DESIGN.md`), aplicando cantos de 4px em botões/inputs, status pills com 100px de raio e fontes monetárias com numerais tabulares.
2. **Isolamento Total:** Um usuário autenticado **não pode sob nenhuma circunstância** visualizar, alterar ou excluir registros pertencentes a outro `usuario_id`.
3. **Precisão Financeira:** O saldo do mês deve corresponder exatamente a $\sum \text{Receitas} - \sum \text{Despesas}$ do período selecionado.
4. **Destaque do Saldo Negativo:** Quando o saldo do mês for menor que $R\$ 0,00$, seu valor deve obrigatoriamente ser formatado na cor vermelha (`#ba1a1a`).
5. **Confirmação de Exclusão:** Tanto para exclusão de lançamentos quanto para exclusão de categorias personalizadas, o sistema deve **exibir obrigatoriamente um modal de confirmação** antes de executar a requisição.
6. **Soft Delete Funcional:** Exclusões não utilizam `DELETE FROM`. Registros excluídos possuem `deleted_at` preenchido.
7. **Integridade de Histórico:** A exclusão de uma categoria personalizada não corrompe e nem oculta os lançamentos antigos vinculados a ela no extrato.
8. **Segurança de Configuração:** Não há uso de arquivo `.env`. O arquivo `config/config.php` é mantido fora do acesso web direto.
9. **Zero Exibição de Erro Técnico:** Exceções PHP ou MySQL não devem ser visíveis para o usuário final.

---

## 15. Plano de Implementação para IA Codificadora

As instruções abaixo estabelecem a ordem exata de construção a ser seguida pela IA codificadora:

1. **Etapa 1 - Banco de Dados e Infraestrutura Basal:**
* Criar a estrutura de diretórios e arquivos `.htaccess`.
* Executar o script DDL em `database/schema.sql`.
* Criar `config/config.php` e os helpers `Auth.php`, `CSRF.php`, `Logger.php` e `Sanitizer.php`.


2. **Etapa 2 - Layout Base e Assets:**
* Salvar os arquivos CSS do Bootstrap 5 e `admin-logic.css` em `public/assets/css/`.
* Criar os templates reutilizáveis `header.php`, `footer.php` e `sidebar.php`.


3. **Etapa 3 - Módulo de Autenticação:**
* Implementar `Usuario.php` (Model) e `AuthController.php`.
* Desenvolver as visões de Login, Cadastro, Solicitação de Recuperação e Redefinição de Senha via Token.


4. **Etapa 4 - Dashboard e Extrato:**
* Implementar `Lancamento.php` (Model) com consultas filtradas por mês/ano e `usuario_id`.
* Construir `DashboardController.php` e a visão do Dashboard com os KPI cards e alerta de saldo negativo.
* Construir `LancamentosController.php` e a visão do Extrato com filtros por categoria e busca textual.


5. **Etapa 5 - CRUD de Lançamentos e Modais:**
* Construir os modais de criação, edição e confirmação de exclusão de lançamentos.


6. **Etapa 6 - Gestão de Categorias Personalizadas:**
* Implementar `Categoria.php` (Model) e `CategoriasController.php`.
* Construir a tela de categorias e o **modal de confirmação de exclusão de categoria**.


7. **Etapa 7 - Perfil do Usuário e Alteração de Senha:**
* Implementar `PerfilController.php` e a visão de alteração de senha de usuário autenticado com gravação em `security.log`.


8. **Etapa 8 - Testes de Aceitação e Validação Final:**
* Validar isolamento de dados, funcionamento do Soft Delete, resposta aos tokens CSRF e ausência de vazamento de erros técnicos.