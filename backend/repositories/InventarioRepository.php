<?php
declare(strict_types=1);

final class InventarioRepository
{
    public function byTienda(string $tiendaId): array
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return [];
        }
        $stmt = $pdo->prepare(
            'SELECT i.producto_id, i.tienda_id, i.stock_piso, i.stock_almacen, p.nombre, p.marca
             FROM inventario i
             INNER JOIN productos p ON p.id = i.producto_id
             WHERE i.tienda_id = ?
             ORDER BY p.nombre'
        );
        $stmt->execute([$tiendaId]);
        return $stmt->fetchAll();
    }

    public function updateStock(int $productoId, string $tiendaId, int $piso, int $almacen): bool
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return false;
        }
        $stmt = $pdo->prepare(
            'UPDATE inventario SET stock_piso = ?, stock_almacen = ? WHERE producto_id = ? AND tienda_id = ?'
        );
        return $stmt->execute([$piso, $almacen, $productoId, $tiendaId]);
    }
}
