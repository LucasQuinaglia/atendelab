<?php

class TiposAtendimentosController
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
        $sql = 'SELECT id, nome, descricao, status
                FROM tipos_atendimentos
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
            'SELECT id, nome, descricao, status
             FROM tipos_atendimentos
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $tipo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tipo) {
            $this->json(['erro' => 'Tipo nao encontrado.'], 404);
            return;
        }

        $this->json($tipo);
    }

    public function buscarPorId(): void
    {
        $this->buscar();
    }

    public function criar(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if ($nome === '') {
            $this->json(['erro' => 'Nome e obrigatorio.'], 422);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            $this->json(['erro' => 'Status invalido.'], 422);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO tipos_atendimentos (nome, descricao, status)
                 VALUES (:nome, :descricao, :status)'
            );
            $stmt->execute(compact('nome', 'descricao', 'status'));

            $this->json([
                'mensagem' => 'Tipo cadastrado com sucesso.',
                'id' => (int) $this->pdo->lastInsertId()
            ], 201);
        } catch (PDOException $e) {
            $this->json(['erro' => 'Nao foi possivel cadastrar o tipo.'], 400);
        }
    }

    public function atualizar(): void
    {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if (!$id || $nome === '') {
            $this->json(['erro' => 'ID e nome sao obrigatorios.'], 422);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            $this->json(['erro' => 'Status invalido.'], 422);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE tipos_atendimentos
                 SET nome = :nome,
                     descricao = :descricao,
                     status = :status
                 WHERE id = :id'
            );
            $stmt->execute(compact('id', 'nome', 'descricao', 'status'));

            $this->json(['mensagem' => 'Tipo atualizado com sucesso.']);
        } catch (PDOException $e) {
            $this->json(['erro' => 'Nao foi possivel atualizar o tipo.'], 400);
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
            "UPDATE tipos_atendimentos SET status = 'inativo' WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        $this->json(['mensagem' => 'Tipo inativado com sucesso.']);
    }

    public function excluir(): void
    {
        $this->inativar();
    }
}
