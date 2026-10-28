<?php
declare(strict_types=1);

final class PedidoValidator
{
    /**
     * @param array<string,mixed> $data
     * @return array{ok:bool, errores:array<int,string>, data:array<string,mixed>}
     */
    public function validateEntrega(array $data): array
    {
        return pedido_validar_entrega($data);
    }
}
