<?php

require_once __DIR__ . '/app/Controllers/UsuariosController.php';

$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

if ($controller == 'usuarios') {

    $usuarioController = new UsuariosController();

    switch ($action) {

        case 'listar':
            $usuarioController->listar();
            break;

        case 'buscar':
            $usuarioController->buscarPorId();
            break;

        case 'criar':
            $usuarioController->criar();
            break;

        case 'atualizar':
            $usuarioController->atualizar();
            break;

        case 'excluir':
            $usuarioController->excluir();
            break;

        default:
            echo "Ação não encontrada.";
            break;
    }

} else {

    echo "<h1>AtendeLab</h1>";
    echo "<p>Projeto em execução.</p>";
}