<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/validators/PedidoValidator.php';
require_once dirname(__DIR__) . '/repositories/PedidoRepository.php';
require_once __DIR__ . '/CarritoService.php';
require_once __DIR__ . '/CuponService.php';

/**
 * Servicio de negocio para la gestión de pedidos, checkout, validación de entrega,
 * cálculo de totales, tracking y confirmación (MySQL con transacciones PDO y fallback en sesión).
 */
final class PedidoService
{
    public const COSTO_ENVIO_RETIRO = 0.0;
    public const COSTO_ENVIO_EXPRESS = 4.9;
    public const AHORRO_APROVECHA_MONTO = 6.95;
    public const AHORRO_APROVECHA_MIN_UNIDADES = 3;
    public const PREFIJO_CODIGO = 'MASS-';

    private PedidoValidator $validator;
    private PedidoRepository $repository;
    private CarritoService $carritoService;
    private CuponService $cuponService;
    private ?PDO $pdo;

    public function __construct(
        ?PedidoValidator $validator = null,
        ?PedidoRepository $repository = null,
        ?CarritoService $carritoService = null,
        ?CuponService $cuponService = null,
        ?PDO $pdo = null,
    ) {
        $this->validator = $validator ?? new PedidoValidator();
        $this->repository = $repository ?? new PedidoRepository();
        $this->carritoService = $carritoService ?? new CarritoService();
        $this->cuponService = $cuponService ?? new CuponService();
        $this->pdo = $pdo;
    }

    /**
     * Devuelve los valores por defecto del estado del pedido en curso.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'cupon' => '',
            'entrega' => PedidoValidator::ENTREGA_RETIRO,
            'pago' => 'yape',
            'comprobante' => 'boleta',
            'nombre' => '',
            'apellido' => '',
            'documento' => '',
            'telefono' => '',
            'direccion' => 'Av. Brasil 2340, Dpto 802, Magdalena del Mar',
            'referencia' => '',
            'tienda_id' => 'magdalena',
            'horario' => 'hoy_tarde',
        ];
    }

    /**
     * Obtiene el estado actual del pedido en sesión fusionado con los valores por defecto.
     *
     * @return array<string, mixed>
     */
    public function estado(): array
    {
        if (!isset($_SESSION['pedido']) || !is_array($_SESSION['pedido'])) {
            $_SESSION['pedido'] = $this->defaults();
        }
        return array_merge($this->defaults(), $_SESSION['pedido']);
    }

    /**
     * Actualiza parcialmente el estado del pedido en sesión.
     *
     * @param array<string, mixed> $data
     */
    public function set(array $data): void
    {
        $_SESSION['pedido'] = array_merge($this->estado(), $data);
    }

    /**
     * Normaliza la modalidad de entrega ('retiro' | 'express').
     */
    public function normalizarEntrega(string $modo): string
    {
        return $this->validator->normalizarEntrega($modo);
    }

    /**
     * Devuelve el costo de envío según la modalidad: 0.0 para retiro y 4.90 para express.
     */
    public function costoEnvio(string $modo): float
    {
        return $this->normalizarEntrega($modo) === PedidoValidator::ENTREGA_EXPRESS
            ? self::COSTO_ENVIO_EXPRESS
            : self::COSTO_ENVIO_RETIRO;
    }

    /**
     * Devuelve el mapa de horarios disponibles según la modalidad de entrega.
     *
     * @return array<string, string>
     */
    public function horariosEntrega(string $modo): array
    {
        return $this->validator->horariosPermitidos($modo);
    }

    /**
     * Valida los datos de entrega delegando en PedidoValidator.
     *
     * @param array<string, mixed> $data
     * @return array{ok:bool, errores:array<int,string>, data:array<string,mixed>}
     */
    public function validarEntrega(array $data): array
    {
        return $this->validator->validateEntrega($data);
    }

    /**
     * Calcula el resumen financiero y las líneas detalladas del pedido.
     *
     * @param array<int, array{id:int|string, cantidad:int|string}>|null $items
     * @param array<string, mixed>|null $pedidoOverride
     * @return array{
     *   items:int,
     *   subtotal:float,
     *   ahorroOfertas:float,
     *   ahorroAprovecha:float,
     *   descuentoCupon:float,
     *   envio:float,
     *   ahorroTotal:float,
     *   total:float,
     *   cupon:string,
     *   entrega:string,
     *   lineas:array<int, array<string, mixed>>
     * }
     */
    public function resumen(?array $items = null, ?array $pedidoOverride = null): array
    {
        $pedido = $pedidoOverride ?? $this->estado();
        $lista = $items ?? $this->carritoService->items();
        $subtotal = 0.0;
        $ahorroOfertas = 0.0;
        $unidades = 0;
        $lineas = [];

        foreach ($lista as $item) {
            $p = function_exists('producto_por_id') ? producto_por_id((int) $item['id']) : null;
            if (!$p) {
                continue;
            }
            $qty = (int) $item['cantidad'];
            $unidades += $qty;
            $precio = (float) $p['precio'];
            $subtotal += $precio * $qty;
            if (!empty($p['precioAntes']) && (float) $p['precioAntes'] > $precio) {
                $ahorroOfertas += ((float) $p['precioAntes'] - $precio) * $qty;
            }
            $lineas[] = [
                'id' => (int) $p['id'],
                'nombre' => $p['nombre'],
                'img' => $p['img'],
                'precio' => $precio,
                'cantidad' => $qty,
                'subtotal' => $precio * $qty,
            ];
        }

        $ahorroAprovecha = $unidades >= self::AHORRO_APROVECHA_MIN_UNIDADES
            ? self::AHORRO_APROVECHA_MONTO
            : 0.0;
        $cupon = $this->cuponService->normalizarCodigo((string) ($pedido['cupon'] ?? ''));
        $descuentoCupon = $this->cuponService->descuento($cupon);
        $entrega = $this->normalizarEntrega((string) ($pedido['entrega'] ?? PedidoValidator::ENTREGA_RETIRO));
        $envio = $this->costoEnvio($entrega);

        $ahorroTotal = $ahorroOfertas + $ahorroAprovecha + $descuentoCupon;
        $total = max(0.0, $subtotal - $ahorroAprovecha - $descuentoCupon + $envio);

        return [
            'items' => $unidades,
            'subtotal' => $subtotal,
            'ahorroOfertas' => $ahorroOfertas,
            'ahorroAprovecha' => $ahorroAprovecha,
            'descuentoCupon' => $descuentoCupon,
            'envio' => $envio,
            'ahorroTotal' => $ahorroTotal,
            'total' => $total,
            'cupon' => $cupon,
            'entrega' => $entrega,
            'lineas' => $lineas,
        ];
    }

    /**
     * Construye los pasos de seguimiento (tracking) según la modalidad y el estado actual.
     *
     * @return array<int, array{key:string, titulo:string, detalle:string, estado:string}>
     */
    public function trackingPasos(string $entrega, string $estadoActual = 'gondola'): array
    {
        $entrega = $this->normalizarEntrega($entrega);
        $orden = $entrega === PedidoValidator::ENTREGA_EXPRESS
            ? ['confirmado', 'gondola', 'camino', 'entregado']
            : ['confirmado', 'gondola', 'listo', 'retirado'];

        $labels = $entrega === PedidoValidator::ENTREGA_EXPRESS
            ? [
                'confirmado' => ['Pedido confirmado & pagado', 'Pago verificado correctamente'],
                'gondola' => ['En góndola: separación y empaque', 'Personal shopper en tu tienda Mass'],
                'camino' => ['En camino: Motorizado Express', 'Tu pedido va hacia la dirección indicada'],
                'entregado' => ['Entregado en destino', 'Pedido recibido'],
            ]
            : [
                'confirmado' => ['Pedido confirmado & pagado', 'Pago verificado correctamente'],
                'gondola' => ['En góndola: separación y empaque', 'Preparando tu retiro en tienda'],
                'listo' => ['Listo para retiro', 'Presenta tu QR en caja de retiro'],
                'retirado' => ['Retirado en tienda', 'Pedido entregado en mostrador'],
            ];

        $idxActual = array_search($estadoActual, $orden, true);
        if ($idxActual === false) {
            $idxActual = 1;
        }

        $out = [];
        foreach ($orden as $i => $key) {
            $estado = $i < $idxActual ? 'done' : ($i === $idxActual ? 'current' : 'pending');
            $out[] = [
                'key' => $key,
                'titulo' => $labels[$key][0],
                'detalle' => $labels[$key][1],
                'estado' => $estado,
            ];
        }
        return $out;
    }

    /**
     * Genera un código único de pedido conservando el prefijo MASS-.
     */
    public function generarCodigo(): string
    {
        return self::PREFIJO_CODIGO . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    /**
     * Confirma el pedido actual: calcula totales, persiste en MySQL mediante PedidoRepository
     * y transacciones PDO cuando existe conexión, guarda el snapshot en sesión y vacía el carrito.
     *
     * @return array<string, mixed>|null
     */
    public function confirmar(string $pago = 'yape'): ?array
    {
        $items = $this->carritoService->items();
        if (!$items) {
            return null;
        }

        $pedido = $this->estado();
        $pago = $this->validator->normalizarPago($pago);
        $resumen = $this->resumen($items, $pedido);
        $tienda = function_exists('mass_tienda_por_id')
            ? mass_tienda_por_id((string) ($pedido['tienda_id'] ?? 'magdalena'))
            : null;
        $codigo = $this->generarCodigo();
        $boleta = 'B004-' . random_int(100, 999);

        $orden = [
            'codigo' => $codigo,
            'boleta' => $boleta,
            'creado' => date('c'),
            'estado' => 'gondola',
            'pago' => $pago,
            'pedido' => $pedido,
            'resumen' => $resumen,
            'tienda' => $tienda,
            'rider' => [
                'nombre' => 'Carlos Huamán',
                'rating' => 4.8,
                'vehiculo' => 'Motorizado Express',
                'telefono' => '999888777',
            ],
            'impacto' => [
                'co2' => 1.0,
                'plastico' => 1.4,
                'puntos' => max(10, (int) round($resumen['total'])),
            ],
        ];

        $lineasDb = $this->prepararLineasPersistencia($items);
        $pedidoDb = [
            'codigo' => $codigo,
            'entrega' => (string) ($pedido['entrega'] ?? PedidoValidator::ENTREGA_RETIRO),
            'pago' => $pago,
            'comprobante' => (string) ($pedido['comprobante'] ?? 'boleta'),
            'nombre' => (string) ($pedido['nombre'] ?? ''),
            'apellido' => (string) ($pedido['apellido'] ?? ''),
            'documento' => (string) ($pedido['documento'] ?? ''),
            'telefono' => (string) ($pedido['telefono'] ?? ''),
            'direccion' => (string) ($pedido['direccion'] ?? ''),
            'referencia' => (string) ($pedido['referencia'] ?? ''),
            'tienda_id' => (string) ($pedido['tienda_id'] ?? ''),
            'horario' => (string) ($pedido['horario'] ?? ''),
            'cupon' => (string) ($pedido['cupon'] ?? ''),
            'subtotal' => (float) ($resumen['subtotal'] ?? 0.0),
            'descuento' => (float) ($resumen['descuento'] ?? ((float) ($resumen['ahorroAprovecha'] ?? 0.0) + (float) ($resumen['descuentoCupon'] ?? 0.0))),
            'envio' => (float) ($resumen['envio'] ?? 0.0),
            'total' => (float) ($resumen['total'] ?? 0.0),
            'estado' => 'confirmado',
        ];

        if ($this->pdo !== null) {
            $creadoDb = $this->persistirConTransaccion($this->pdo, $pedidoDb, $lineasDb);
            if ($creadoDb !== null && isset($creadoDb['id'])) {
                $orden['pedido_id'] = (int) $creadoDb['id'];
            }
        } elseif (class_exists('Database') && Database::connected()) {
            $creadoDb = $this->repository->create($pedidoDb, $lineasDb);
            if ($creadoDb !== null && isset($creadoDb['id'])) {
                $orden['pedido_id'] = (int) $creadoDb['id'];
            }
        }

        $_SESSION['ultimo_pedido'] = $orden;
        $this->carritoService->vaciar();
        return $orden;
    }

    /**
     * Devuelve el último pedido confirmado almacenado en sesión o null si no existe.
     *
     * @return array<string, mixed>|null
     */
    public function ultimo(): ?array
    {
        $o = $_SESSION['ultimo_pedido'] ?? null;
        return is_array($o) ? $o : null;
    }

    /**
     * Persiste un pedido y sus detalles utilizando una transacción PDO explícita
     * (commit solo al completar cabecera + detalles, rollBack ante cualquier fallo).
     *
     * @param array<string, mixed> $pedido
     * @param array<int, array<string, mixed>> $lineas
     * @return array<string, mixed>|null
     */
    public function persistirConTransaccion(PDO $pdo, array $pedido, array $lineas): ?array
    {
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
                ':entrega' => $pedido['entrega'] ?? PedidoValidator::ENTREGA_RETIRO,
                ':pago' => $pedido['pago'] ?? 'yape',
                ':comprobante' => $pedido['comprobante'] ?? 'boleta',
                ':nombre' => $pedido['nombre'] ?? '',
                ':apellido' => $pedido['apellido'] ?? '',
                ':documento' => $pedido['documento'] ?? '',
                ':telefono' => $pedido['telefono'] ?? '',
                ':direccion' => $pedido['direccion'] ?? '',
                ':referencia' => $pedido['referencia'] ?? '',
                ':tienda_id' => ($pedido['tienda_id'] ?? '') !== '' ? $pedido['tienda_id'] : null,
                ':horario' => $pedido['horario'] ?? '',
                ':cupon' => $pedido['cupon'] ?? '',
                ':subtotal' => $pedido['subtotal'] ?? 0.0,
                ':descuento' => $pedido['descuento'] ?? 0.0,
                ':envio' => $pedido['envio'] ?? 0.0,
                ':total' => $pedido['total'] ?? 0.0,
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

    /**
     * Prepara las líneas de detalle para persistencia en pedido_detalles.
     *
     * @param array<int, array{id:int|string, cantidad:int|string}> $items
     * @return array<int, array{producto_id:int, nombre:string, precio_unit:float, cantidad:int, subtotal:float}>
     */
    private function prepararLineasPersistencia(array $items): array
    {
        $lineas = [];
        foreach ($items as $item) {
            $p = function_exists('producto_por_id') ? producto_por_id((int) $item['id']) : null;
            if (!$p) {
                continue;
            }
            $cant = (int) $item['cantidad'];
            $precio = (float) $p['precio'];
            $lineas[] = [
                'producto_id' => (int) $p['id'],
                'nombre' => (string) $p['nombre'],
                'precio_unit' => $precio,
                'cantidad' => $cant,
                'subtotal' => $precio * $cant,
            ];
        }
        return $lineas;
    }
}

