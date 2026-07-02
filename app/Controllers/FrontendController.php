<?php

class FrontendController
{
    public function pessoas(): void
    {
        require_once __DIR__ . '/../Views/layouts/header.php';
        require_once __DIR__ . '/../Views/pessoas/index.php';
        require_once __DIR__ . '/../Views/layouts/footer.php';
    }

    public function tipos(): void
    {
        require_once __DIR__ . '/../Views/layouts/header.php';
        require_once __DIR__ . '/../Views/tipos-atendimentos/index.php';
        require_once __DIR__ . '/../Views/layouts/footer.php';
    }

    public function atendimentos(): void
    {
        require_once __DIR__ . '/../Views/layouts/header.php';
        require_once __DIR__ . '/../Views/atendimentos/index.php';
        require_once __DIR__ . '/../Views/layouts/footer.php';
    }
}
