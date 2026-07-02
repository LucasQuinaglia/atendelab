<?php

class PessoasController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    private function json(array $dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }

    public function listar(): void
    {
        $sql = 'SELECT id, nome, documento, telefone, email,
                       curso, periodo, status, observacoes
                FROM pessoas
                ORDER BY nome';

        $this->json($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
    }

    public function buscar(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            $this->json(['erro' => 'ID invalido.'], 400);
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT id, nome, documento, telefone, email,
                    curso, periodo, status, observacoes
             FROM pessoas
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            $this->json(['erro' => 'Pessoa nao encontrada.'], 404);
            return;
        }

        $this->json($pessoa);
    }

    public function buscarPorId(): void
    {
        $this->buscar();
    }

    public function criar(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $status = $_POST['status'] ?? 'ativo';
        $observacoes = trim($_POST['observacoes'] ?? '');

        if ($nome === '' || $documento === '' || $email === '') {
            $this->json(['erro' => 'Nome, documento e e-mail sao obrigatorios.'], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['erro' => 'E-mail invalido.'], 422);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            $this->json(['erro' => 'Status invalido.'], 422);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO pessoas
                 (nome, documento, telefone, email, curso, periodo, status, observacoes)
                 VALUES
                 (:nome, :documento, :telefone, :email, :curso, :periodo, :status, :observacoes)'
            );
            $stmt->execute(compact(
                'nome',
                'documento',
                'telefone',
                'email',
                'curso',
                'periodo',
                'status',
                'observacoes'
            ));

            $this->json([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id' => (int) $this->pdo->lastInsertId()
            ], 201);
        } catch (PDOException $e) {
            $this->json(['erro' => 'Nao foi possivel cadastrar a pessoa.'], 400);
        }
    }

    public function atualizar(): void
    {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $status = $_POST['status'] ?? 'ativo';
        $observacoes = trim($_POST['observacoes'] ?? '');

        if (!$id || $nome === '' || $documento === '' || $email === '') {
            $this->json(['erro' => 'Dados obrigatorios ausentes.'], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['erro' => 'E-mail invalido.'], 422);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            $this->json(['erro' => 'Status invalido.'], 422);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE pessoas
                 SET nome = :nome,
                     documento = :documento,
                     telefone = :telefone,
                     email = :email,
                     curso = :curso,
                     periodo = :periodo,
                     status = :status,
                     observacoes = :observacoes
                 WHERE id = :id'
            );
            $stmt->execute(compact(
                'id',
                'nome',
                'documento',
                'telefone',
                'email',
                'curso',
                'periodo',
                'status',
                'observacoes'
            ));

            $this->json(['mensagem' => 'Pessoa atualizada com sucesso.']);
        } catch (PDOException $e) {
            $this->json(['erro' => 'Nao foi possivel atualizar a pessoa.'], 400);
        }
    }

    public function inativar(): void
    {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            $this->json(['erro' => 'ID invalido.'], 422);
            return;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE pessoas SET status = 'inativo' WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        $this->json(['mensagem' => 'Pessoa inativada com sucesso.']);
    }

    public function excluir(): void
    {
        $this->inativar();
    }
}
