<?php
declare(strict_types=1);

final class AuthController
{
    public function loginForm(): void
    {
        require MASS_ROOT . '/frontend/views/admin/login.php';
    }
}
