<?php
declare(strict_types=1);

final class CuponRepository
{
    public function descuento(string $codigo): float
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return -1.0; // señal: usar fallback
        }
        $stmt = $pdo->prepare('SELECT descuento_fijo FROM cupones WHERE codigo = ? AND activo = 1 LIMIT 1');
        $stmt->execute([strtoupper(trim($codigo))]);
        $row = $stmt->fetch();
        return $row ? (float) $row['descuento_fijo'] : 0.0;
    }
}
