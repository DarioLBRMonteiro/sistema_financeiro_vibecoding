# Contexto do Projeto - Gestão Financeira Simples (Modo Manutenção)

Este arquivo serve como contexto global e guia de atuação em **Modo Manutenção** para qualquer assistente de Inteligência Artificial que venha a trabalhar neste repositório.

## Idioma
Responda sempre em **português do Brasil**.

---

## 1. Stack, Arquitetura e Restrições Técnicas

* **Linguagem Back-end:** PHP 8.x nativo (sem frameworks como Laravel, Symfony ou Slim).
* **Banco de Dados:** MySQL 8.x. Acesso exclusivo via PDO com *prepared statements* obrigatórios.
* **Front-end:** HTML5, CSS3, JavaScript puro (Vanilla JS), Bootstrap 5 (CSS/JS locais) e Chart.js v4.x (JS local UMD em `public/assets/js/chart.umd.js`).
* **Dependências Externas:** Proibido o uso de Composer, NPM, Webpack ou CDNs para CSS/JS em tempo de execução (exceto a CDN de fontes/ícones do Google).
* **Padrão Arquitetural:** MVC (Model-View-Controller) com Front Controller (`public/index.php`) e reescrita de URL via `.htaccess`.
* **Bloqueio de Acesso Direto:** Arquivos de visão e configuração contêm obrigatoriamente a verificação:
  ```php
  defined('APP_EXEC') or die('Acesso direto não permitido.');
  ```

---

## 2. Ambientes do Sistema

* **Desenvolvimento:** Ambiente local baseado no Apache/PHP (compatível com XAMPP). Erros detalhados do PHP/PDO devem ser gravados silenciosamente em `logs/error.log` e nunca exibidos na tela.
* **Produção:** Servidor Apache com isolamento rígido de diretórios sensíveis (`.htaccess` com `Deny from all`), suporte a HTTPS e supressão total de erros técnicos em tela (`'env' => 'producao'`).
* **Configuração:** Armazenada unicamente em `config/config.php` retornando um array PHP. Não utilizar arquivos `.env`.

---

## 3. Estrutura de Pastas do Projeto

```text
├── app/
│   ├── controllers/      # Processamento das requisições e controle de fluxo
│   ├── models/           # Regras de negócio e consultas PDO (isoladas por usuario_id)
│   ├── views/            # Visões HTML renderizadas no servidor
│   │   ├── auth/         # Telas de login, cadastro, recuperação e redefinição de senha
│   │   ├── categorias/   # Tela de gerenciamento de categorias
│   │   ├── dashboard/    # Tela do painel financeiro principal
│   │   ├── extrato/      # Tela de histórico e extrato de lançamentos
│   │   ├── perfil/       # Tela de alteração de senha
│   │   └── templates/    # Templates reutilizáveis (header, footer, sidebar)
│   └── helpers/          # Funções de suporte (Auth, CSRF, Logger, Sanitizer)
├── config/               # Arquivos de configuração (config.php e database.php)
├── database/             # Script SQL de DDL e carga inicial (schema.sql)
├── logs/                 # Logs de erros e segurança do sistema
├── public/               # Raiz pública acessível pelo navegador
│   ├── assets/           # Arquivos estáticos (css, js, imagens)
│   └── index.php         # Front Controller e roteador do sistema
└── docs/                 # Documentação (FSD, DESIGN, MANUTENCAO, COMO-PEDIR-MUDANCAS, STATUS, ERROS)
```

---

## 4. Protocolo Obrigatório para Mudanças Futuras

### Antes de qualquer alteração:
1. Ler `docs/MANUTENCAO.md`.
2. Ler `docs/FSD.md`.
3. Ler `docs/DESIGN.md`, se a alteração envolver interface ou componentes visuais.
4. Ler `docs/STATUS.md`.
5. Ler `docs/ERROS.md`.
6. Entender integralmente o pedido do usuário.
7. Explicar o plano de implementação antes de alterar qualquer arquivo.

### Depois de qualquer alteração:
1. Testar o que foi alterado (sintaxe via PHP Lint e testes manuais de fluxo).
2. Atualizar `docs/STATUS.md`.
3. Registrar o erro e a solução em `docs/ERROS.md`, se algum bug for encontrado ou corrigido.
4. Fazer commit no Git ou entregar os comandos para o usuário executar.
5. Explicar ao usuário como testar e validar as alterações no navegador.

---

## 5. Regras de Segurança Inegociáveis

* **Isolamento de Dados (Multitenancy Rígido):** Toda operação SQL executada em `lancamentos` ou `categorias` personalizadas deve restringir a consulta ao ID do usuário autenticado:
  ```sql
  WHERE usuario_id = :usuario_id
  ```
  O ID do usuário logado deve ser obrigatoriamente extraído de `$_SESSION['user_id']`.
* **Proteção contra Injeção SQL:** Uso obrigatório de Prepared Statements via PDO com `PDO::ATTR_EMULATE_PREPARES => false`.
* **Proteção contra XSS:** Todas as saídas de texto dinâmicas nas views devem ser escapadas utilizando o helper `Sanitizer::e($valor)`.
* **Proteção contra CSRF:** Todas as requisições POST devem validar o token CSRF via `CSRF::validateToken($_POST['csrf_token'] ?? '')`. Se inválido, abortar com HTTP 403.
* **Gestão de Sessão Segura:** Manter inicialização segura da sessão em `public/index.php` e regenerar o ID via `session_regenerate_id(true)` no login.
* **Soft Delete:** Nunca utilizar `DELETE FROM` em movimentações ou categorias. Atualizar `deleted_at = NOW()`.
* **Proteção de Diretórios Sensíveis:** Manter arquivos `.htaccess` (`Deny from all`) nos diretórios `app/`, `config/`, `database/`, `docs/` e `logs/`.

---

## 6. Padrões de Código e Interface

* **Código Limpo:** Escrever código em português do Brasil (ou inglês para termos técnicos comuns), claro, modular e de responsabilidade única.
* **Interface (Design System Admin Logic):** Seguir as especificações contidas em `docs/DESIGN.md` (cores `#0f2d7b`/`#10b981`/`#ba1a1a`, fontes Public Sans/Inter, bordas de 4px, status pills de 100px e numerais tabulares `data-mono`).
* **Caminhos Relativos:** Usar sempre caminhos relativos à raiz do projeto nos documentos. Nunca utilizar links absolutos `file:///`.
