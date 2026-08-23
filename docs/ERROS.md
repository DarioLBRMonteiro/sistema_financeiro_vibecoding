# Registro de Erros e Soluções - Gestão Financeira Simples

Este arquivo é um registro dinâmico utilizado para catalogar erros encontrados durante o desenvolvimento e a homologação do sistema Gestão Financeira Simples, documentando suas causas, soluções aplicadas e estratégias de prevenção.

Ao encontrar e solucionar qualquer erro relevante no sistema, faça um novo registro no topo deste arquivo utilizando o modelo abaixo.

---

## Modelo de Registro

```text
## <data> - <título curto do erro>

- Sintoma: <descrever o comportamento observado na tela ou log que denunciou o erro>
- Causa: <descrever a causa raiz técnica, falha lógica ou inconsistência>
- Solução aplicada: <descrever as modificações efetuadas para resolver o erro>
- Como evitar no futuro: <descrever boas práticas ou testes a serem feitos para que o erro não se repita>
```

---

## Histórico de Erros

## 23/08/2026 - Call to undefined method Database::getInstance()

- Sintoma: Erro fatal "Call to undefined method Database::getInstance()" na tela ao acessar a rota de Categorias (/categorias).
- Causa: O model `Categoria.php` estava tentando instanciar a conexão com o banco de dados utilizando o método `Database::getInstance()`, mas a classe base de configuração do projeto definiu o método como `Database::getConnection()`.
- Solução aplicada: Alteração da chamada de `Database::getInstance()` para `Database::getConnection()` no construtor de `Categoria.php`.
- Como evitar no futuro: Verificar as assinaturas de métodos nas classes de configuração de infraestrutura (como banco de dados e sessão) antes de utilizá-los em novas models, não presumindo nomes genéricos comuns como `getInstance()`.

## 13/08/2026 - Call to undefined method Auth::requireLogin()

- Sintoma: Erro fatal "Call to undefined method Auth::requireLogin()" exibido em tela ao acessar a rota do Dashboard (/dashboard).
- Causa: O controller estava tentando chamar o método de restrição de acesso como `Auth::requireLogin()`, mas o ajudante `Auth.php` implementado na Fase 3 nomeou esse método como `Auth::requireAuth()`.
- Solução aplicada: O método foi renomeado de `Auth::requireLogin()` para `Auth::requireAuth()` dentro de `DashboardController.php` e `LancamentosController.php`.
- Como evitar no futuro: Sempre consultar a implementação real da classe base antes de presumir o nome de um método, garantindo assim que a assinatura e o nome estejam de acordo com as fases anteriores.
