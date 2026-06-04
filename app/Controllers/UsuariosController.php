<?php
// Controller da entidade de usuários.
// Em uma arquitetura MVC, ele recebe a requisição, valida dados e acessa o banco.
class UsuariosController
{
    // Conexão PDO reutilizada em todos os métodos.
    private PDO $pdo;

    public function __construct()
    {
        // Importa o arquivo que inicializa o objeto $pdo.
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        // Define saída em JSON para APIs/consumo por front-end.
        header('Content-Type: application/json; charset=utf-8');

        // Consulta todos os usuários com ordenação decrescente por ID.
        $sql = 'SELECT id, nome, email, perfil, status, criado_em
                FROM usuarios
                ORDER BY id DESC';

        $stmt = $this->pdo->query($sql);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // JSON_PRETTY_PRINT melhora leitura em desenvolvimento.
        echo json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Lê e valida o ID recebido por GET.
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        // Consulta parametrizada evita SQL Injection.
        $sql = 'SELECT id, nome, email, perfil, status, criado_em
                FROM usuarios
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['erro' => 'Usuário não encontrado.']);
            return;
        }

        echo json_encode($usuario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Coleta dados do formulário (POST).
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $perfil = $_POST['perfil'] ?? 'atendente';
        $status = $_POST['status'] ?? 'ativo';

        // Regras mínimas de validação de entrada.
        if ($nome === '' || $email === '' || $senha === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome, e-mail e senha são obrigatórios.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido.']);
            return;
        }

        // Whitelist de valores válidos para campos de domínio.
        if (!in_array($perfil, ['admin', 'atendente', 'aluno'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Perfil inválido.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        // Nunca armazenar senha em texto puro.
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $sql = 'INSERT INTO usuarios (nome, email, senha, perfil, status)
                    VALUES (:nome, :email, :senha, :perfil, :status)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':senha', $senhaHash);
            $stmt->bindValue(':perfil', $perfil);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Usuário cadastrado com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            // Em produção, registre $e em log em vez de expor detalhes.
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar usuário.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // ID vem no POST para operação de update.
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $perfil = $_POST['perfil'] ?? 'atendente';
        $status = $_POST['status'] ?? 'ativo';

        if (!$id || $nome === '' || $email === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID, nome e e-mail são obrigatórios.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido.']);
            return;
        }

        if (!in_array($perfil, ['admin', 'atendente', 'aluno'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Perfil inválido.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'UPDATE usuarios
                    SET nome = :nome,
                        email = :email,
                        perfil = :perfil,
                        status = :status
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':perfil', $perfil);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Usuário atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar usuário.']);
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Exclusão por ID recebido no corpo da requisição.
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $sql = 'DELETE FROM usuarios WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Usuário excluído com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir usuário.']);
        }
    }
}