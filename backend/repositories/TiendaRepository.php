<?php
declare(strict_types=1);

final class TiendaRepository
{
    public function all(): array
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return [];
        }
        $stmt = $pdo->query('SELECT * FROM tiendas');
        $rows = [];
        foreach ($stmt->fetchAll() as $t) {
            $rows[] = [
                'id' => (string) $t['id'],
                'nombre' => (string) $t['nombre'],
                'direccion' => (string) ($t['direccion'] ?? ''),
                'distrito' => (string) ($t['distrito'] ?? ''),
                'stock' => (int) ($t['stock'] ?? 0),
                'eta' => (string) ($t['eta'] ?? ''),
                'estado' => (string) ($t['estado'] ?? 'ok'),
                'img' => (string) ($t['img'] ?? ''),
                'distancia' => (string) ($t['eta'] ?? ''),
                'estadoLabel' => (string) ($t['estado'] ?? ''),
                'misma' => ($t['id'] ?? '') === 'magdalena',
            ];
        }
        return $rows;
    }

    public function find(string $id): ?array
    {
        foreach ($this->all() as $t) {
            if (($t['id'] ?? '') === $id) {
                return $t;
            }
        }
        return null;
    }
}
