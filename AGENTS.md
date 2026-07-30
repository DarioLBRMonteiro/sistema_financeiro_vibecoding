# Contexto do Projeto - Gestão Financeira Simples

Este arquivo serve como contexto global para qualquer assistente de Inteligência Artificial que venha a trabalhar neste repositório.

## Idioma
Responda sempre em **português do Brasil**.

---

## 1. Stack, Arquitetura e Restrições Técnicas (FSD)

* **Linguagem Back-end:** PHP 8.x nativo (sem frameworks como Laravel, Symfony ou Slim).
* **Banco de Dados:** MySQL 8.x. Acesso exclusivo via PDO com *prepared statements* obrigatórios.
* **Front-end:** HTML5, CSS3, JavaScript puro (Vanilla JS), Bootstrap 5 (CSS/JS locais) e Chart.js v4.x (JS local UMD em `public/assets/js/chart.umd.js`).
* **Dependências Externas:** Proibido o uso de Composer, NPM, Webpack ou CDNs para CSS/JS em tempo de execução (exceto a CDN de fontes/ícones do Google).
* **Padrão Arquitetural:** MVC (Model-View-Controller) com Front Controller (`public/index.php`) e reescrita de URL.
* **Bloqueio de Acesso Direto:** Arquivos de visão e configuração contêm obrigatoriamente a verificação:
  ```php
  defined('APP_EXEC') or die('Acesso direto não permitido.');
  ```

---

## 2. Ambientes do Sistema

* **Desenvolvimento:** Ambiente local baseado no Apache/PHP (compatível com XAMPP). Erros detalhados do PHP/PDO devem ser gravados nos logs internos, mas nunca exibidos na tela.
* **Produção:** Servidor Apache com isolamento rígido de diretórios sensíveis e supressão total de erros técnicos em tela.
* **Configuração:** Armazenada unicamente em `config/config.php` retornando um array PHP. Não utilizar arquivos `.env`.

---

## 3. Estrutura de Pastas do Projeto

```text
├── app/
│   ├── controllers/      # Processamento das requisições e controle de fluxo
│   ├── models/           # Regras de negócio e consultas PDO (isoladas por usuário)
│   ├── views/            # Visões HTML renderizadas no servidor
│   │   ├── auth/         # Telas de login, cadastro, recuperação e redefinição de senha
│   │   ├── categorias/   # Tela de gerenciamento de categorias
│   │   ├── dashboard/    # Tela do painel financeiro principal
│   │   ├── extrato/      # Tela de histórico e extrato
│   │   ├── perfil/       # Tela de alteração de dados do perfil (senha)
│   │   └── templates/    # Templates reutilizáveis (header, footer, sidebar)
│   └── helpers/          # Funções de suporte (Auth, CSRF, Logger, Sanitizer)
├── config/               # Arquivos de configuração (config.php e database.php)
├── database/             # Script SQL de DDL e carga inicial (schema.sql)
├── logs/                 # Logs de erros e segurança do sistema
├── public/               # Raiz pública acessível pelo navegador
│   ├── assets/           # Arquivos estáticos (css, js, imagens)
│   └── index.php         # Front Controller e roteador do sistema
└── docs/                 # Documentação de apoio do projeto
```

---

## 4. Comandos e Execução do Projeto

* **Instalação:**
  1. Configurar o banco de dados MySQL executando o script `database/schema.sql`.
  2. Duplicar ou ajustar `config/config.php` informando as credenciais de banco correspondentes.
  3. Iniciar o servidor web Apache e MySQL local (ex: através do painel do XAMPP).
* **Execução:** Acessar a URL local configurada em `app_url` (padrão: `http://localhost/gestao-financeira` ou similar).
* **Testes/Validação:** Validação visual no navegador e verificação manual dos logs em `logs/error.log` e `logs/security.log`.

---

## 5. Regras de Segurança do Projeto

* **Isolamento de Dados (Multitenancy Rígido):** Toda operação SQL executada para lançamentos e categorias personalizadas deve restringir a consulta ao ID do usuário autenticado:
  ```sql
  WHERE usuario_id = :usuario_id
  ```
  O ID do usuário logado é extraído de `$_SESSION['user_id']`.
* **Proteção contra Injeção SQL:** Uso obrigatório de Prepared Statements com PDO para qualquer query dinâmica.
* **Proteção contra XSS:** Todas as saídas de texto dinâmicas nas views devem ser escapadas usando o helper `Sanitizer::e($valor)`.
* **Proteção contra CSRF:** Todas as requisições de alteração de estado (POST) devem validar um token CSRF único gerado na sessão (`CSRF::validateToken($_POST['csrf_token'])`). Se inválido, abortar com HTTP 403 e registrar em `logs/security.log`.
* **Gestão de Sessão Segura:** Sessão iniciada no Front Controller com parâmetros reforçados:
  ```php
  session_start([
      'cookie_httponly' => true,
      'cookie_samesite' => 'Lax',
      'use_strict_mode' => true,
  ]);
  ```
  E regeneração imediata do ID de sessão no login com `session_regenerate_id(true)`.
* **Criptografia de Senhas:** Hashes gerados com `password_hash($senha, PASSWORD_BCRYPT)` e verificados com `password_verify($senha, $hash)`.
* **Proteção de Arquivos Sensíveis:** O acesso público direto aos diretórios `config/`, `logs/` e `app/` é bloqueado via arquivos `.htaccess`.
* **Segurança de Logs:** Erros técnicos detalhados (exceções PDO, falhas PHP) são capturados e gravados silenciosamente em `logs/error.log`. Mensagens amigáveis genéricas são exibidas ao usuário. Auditorias de conta são salvas em `logs/security.log`.

---

## 6. Boas Práticas e Padrões de Código

* **Código Limpo:** Escrever código claro, legível e de responsabilidade única. Funções pequenas e nomes de variáveis/métodos altamente descritivos em português (ou inglês para estruturas técnicas comuns, desde que padronizados).
* **Comentários:** Úteis e escritos em português do Brasil quando agregarem valor explicativo.
* **Sem Duplicação:** Evitar código repetido. Reaproveitar blocos comuns através de helpers ou views de templates.
* **Interface (Design System Admin Logic):** Seguir as especificações de cores, fontes, bordas (4px), status pills (100px), numerais tabulares e densidade de dados contidas em `docs/DESIGN.md`.

---

## 7. Protocolo dos Arquivos Vivos (Uso Obrigatório)

Antes de iniciar qualquer trabalho:
1. Ler `docs/FSD.md`.
2. Ler `docs/DESIGN.md`.
3. Ler `docs/INSUMOS.md`.
4. Ler `docs/PLANO.md`.
5. Ler `docs/STATUS.md`.
6. Ler `docs/ERROS.md`.

Use sempre caminhos relativos à raiz do projeto.
Não transformar estes caminhos em links absolutos.
Não usar links `file:///`.
Não registrar caminhos locais da máquina atual dentro do `AGENTS.md`.

Ao terminar qualquer trabalho:
1. Atualizar `docs/STATUS.md`.
2. Registrar erros e soluções em `docs/ERROS.md`, se houver.
3. Informar ao usuário o que foi feito.
4. Informar como testar ou validar a entrega.
