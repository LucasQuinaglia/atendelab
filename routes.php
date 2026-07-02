<?php

require_once __DIR__ . '/app/Middleware/auth.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';
require_once __DIR__ . '/app/Controllers/FrontendController.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';

$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

if ($controller === 'auth') {
    $auth = new AuthController();

    switch ($action) {
        case 'login':
            $auth->exibirLogin();
            break;

        case 'entrar':
            $auth->entrar();
            break;

        case 'dashboard':
            exigirAutenticacao();
            $auth->dashboard();
            break;

        case 'logout':
            $auth->logout();
            break;

        default:
            http_response_code(404);
            echo 'Acao de autenticacao nao encontrada.';
    }

    exit;
}

if ($controller === 'frontend') {
    exigirAutenticacao();
    $controller = new FrontendController();
    if (!method_exists($controller, $action)) {
        http_response_code(404);
        exit('Acao nao encontrada.');
    }
    $controller->$action();
    exit;
}

if ($controller === 'dashboard') {
    exigirAutenticacao();
    $controller = new DashboardController();
    if (!method_exists($controller, $action)) {
        http_response_code(404);
        exit('Acao nao encontrada.');
    }
    $controller->$action();
    exit;
}

exigirAutenticacao();

switch ($controller) {
    case 'usuarios':
        $obj = new UsuariosController();
        break;

    case 'pessoas':
        $obj = new PessoasController();
        break;

    case 'tipos':
        $obj = new TiposAtendimentosController();
        break;

    case 'atendimentos':
        $obj = new AtendimentosController();
        break;

    default:
        http_response_code(404);
        exit('Controller nao encontrado.');
}

if ($action === 'buscarPorId' && method_exists($obj, 'buscar')) {
    $action = 'buscar';
}

if ($action === 'excluir' && method_exists($obj, 'inativar')) {
    $action = 'inativar';
}

if (str_starts_with($action, '__') || !is_callable([$obj, $action])) {
    http_response_code(404);
    exit('Acao nao encontrada.');
}

$obj->$action();
