<?php
declare(strict_types=1);

/**
 * Validador de productos admin — delega a la regla de negocio existente.
 */
final class ProductoValidator
{
    /**
     * @param array<string,mixed> $data
     * @return array{ok:bool,errores:array<int,string>,data:?array<string,mixed>}
     */
    public function validate(array $data, int $idExcluir = 0): array
    {
        return producto_admin_validar($data, $idExcluir);
    }
}
