<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login – AtendeLab</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-2">AtendeLab</h1>
                        <p class="text-muted">
                            Informe suas credenciais para acessar o sistema.
                        </p>

                        <?php if (!empty($erro)): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars(
                                    $erro,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mensagem)): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars(
                                    $mensagem,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="?controller=auth&action=entrar">

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    E-mail
                                </label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">
                                    Senha
                                </label>
                                <input type="password" name="senha" id="senha" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Entrar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>