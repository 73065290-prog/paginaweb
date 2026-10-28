<?php
declare(strict_types=1);

namespace Tests\Support;

use PDO;

/**
 * Almacén SQLite en memoria SOLO para laboratorio de pruebas (no altera la app ni BD de desarrollo).
 * Sirve para demostrar integración guardar/leer aislada según la guía de práctica.
 */
final class SqliteLabStore
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec(
            'CREATE TABLE lab_carrito (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                producto_id INTEGER NOT NULL,
                cantidad INTEGER NOT NULL,
                created_at TEXT NOT NULL
            )'
        );
    }

    public function guardarLinea(int $productoId, int $cantidad): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO lab_carrito (producto_id, cantidad, created_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$productoId, $cantidad, date('c')]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return array{producto_id:int,cantidad:int}|null */
    public function leerLinea(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT producto_id, cantidad FROM lab_carrito WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return [
            'producto_id' => (int) $row['producto_id'],
            'cantidad' => (int) $row['cantidad'],
        ];
    }

    public function totalUnidades(): int
    {
        return (int) $this->pdo->query('SELECT COALESCE(SUM(cantidad),0) FROM lab_carrito')->fetchColumn();
    }
}
