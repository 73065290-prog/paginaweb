<?php
declare(strict_types=1);

final class PedidoRepository
{
    /**
     * @param array<string,mixed> $pedido
     * @param array<int,array<string,mixed>> $lineas
     */
    public function create(array $pedido, array $lineas): ?array
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return null;
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO pedidos
                (codigo, entrega, pago, comprobante, nombre, apellido, documento, telefono, direccion, referencia, tienda_id, horario, cupon, subtotal, descuento, envio, total, estado)
                VALUES
                (:codigo, :entrega, :pago, :comprobante, :nombre, :apellido, :documento, :telefono, :direccion, :referencia, :tienda_id, :horario, :cupon, :subtotal, :descuento, :envio, :total, :estado)'
            );
            $stmt->execute([
                ':codigo' => $pedido['codigo'],
                ':entrega' => $pedido['entrega'] ?? 'retiro',
                ':pago' => $pedido['pago'] ?? 'yape',
                ':comprobante' => $pedido['comprobante'] ?? 'boleta',
                ':nombre' => $pedido['nombre'] ?? '',
                ':apellido' => $pedido['apellido'] ?? '',
                ':documento' => $pedido['documento'] ?? '',
                ':telefono' => $pedido['telefono'] ?? '',
                ':direccion' => $pedido['direccion'] ?? '',
                ':referencia' => $pedido['referencia'] ?? '',
                ':tienda_id' => $pedido['tienda_id'] ?: null,
                ':horario' => $pedido['horario'] ?? '',
                ':cupon' => $pedido['cupon'] ?? '',
                ':subtotal' => $pedido['subtotal'] ?? 0,
                ':descuento' => $pedido['descuento'] ?? 0,
                ':envio' => $pedido['envio'] ?? 0,
                ':total' => $pedido['total'] ?? 0,
                ':estado' => $pedido['estado'] ?? 'confirmado',
            ]);
            $pedidoId = (int) $pdo->lastInsertId();

            $det = $pdo->prepare(
                'INSERT INTO pedido_detalles (pedido_id, producto_id, nombre, precio_unit, cantidad, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($lineas as $linea) {
                $det->execute([
                    $pedidoId,
                    $linea['producto_id'] ?? null,
                    $linea['nombre'],
                    $linea['precio_unit'],
                    $linea['cantidad'],
                    $linea['subtotal'],
                ]);
            }
            $pdo->commit();
            $pedido['id'] = $pedidoId;
            return $pedido;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return null;
        }
    }

    public function findByCodigo(string $codigo): ?array
    {
        $pdo = Database::pdo();
        if (!$pdo) {
            return null;
        }
        $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE codigo = ? LIMIT 1');
        $stmt->execute([$codigo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
