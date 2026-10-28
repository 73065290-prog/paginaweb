<?php
declare(strict_types=1);

final class UsuarioRepository
{
    public function allAuthUsers(): array
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return [];
        }
        $stmt = $pdo->query(
            'SELECT u.usuario, u.password_hash, u.nombre, u.cargo, r.nombre AS rol
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.activo = 1'
        );
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[] = [
                'usuario' => (string) $row['usuario'],
                'password_hash' => (string) $row['password_hash'],
                'rol' => (string) $row['rol'],
                'nombre' => (string) $row['nombre'],
                'cargo' => (string) ($row['cargo'] ?? ''),
            ];
        }
        return $rows;
    }
}
