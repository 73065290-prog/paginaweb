<?php
declare(strict_types=1);

/**
 * Repositorio de acceso a datos para usuarios y credenciales de autenticación.
 */
final class UsuarioRepository
{
    /**
     * Obtiene todos los usuarios activos con su rol asociado desde MySQL/MariaDB.
     * Retorna arreglo vacío cuando no hay conexión activa.
     *
     * @return array<int, array{usuario:string, password_hash:string, rol:string, nombre:string, cargo:string}>
     */
    public function allAuthUsers(): array
    {
        if (!class_exists('Database') || !Database::connected()) {
            return [];
        }
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

    /**
     * Busca un usuario activo en la fuente de usuarios proporcionada (o en BD si está conectada)
     * permitiendo coincidencia exacta por usuario/correo o por el alias previo a '@'.
     *
     * @param array<int, array{usuario:string, password_hash:string, rol:string, nombre:string, cargo:string}>|null $fuente
     * @return array{usuario:string, password_hash:string, rol:string, nombre:string, cargo:string}|null
     */
    public function findByIdentifier(string $identificador, ?array $fuente = null): ?array
    {
        $needle = mb_strtolower(trim($identificador));
        if ($needle === '') {
            return null;
        }

        $usuarios = $fuente ?? $this->allAuthUsers();
        foreach ($usuarios as $u) {
            $login = mb_strtolower((string) ($u['usuario'] ?? ''));
            $loginShort = explode('@', $login)[0];
            if ($needle === $login || $needle === $loginShort) {
                return [
                    'usuario' => (string) $u['usuario'],
                    'password_hash' => (string) ($u['password_hash'] ?? ''),
                    'rol' => (string) ($u['rol'] ?? ''),
                    'nombre' => (string) ($u['nombre'] ?? ''),
                    'cargo' => (string) ($u['cargo'] ?? ''),
                ];
            }
        }

        return null;
    }
}
