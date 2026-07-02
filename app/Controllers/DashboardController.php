<?php

class DashboardController
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

    public function resumo(): void
    {
        try {
            // Total de pessoas ativas
            $stmtPessoas = $this->pdo->query('SELECT COUNT(*) as total FROM pessoas WHERE status = "ativo"');
            $totalPessoas = $stmtPessoas->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Total de tipos ativos
            $stmtTipos = $this->pdo->query('SELECT COUNT(*) as total FROM tipos_atendimentos WHERE status = "ativo"');
            $totalTipos = $stmtTipos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Total de atendimentos
            $stmtAtendimentos = $this->pdo->query('SELECT COUNT(*) as total FROM atendimentos');
            $totalAtendimentos = $stmtAtendimentos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $this->json([
                'indicadores' => [
                    'total_pessoas' => (int) $totalPessoas,
                    'total_tipos' => (int) $totalTipos,
                    'total_atendimentos' => (int) $totalAtendimentos
                ]
            ]);
        } catch (PDOException $e) {
            $this->json(['erro' => 'Nao foi possivel recuperar os indicadores.'], 500);
        }
    }
}
