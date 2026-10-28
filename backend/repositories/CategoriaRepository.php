<?php
declare(strict_types=1);

final class CategoriaRepository
{
    public function all(): array
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return [];
        }
        $stmt = $pdo->query('SELECT * FROM categorias ORDER BY nombre ASC');
        $rows = [];
        foreach ($stmt->fetchAll() as $c) {
            $rows[] = [
                'id' => (string) $c['id'],
                'nombre' => (string) $c['nombre'],
                'badge' => (string) ($c['badge'] ?? ''),
                'badgeTone' => (string) ($c['badge_tone'] ?? ''),
                'count' => (int) ($c['count_hint'] ?? 0),
                'cat' => (string) ($c['cat_filtro'] ?? ''),
                'img' => (string) ($c['img'] ?? ''),
            ];
        }
        return $rows;
    }
}
