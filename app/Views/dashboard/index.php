<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Dashboard – AtendeLab</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">AtendeLab</span>

        <a class="btn btn-outline-light btn-sm"
           href="?controller=auth&action=logout">
            Sair
        </a>
    </div>
</nav>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4">Area restrita</h1>

            <p class="mb-1">
                Bem-vindo,
                <strong>
                    <?= htmlspecialchars(
                        $usuario['nome'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>.
            </p>

            <p class="text-muted">
                Perfil:
                <?= htmlspecialchars(
                    $usuario['perfil'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <a class="btn btn-primary"
               href="?controller=usuarios&action=listar">
                Testar rota protegida de usuarios
            </a>
        </div>
    </div>
</div>

</body>
</html>