<?php
declare(strict_types=1);

final class AuthService
{
    /**
     * @return array{ok:bool, errores:array<int,string>, usuario:?array<string,mixed>}
     */
    public function attempt(string $usuario, string $password, string $rol = 'admin'): array
    {
        return auth_intentar($usuario, $password, $rol);
    }

    public function login(array $usuario, bool $recordar = false): void
    {
        auth_login($usuario, $recordar);
    }

    public function logout(): void
    {
        auth_logout();
    }
}
