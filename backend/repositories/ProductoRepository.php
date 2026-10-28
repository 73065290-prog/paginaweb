<?php
declare(strict_types=1);

final class ProductoRepository
{
    public function all(): array
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return [];
        }
        $stmt = $pdo->query('SELECT * FROM productos ORDER BY id ASC');
        return array_map([$this, 'mapRow'], $stmt->fetchAll());
    }

    public function find(int $id): ?array
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return null;
        }
        $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->mapRow($row) : null;
    }

    public function create(array $data): int
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return 0;
        }
        $stmt = $pdo->prepare(
            'INSERT INTO productos (nombre, marca, categoria, subcategoria, unidad, descripcion, ean, precio, costo, stock_tone, img, img_nombre, punto_reorden, capacidad_gondola, abc, activo)
             VALUES (:nombre, :marca, :categoria, :subcategoria, :unidad, :descripcion, :ean, :precio, :costo, :stock_tone, :img, :img_nombre, :punto_reorden, :capacidad_gondola, :abc, :activo)'
        );
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':marca' => $data['marca'] ?? 'MASS',
            ':categoria' => $data['categoria'] ?? '',
            ':subcategoria' => $data['subcategoria'] ?? '',
            ':unidad' => $data['unidad'] ?? '',
            ':descripcion' => $data['descripcion'] ?? '',
            ':ean' => $data['ean'] ?? null,
            ':precio' => $data['precio'] ?? 0,
            ':costo' => $data['costo'] ?? 0,
            ':stock_tone' => $data['stockTone'] ?? 'ok',
            ':img' => $data['img'] ?? '',
            ':img_nombre' => $data['imgNombre'] ?? '',
            ':punto_reorden' => $data['puntoReorden'] ?? 10,
            ':capacidad_gondola' => $data['capacidadGondola'] ?? 50,
            ':abc' => $data['abc'] ?? 'B',
            ':activo' => !empty($data['activo']) ? 1 : 0,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return false;
        }
        $stmt = $pdo->prepare(
            'UPDATE productos SET
                nombre = :nombre, marca = :marca, categoria = :categoria, subcategoria = :subcategoria,
                unidad = :unidad, descripcion = :descripcion, ean = :ean, precio = :precio, costo = :costo,
                punto_reorden = :punto_reorden, capacidad_gondola = :capacidad_gondola, abc = :abc, activo = :activo
             WHERE id = :id'
        );
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':marca' => $data['marca'] ?? 'MASS',
            ':categoria' => $data['categoria'] ?? '',
            ':subcategoria' => $data['subcategoria'] ?? '',
            ':unidad' => $data['unidad'] ?? '',
            ':descripcion' => $data['descripcion'] ?? '',
            ':ean' => $data['ean'] ?? null,
            ':precio' => $data['precio'] ?? 0,
            ':costo' => $data['costo'] ?? 0,
            ':punto_reorden' => $data['puntoReorden'] ?? 10,
            ':capacidad_gondola' => $data['capacidadGondola'] ?? 50,
            ':abc' => $data['abc'] ?? 'B',
            ':activo' => !empty($data['activo']) ? 1 : 0,
        ]);
    }

    /** @param array<string,mixed> $row */
    private function mapRow(array $row): array
    {
        $badge = null;
        if (!empty($row['badge_text'])) {
            $badge = ['text' => (string) $row['badge_text'], 'tone' => (string) ($row['badge_tone'] ?? '')];
        }
        return [
            'id' => (int) $row['id'],
            'nombre' => (string) $row['nombre'],
            'marca' => (string) ($row['marca'] ?? ''),
            'categoria' => (string) ($row['categoria'] ?? ''),
            'subcategoria' => (string) ($row['subcategoria'] ?? ''),
            'unidad' => (string) ($row['unidad'] ?? ''),
            'descripcion' => (string) ($row['descripcion'] ?? ''),
            'ean' => (string) ($row['ean'] ?? ''),
            'precio' => (float) $row['precio'],
            'precioAntes' => $row['precio_antes'] !== null ? (float) $row['precio_antes'] : null,
            'costo' => (float) ($row['costo'] ?? 0),
            'stockLabel' => (string) ($row['stock_label'] ?? ''),
            'stockTone' => (string) ($row['stock_tone'] ?? 'ok'),
            'badge' => $badge,
            'img' => (string) ($row['img'] ?? ''),
            'imgNombre' => (string) ($row['img_nombre'] ?? ''),
            'favorito' => !empty($row['favorito']),
            'puntoReorden' => (int) ($row['punto_reorden'] ?? 10),
            'capacidadGondola' => (int) ($row['capacidad_gondola'] ?? 50),
            'abc' => (string) ($row['abc'] ?? 'B'),
            'activo' => !empty($row['activo']),
            'sku' => 'SKU-' . str_pad((string) $row['id'], 4, '0', STR_PAD_LEFT),
            'skuInterno' => 'MASS-' . (int) $row['id'],
            'stockPiso' => 0,
            'stockAlmacen' => 0,
            'margenPct' => 0.0,
            'margenBruto' => 0.0,
            'esNuevo' => false,
        ];
    }
}
