<?php

class AtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Busca dados do atendimento trazendo os nomes das tabelas relacionadas
        $sql = 'SELECT a.id, a.descricao, a.data_atendimento, a.status,
                       p.nome AS pessoa_nome, 
                       t.nome AS tipo_atendimento_nome, 
                       u.nome AS usuario_nome
                FROM atendimentos a
                JOIN pessoas p ON a.pessoa_id = p.id
                JOIN tipos_atendimentos t ON a.tipo_atendimento_id = t.id
                JOIN usuarios u ON a.usuario_id = u.id
                ORDER BY a.id DESC';

        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $sql = 'SELECT a.id, a.pessoa_id, a.tipo_atendimento_id, a.usuario_id, 
                       a.descricao, a.data_atendimento, a.status,
                       p.nome AS pessoa_nome, 
                       t.nome AS tipo_atendimento_nome, 
                       u.nome AS usuario_nome
                FROM atendimentos a
                JOIN pessoas p ON a.pessoa_id = p.id
                JOIN tipos_atendimentos t ON a.tipo_atendimento_id = t.id
                JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento não encontrado.']);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoa_id = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipo_atendimento_id = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'aberto';

        if (!$pessoa_id || !$tipo_atendimento_id || !$usuario_id) {
            http_response_code(400);
            echo json_encode(['erro' => 'Os campos pessoa_id, tipo_atendimento_id e usuario_id são obrigatórios e devem ser inteiros.']);
            return;
        }

        if (!in_array($status, ['aberto', 'em_andamento', 'finalizado', 'cancelado'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'INSERT INTO atendimentos (pessoa_id, tipo_atendimento_id, usuario_id, descricao, status)
                    VALUES (:pessoa_id, :tipo_atendimento_id, :usuario_id, :descricao, :status)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipo_atendimento_id, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Atendimento registrado com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao registrar atendimento. Verifique as chaves estrangeiras.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $pessoa_id = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipo_atendimento_id = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'aberto';

        if (!$id || !$pessoa_id || !$tipo_atendimento_id || !$usuario_id) {
            http_response_code(400);
            echo json_encode(['erro' => 'Todos os IDs (id, pessoa_id, tipo_atendimento_id, usuario_id) são obrigatórios.']);
            return;
        }

        if (!in_array($status, ['aberto', 'em_andamento', 'finalizado', 'cancelado'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'UPDATE atendimentos
                    SET pessoa_id = :pessoa_id,
                        tipo_atendimento_id = :tipo_atendimento_id,
                        usuario_id = :usuario_id,
                        descricao = :descricao,
                        status = :status
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipo_atendimento_id, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar atendimento. Verifique os dados informados.']);
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $sql = 'DELETE FROM atendimentos WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Atendimento excluído com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir atendimento.']);
        }
    }
}