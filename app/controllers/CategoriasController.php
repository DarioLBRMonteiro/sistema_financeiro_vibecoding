<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

require_once dirname(__DIR__) . '/models/Categoria.php';

class CategoriasController
{
    private $categoriaModel;

    public function __construct()
    {
        Auth::requireAuth(); // Requer que o usuário esteja logado
        $this->categoriaModel = new Categoria();
    }

    /**
     * Exibe a tela de gerenciamento de categorias
     */
    public function index()
    {
        $usuario_id = $_SESSION['user_id'];
        
        try {
            $categorias = $this->categoriaModel->listarAtivasPorUsuario($usuario_id);
            
            // Separação para facilitar a view, ou passa tudo junto.
            $categoriasSistema = [];
            $categoriasUsuario = [];
            
            foreach ($categorias as $cat) {
                if ($cat['usuario_id'] === null) {
                    $categoriasSistema[] = $cat;
                } else {
                    $categoriasUsuario[] = $cat;
                }
            }
            
            require_once dirname(__DIR__) . '/views/categorias/index.php';
        } catch (Exception $e) {
            Logger::logError("Erro ao carregar categorias: " . $e->getMessage(), $e);
            die("Ocorreu um erro interno ao processar sua solicitação. Tente novamente mais tarde.");
        }
    }

    /**
     * Adiciona uma nova categoria personalizada
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Auth::redirect('/categorias');
            return;
        }

        // Validação CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        CSRF::validateToken($csrfToken);

        $usuario_id = $_SESSION['user_id'];
        $nome = trim($_POST['nome'] ?? '');
        $tipo = $_POST['tipo'] ?? '';

        // Validações
        if (empty($nome) || mb_strlen($nome) > 50) {
            $_SESSION['error'] = "O nome da categoria é obrigatório (máx. 50 caracteres).";
            Auth::redirect('/categorias');
            return;
        }

        if (!in_array($tipo, ['RECEITA', 'DESPESA'])) {
            $_SESSION['error'] = "Tipo de categoria inválido.";
            Auth::redirect('/categorias');
            return;
        }

        try {
            // Verificar duplicidade
            if ($this->categoriaModel->verificarDuplicidade($usuario_id, $nome, $tipo)) {
                $_SESSION['error'] = "O nome da categoria deve ser único para o seu tipo.";
                Auth::redirect('/categorias');
                return;
            }

            // Inserir
            if ($this->categoriaModel->adicionar($usuario_id, $nome, $tipo)) {
                $_SESSION['success'] = "Categoria criada com sucesso!";
            } else {
                $_SESSION['error'] = "Não foi possível criar a categoria.";
            }
        } catch (Exception $e) {
            Logger::logError("Erro ao criar categoria para usuario {$usuario_id}: " . $e->getMessage(), $e);
            $_SESSION['error'] = "Ocorreu um erro interno ao criar a categoria. Tente novamente mais tarde.";
        }

        Auth::redirect('/categorias');
    }

    /**
     * Exclui logicamente uma categoria personalizada
     */
    public function excluir()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Auth::redirect('/categorias');
            return;
        }

        // Validação CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        CSRF::validateToken($csrfToken);

        $usuario_id = $_SESSION['user_id'];
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);

        if (!$id) {
            $_SESSION['error'] = "Categoria inválida.";
            Auth::redirect('/categorias');
            return;
        }

        try {
            if ($this->categoriaModel->excluirLogicamente($id, $usuario_id)) {
                $_SESSION['success'] = "Categoria excluída com sucesso!";
            } else {
                $_SESSION['error'] = "Não foi possível excluir a categoria ou ela pertence ao sistema.";
            }
        } catch (Exception $e) {
            Logger::logError("Erro ao excluir categoria ID {$id} do usuario {$usuario_id}: " . $e->getMessage(), $e);
            $_SESSION['error'] = "Ocorreu um erro interno ao excluir a categoria.";
        }

        Auth::redirect('/categorias');
    }
}
