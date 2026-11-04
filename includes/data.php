<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/bootstrap.php';
require_once dirname(__DIR__) . '/backend/repositories/InventarioRepository.php';
require_once dirname(__DIR__) . '/backend/validators/ProductoValidator.php';
require_once dirname(__DIR__) . '/backend/services/ProductoService.php';
require_once dirname(__DIR__) . '/backend/services/InventarioService.php';

/**
 * Datos demo Tiendas Mass (equivalente al diseño Figma).
 */
function mass_categorias(): array
{
    if (Database::shouldUseDb()) {
        if (!Database::connected() || !class_exists('CategoriaRepository')) {
            return [];
        }
        return (new CategoriaRepository())->all();
    }

    return [
        [
            'id' => 'frutas',
            'nombre' => 'Frutas y Verduras',
            'badge' => 'Diario',
            'badgeTone' => '',
            'count' => 85,
            'cat' => 'Frutas',
            'img' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=400&h=400&fit=crop',
        ],
        [
            'id' => 'abarrotes',
            'nombre' => 'Abarrotes',
            'badge' => 'Básicos',
            'badgeTone' => 'o',
            'count' => 240,
            'cat' => 'Abarrotes',
            'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop',
        ],
        [
            'id' => 'lacteos',
            'nombre' => 'Lácteos y Huevos',
            'badge' => 'Fresco',
            'badgeTone' => '',
            'count' => 64,
            'cat' => 'Lácteos',
            'img' => 'https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=400&h=400&fit=crop',
        ],
        [
            'id' => 'bebidas',
            'nombre' => 'Bebidas y Jugos',
            'badge' => 'Refresco',
            'badgeTone' => 'o',
            'count' => 70,
            'cat' => 'Bebidas',
            'img' => 'https://images.unsplash.com/photo-1625772299848-391b6a87d7b3?w=400&h=400&fit=crop',
        ],
        [
            'id' => 'snacks',
            'nombre' => 'Snacks y Piques',
            'badge' => 'Antojos',
            'badgeTone' => '',
            'count' => 110,
            'cat' => 'Abarrotes',
            'img' => 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=400&h=400&fit=crop',
        ],
        [
            'id' => 'limpieza',
            'nombre' => 'Limpieza Hogar',
            'badge' => 'Hogar',
            'badgeTone' => '',
            'count' => 95,
            'cat' => 'Limpieza',
            'img' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=400&h=400&fit=crop',
        ],
        [
            'id' => 'cuidado',
            'nombre' => 'Cuidado Personal',
            'badge' => 'Cuidado',
            'badgeTone' => 'o',
            'count' => 78,
            'cat' => 'Limpieza',
            'img' => 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop',
        ],
    ];
}

function mass_productos(): array
{
    if (Database::shouldUseDb()) {
        if (!Database::connected() || !class_exists('ProductoRepository')) {
            return [];
        }
        return (new ProductoRepository())->all();
    }

    return [
        [
            'id' => 1,
            'nombre' => 'Aceite Vegetal Clásico Primor 900 ml',
            'marca' => 'PRIMOR',
            'categoria' => 'Abarrotes',
            'unidad' => 'Botella 900 ml',
            'precio' => 8.9,
            'precioAntes' => 11.5,
            'stockLabel' => '42 unidades disponibles en Magdalena',
            'stockTone' => 'ok',
            'badge' => ['text' => '-22% OFERTA', 'tone' => 'oferta'],
            'img' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=800&h=800&fit=crop',
            'favorito' => true,
        ],
        [
            'id' => 2,
            'nombre' => 'Arroz Extra Grano Largo',
            'marca' => 'COSTEÑO',
            'categoria' => 'Abarrotes',
            'unidad' => 'Bolsa 5 kg',
            'precio' => 22.9,
            'precioAntes' => 26.5,
            'stockLabel' => '41 unidades disponibles en Magdalena',
            'stockTone' => 'ok',
            'badge' => ['text' => 'MÁS VENDIDO', 'tone' => 'verde'],
            'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500&h=500&fit=crop',
            'favorito' => true,
        ],
        [
            'id' => 3,
            'nombre' => 'Leche Evaporada Entera Azul Pack',
            'marca' => 'GLORIA',
            'categoria' => 'Lácteos',
            'unidad' => 'Pack x 6 latas (400g c/u)',
            'precio' => 19.8,
            'precioAntes' => 23.5,
            'stockLabel' => '42 packs disponibles',
            'stockTone' => 'ok',
            'badge' => ['text' => 'AHORRO PACK x6', 'tone' => 'lima'],
            'img' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=500&h=500&fit=crop',
            'favorito' => false,
        ],
        [
            'id' => 4,
            'nombre' => 'Palta Hass Seleccionada',
            'marca' => 'CAMPO MASS',
            'categoria' => 'Frutas',
            'unidad' => 'Malla 1 kg',
            'precio' => 8.5,
            'precioAntes' => 11.2,
            'stockLabel' => '¡Bajo stock! Solo 6 mallas',
            'stockTone' => 'bajo',
            'badge' => ['text' => 'COSECHA HOY', 'tone' => 'verde'],
            'img' => 'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=500&h=500&fit=crop',
            'favorito' => true,
        ],
        [
            'id' => 5,
            'nombre' => 'Detergente en Polvo Floral',
            'marca' => 'BOLÍVAR',
            'categoria' => 'Limpieza',
            'unidad' => 'Bolsa 1 kg',
            'precio' => 11.9,
            'precioAntes' => 14.5,
            'stockLabel' => '22 unidades disponibles en Magdalena',
            'stockTone' => 'ok',
            'badge' => ['text' => '-18% OFERTA', 'tone' => 'oferta'],
            'img' => 'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?w=500&h=500&fit=crop',
            'favorito' => false,
        ],
        [
            'id' => 6,
            'nombre' => 'Gaseosa Cola Familiar',
            'marca' => 'INKA KOLA',
            'categoria' => 'Bebidas',
            'unidad' => 'Botella 2.25 L',
            'precio' => 7.5,
            'precioAntes' => 8.9,
            'stockLabel' => '35 unidades disponibles en Magdalena',
            'stockTone' => 'ok',
            'badge' => ['text' => 'MÁS VENDIDO', 'tone' => 'verde'],
            'img' => 'https://images.unsplash.com/photo-1625772299848-391b6a87d7b3?w=500&h=500&fit=crop',
            'favorito' => true,
        ],
        [
            'id' => 7,
            'nombre' => 'Huevos Blancos Frescos',
            'marca' => 'LA CALERA',
            'categoria' => 'Lácteos',
            'unidad' => 'Pack x12',
            'precio' => 9.9,
            'precioAntes' => 11.5,
            'stockLabel' => '14 packs disponibles en Magdalena',
            'stockTone' => 'ok',
            'badge' => ['text' => '-14% OFERTA', 'tone' => 'oferta'],
            'img' => 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=500&h=500&fit=crop',
            'favorito' => false,
        ],
        [
            'id' => 8,
            'nombre' => 'Plátano de Seda',
            'marca' => 'CAMPO MASS',
            'categoria' => 'Frutas',
            'unidad' => 'Kg',
            'precio' => 3.5,
            'precioAntes' => 4.2,
            'stockLabel' => '18 kg disponibles en Magdalena',
            'stockTone' => 'ok',
            'badge' => ['text' => 'COSECHA HOY', 'tone' => 'verde'],
            'img' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=500&h=500&fit=crop',
            'favorito' => true,
        ],
        [
            'id' => 9,
            'nombre' => 'Arroz Extra Añejo Selección Costeño',
            'marca' => 'COSTEÑO',
            'categoria' => 'Abarrotes',
            'unidad' => 'Bolsa 5 kg',
            'precio' => 23.5,
            'precioAntes' => 27.9,
            'stockLabel' => '33 unidades disponibles',
            'stockTone' => 'ok',
            'badge' => ['text' => 'MÁS AHORRO', 'tone' => 'verde'],
            'img' => 'https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?w=500&h=500&fit=crop',
            'favorito' => false,
        ],
        [
            'id' => 10,
            'nombre' => 'Avena en Hojuelas Enteras Mass',
            'marca' => 'MASS',
            'categoria' => 'Abarrotes',
            'unidad' => 'Bolsa 500 g',
            'precio' => 4.9,
            'precioAntes' => null,
            'stockLabel' => '50 unidades disponibles',
            'stockTone' => 'ok',
            'badge' => ['text' => 'NUEVO', 'tone' => 'lima'],
            'img' => 'https://images.unsplash.com/photo-1517673132405-a56a62b18caf?w=500&h=500&fit=crop',
            'favorito' => false,
        ],
        [
            'id' => 11,
            'nombre' => 'Café Gourmet Tostado Molido',
            'marca' => 'ALTOMAYO',
            'categoria' => 'Abarrotes',
            'unidad' => 'Frasco 250 g',
            'precio' => 14.8,
            'precioAntes' => 17.5,
            'stockLabel' => '19 unidades disponibles',
            'stockTone' => 'ok',
            'badge' => ['text' => 'AHORRO PACK', 'tone' => 'oferta'],
            'img' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=500&h=500&fit=crop',
            'favorito' => true,
        ],
        [
            'id' => 12,
            'nombre' => 'Fideos Spaghetti Clásicos Don Vittorio',
            'marca' => 'DON VITTORIO',
            'categoria' => 'Abarrotes',
            'unidad' => 'Paquete 500 g',
            'precio' => 4.5,
            'precioAntes' => 5.2,
            'stockLabel' => '60 unidades disponibles',
            'stockTone' => 'ok',
            'badge' => null,
            'img' => 'https://images.unsplash.com/photo-1551462147-3787042458dae?w=500&h=500&fit=crop',
            'favorito' => false,
        ],
        [
            'id' => 13,
            'nombre' => 'Lentejas Bebé Seleccionadas',
            'marca' => 'COSTENITO',
            'categoria' => 'Abarrotes',
            'unidad' => 'Bolsa 500 g',
            'precio' => 4.3,
            'precioAntes' => 4.9,
            'stockLabel' => '27 unidades disponibles',
            'stockTone' => 'ok',
            'badge' => null,
            'img' => 'https://images.unsplash.com/photo-1596797038530-2c107229654b?w=500&h=500&fit=crop',
            'favorito' => false,
        ],
        [
            'id' => 14,
            'nombre' => 'Leche Evaporada Entera Gloria Pack 6',
            'marca' => 'GLORIA',
            'categoria' => 'Lácteos',
            'unidad' => 'Pack x6 latas',
            'precio' => 24.2,
            'precioAntes' => 27.0,
            'stockLabel' => '38 packs disponibles',
            'stockTone' => 'ok',
            'badge' => ['text' => 'AHORRO PACK', 'tone' => 'lima'],
            'img' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=500&h=500&fit=crop',
            'favorito' => true,
        ],
        [
            'id' => 15,
            'nombre' => 'Yogurt Natural Bebible',
            'marca' => 'LAIVE',
            'categoria' => 'Lácteos',
            'unidad' => 'Botella 1 L',
            'precio' => 6.5,
            'precioAntes' => 7.9,
            'stockLabel' => '¡Bajo stock! Solo 8 unidades',
            'stockTone' => 'bajo',
            'badge' => ['text' => '-18% OFERTA', 'tone' => 'oferta'],
            'img' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=500&h=500&fit=crop',
            'favorito' => false,
        ],
    ];
}

function mass_filtros_catalogo(): array
{
    return [
        'categorias' => [
            ['id' => 'todos', 'nombre' => 'Todas las categorías', 'count' => 15],
            ['id' => 'Abarrotes', 'nombre' => 'Abarrotes y Despensa', 'count' => 8],
            ['id' => 'Lácteos', 'nombre' => 'Lácteos y Huevos', 'count' => 4],
            ['id' => 'Frutas', 'nombre' => 'Frutas y Verduras', 'count' => 2],
            ['id' => 'Bebidas', 'nombre' => 'Bebidas y Jugos', 'count' => 1],
            ['id' => 'Limpieza', 'nombre' => 'Limpieza Hogar', 'count' => 1],
        ],
        'marcas' => [
            'PRIMOR', 'COSTEÑO', 'GLORIA', 'MASS', 'CAMPO MASS',
            'BOLÍVAR', 'INKA KOLA', 'LA CALERA', 'ALTOMAYO', 'DON VITTORIO', 'LAIVE', 'COSTENITO',
        ],
    ];
}

function mass_tiendas(): array
{
    if (Database::shouldUseDb()) {
        if (!Database::connected() || !class_exists('TiendaRepository')) {
            return [];
        }
        return (new TiendaRepository())->all();
    }

    return [
        [
            'id' => 'magdalena',
            'nombre' => 'Tienda Magdalena Sucre',
            'direccion' => 'Av. Sucre 420, Magdalena del Mar',
            'stock' => 42,
            'distancia' => '0.0 km',
            'estado' => 'actual',
            'estadoLabel' => 'Tu tienda',
            'misma' => true,
            'lat' => -12.0912,
            'lng' => -77.0678,
        ],
        [
            'id' => 'pueblo_libre',
            'nombre' => 'Tienda Pueblo Libre',
            'direccion' => 'Av. Bolívar 598, Pueblo Libre',
            'stock' => 18,
            'distancia' => '2.8 km',
            'estado' => 'ok',
            'estadoLabel' => 'Disponible',
            'misma' => false,
            'lat' => -12.0775,
            'lng' => -77.0621,
        ],
        [
            'id' => 'san_miguel',
            'nombre' => 'Tienda San Miguel',
            'direccion' => 'Av. La Marina 2055, San Miguel',
            'stock' => 4,
            'distancia' => '3.5 km',
            'estado' => 'bajo',
            'estadoLabel' => '¡Últimas 4 unidades!',
            'misma' => false,
            'lat' => -12.0770,
            'lng' => -77.0860,
        ],
        [
            'id' => 'brena',
            'nombre' => 'Tienda Breña',
            'direccion' => 'Jr. Huáscar 1120, Breña',
            'stock' => 0,
            'distancia' => '4.2 km',
            'estado' => 'agotado',
            'estadoLabel' => 'Agotado',
            'misma' => false,
            'lat' => -12.0590,
            'lng' => -77.0500,
        ],
    ];
}

function mass_tienda_por_id(string $id): ?array
{
    return (new InventarioService())->obtenerTiendaPorId($id);
}

/** Horarios según modalidad. */
function mass_horarios_entrega(string $modo): array
{
    if ($modo === 'express') {
        return [
            'hoy_express' => 'Hoy · Express 45–60 min',
            'hoy_tarde' => 'Hoy · 16:00–18:00',
            'hoy_noche' => 'Hoy · 18:00–20:00',
            'manana_manana' => 'Mañana · 09:00–13:00',
        ];
    }
    return [
        'hoy_tarde' => 'Hoy · 16:00–20:00 (retiro en tienda)',
        'manana_manana' => 'Mañana · 09:00–13:00',
        'manana_tarde' => 'Mañana · 16:00–20:00',
        'pasado_manana' => 'Pasado mañana · 09:00–13:00',
    ];
}

/**
 * Detalle enriquecido por producto (Figma 20-751).
 * Se fusiona sobre el catálogo base.
 */
function mass_producto_detalle(int $id): ?array
{
    $base = null;
    foreach (mass_productos() as $p) {
        if ((int) $p['id'] === $id) {
            $base = $p;
            break;
        }
    }
    if ($base === null) {
        return null;
    }

    $detalles = [
        1 => [
            'sku' => 'ACE-PRI-900',
            'rating' => 4.8,
            'reviews' => 342,
            'tiendaStock' => 'Magdalena',
            'stockCantidad' => 42,
            'stockMax' => 50,
            'descripcion' => 'Aceite vegetal clásico Primor de 900 ml, ideal para freír, guisar y aderezar. Elaborado con aceites de alta calidad, ofrece un sabor neutro, excelente rendimiento y estabilidad a altas temperaturas para tu cocina diaria.',
            'beneficios' => [
                'Punto de humo alto',
                'Cero grasas trans',
                'Sin colesterol',
                'Botella con tapa hermética',
            ],
            'confianza' => [
                ['icono' => '✓', 'titulo' => '100% Original', 'texto' => 'Producto auténtico'],
                ['icono' => '↺', 'titulo' => 'Garantía Mass', 'texto' => 'Devolución fácil'],
                ['icono' => '♻', 'titulo' => 'Empaque Seguro', 'texto' => 'Reciclable'],
            ],
            'presentaciones' => [
                ['id' => '900ml', 'label' => '900 ml', 'precio' => 8.9, 'precioAntes' => 11.5, 'activa' => true],
                ['id' => '1l', 'label' => '1 Litro', 'precio' => 10.4, 'precioAntes' => 13.9, 'activa' => false],
                ['id' => 'pack3', 'label' => 'Pack 3×900 ml', 'precio' => 24.9, 'precioAntes' => 34.5, 'activa' => false],
            ],
            'galeria' => [
                'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=800&h=800&fit=crop&q=60',
                'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1505576399279-565b52d5ac87?w=800&h=800&fit=crop',
            ],
            'promoPlus' => [
                'badge' => '-45% Dcto',
                'titulo' => 'Aprovecha+',
                'texto' => 'Lleva 2 y obtén descuento extra en tu segunda unidad.',
            ],
            'combo' => [
                'badge' => 'COMBO FAMILIAR',
                'titulo' => 'Arma tu Alacena: Comprados Frecuentemente Juntos',
                'precioCombo' => 35.8,
                'precioAntes' => 42.3,
                'items' => [
                    ['nombre' => 'Aceite Primor 900 ml', 'precio' => 8.9, 'img' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=200&h=200&fit=crop', 'checked' => true],
                    ['nombre' => 'Arroz Extra Añejo 5 kg', 'precio' => 18.9, 'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=200&h=200&fit=crop', 'checked' => true],
                    ['nombre' => 'Fideos Spaghetti 1 kg', 'precio' => 8.0, 'img' => 'https://images.unsplash.com/photo-1551892374-ecf8754cf8b0?w=200&h=200&fit=crop', 'checked' => true],
                ],
            ],
            'relacionados' => [2, 3, 8, 11],
        ],
    ];

    $extra = $detalles[$id] ?? [
        'sku' => 'SKU-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
        'rating' => 4.5,
        'reviews' => 48,
        'tiendaStock' => 'Magdalena',
        'stockCantidad' => $base['stockTone'] === 'bajo' ? 8 : 24,
        'stockMax' => 50,
        'descripcion' => $base['nombre'] . '. Producto de calidad disponible en tiendas Mass cerca de ti.',
        'beneficios' => [
            'Calidad garantizada Mass',
            'Disponible en tiendas cercanas',
            'Precio bajo siempre',
        ],
        'confianza' => [
            ['icono' => '✓', 'titulo' => '100% Original', 'texto' => 'Producto auténtico'],
            ['icono' => '↺', 'titulo' => 'Garantía Mass', 'texto' => 'Devolución fácil'],
            ['icono' => '♻', 'titulo' => 'Empaque Seguro', 'texto' => 'Reciclable'],
        ],
        'presentaciones' => [
            [
                'id' => 'default',
                'label' => $base['unidad'],
                'precio' => $base['precio'],
                'precioAntes' => $base['precioAntes'] ?? null,
                'activa' => true,
            ],
        ],
        'galeria' => [$base['img'], $base['img'], $base['img']],
        'promoPlus' => null,
        'combo' => null,
        'relacionados' => array_values(array_filter(
            array_map(static fn ($p) => (int) $p['id'], mass_productos()),
            static fn ($pid) => $pid !== $id
        )),
    ];

    if (isset($extra['relacionados'])) {
        $extra['relacionados'] = array_slice($extra['relacionados'], 0, 4);
    }

    return array_merge($base, $extra);
}


/** Dashboard admin — delega en InventarioService (con fallback demo). */
function mass_admin_kpis(): array
{
    return (new InventarioService())->obtenerKpis();
}

function mass_admin_venta_horaria(): array
{
    return [
        ['hora' => '08:00', 'venta' => 18200, 'objetivo' => 20000],
        ['hora' => '10:00', 'venta' => 32400, 'objetivo' => 30000],
        ['hora' => '12:00', 'venta' => 48500, 'objetivo' => 42000],
        ['hora' => '14:00', 'venta' => 39800, 'objetivo' => 41000],
        ['hora' => '16:00', 'venta' => 45200, 'objetivo' => 40000],
        ['hora' => '18:00', 'venta' => 61200, 'objetivo' => 55000],
        ['hora' => '20:00', 'venta' => 54800, 'objetivo' => 50000],
        ['hora' => '22:00', 'venta' => 28600, 'objetivo' => 25000],
    ];
}

function mass_admin_canales(): array
{
    return (new InventarioService())->obtenerClasificacionAbc();
}

function mass_admin_abc_total(): int
{
    return (new InventarioService())->obtenerTotalAbc();
}

function mass_admin_stock_global(): array
{
    return (new InventarioService())->obtenerStockGlobal();
}

function mass_admin_top_rotacion(): array
{
    return (new ProductoService())->obtenerTopRotacion();
}

/**
 * @return array<int, array<string, mixed>>
 */
function mass_admin_alertas_gondola(): array
{
    return (new InventarioService())->obtenerAlertasGondola();
}

function mass_admin_gondola(): array
{
    return (new InventarioService())->listarGondola();
}

function mass_admin_picking_cola(): array
{
    return [
        [
            'codigo' => 'MASS-04573',
            'cliente' => 'Mariana Quispe',
            'modo' => 'express',
            'items' => 6,
            'mins' => 4,
            'estado' => 'preparacion',
            'estadoLabel' => 'En preparación',
        ],
        [
            'codigo' => 'MASS-04574',
            'cliente' => 'Luis Ramos',
            'modo' => 'retiro',
            'items' => 3,
            'mins' => 9,
            'estado' => 'por_recoger',
            'estadoLabel' => 'Por recoger',
        ],
        [
            'codigo' => 'MASS-04575',
            'cliente' => 'Ana Torres',
            'modo' => 'express',
            'items' => 11,
            'mins' => 12,
            'estado' => 'preparacion',
            'estadoLabel' => 'En preparación',
        ],
        [
            'codigo' => 'MASS-04576',
            'cliente' => 'Carlos Díaz',
            'modo' => 'retiro',
            'items' => 2,
            'mins' => 2,
            'estado' => 'por_recoger',
            'estadoLabel' => 'Por recoger',
        ],
        [
            'codigo' => 'MASS-04577',
            'cliente' => 'Elena Vargas',
            'modo' => 'express',
            'items' => 8,
            'mins' => 15,
            'estado' => 'preparacion',
            'estadoLabel' => 'Picking',
        ],
    ];
}

function mass_admin_pedidos(): array
{
    $cola = mass_admin_picking_cola();
    $extra = [
        [
            'codigo' => 'MASS-04560',
            'cliente' => 'Pedro Salas',
            'modo' => 'express',
            'items' => 5,
            'mins' => 0,
            'estado' => 'entregado',
            'estadoLabel' => 'Entregado',
            'total' => 86.40,
        ],
        [
            'codigo' => 'MASS-04558',
            'cliente' => 'Rosa Méndez',
            'modo' => 'retiro',
            'items' => 4,
            'mins' => 0,
            'estado' => 'retirado',
            'estadoLabel' => 'Retirado',
            'total' => 52.10,
        ],
    ];
    $out = [];
    foreach ($cola as $p) {
        $out[] = $p + ['total' => 45.90 + (int) $p['items'] * 3.5];
    }
    return array_merge($out, $extra);
}

function mass_admin_usuario(): array
{
    $auth = function_exists('auth_usuario') ? auth_usuario() : null;
    return [
        'nombre' => (string) ($auth['nombre'] ?? 'Ing. Roberto Silva'),
        'rol' => (string) ($auth['cargo'] ?? 'Jefe de Operaciones'),
        'sucursal' => 'Sede Operativa #143 Miraflores | Municipal',
        'sedeCorta' => 'Sede Central (Lima Sur)',
        'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop',
        'panelVersion' => 'web panel v1.2',
    ];
}

/**
 * Catálogo admin (Figma 35-9928) — delega en ProductoService.
 *
 * @return array<int, array<string, mixed>>
 */
function mass_admin_productos(): array
{
    return (new ProductoService())->listarAdmin();
}

/**
 * @param array<string, mixed> $row
 * @param array<string, mixed> $campos
 * @return array<string, mixed>
 */
function mass_admin_producto_aplicar_campos(array $row, array $campos): array
{
    return (new ProductoService())->aplicarCamposAdmin($row, $campos);
}

/**
 * @param array<string, mixed> $fila
 * @return array<string, mixed>
 */
function mass_admin_producto_normalizar_fila(array $fila): array
{
    return (new ProductoService())->normalizarFilaAdmin($fila);
}

function mass_admin_producto_por_id(int $id): ?array
{
    return (new ProductoService())->obtenerAdminPorId($id);
}

function mass_admin_producto_siguiente_id(): int
{
    return (new ProductoService())->siguienteIdAdmin();
}

function mass_admin_producto_plantilla_nueva(): array
{
    return (new ProductoService())->plantillaNuevaAdmin();
}

/**
 * Reglas de negocio catálogo admin (alta / edición) — delega en ProductoService / ProductoValidator.
 *
 * @param array<string, mixed> $data
 * @return array{ok:bool,errores:array<int,string>,data:?array<string,mixed>}
 */
function producto_admin_validar(array $data, int $idExcluir = 0): array
{
    return (new ProductoService())->validar($data, $idExcluir);
}

/**
 * @param array<string, mixed> $data
 * @return array{ok:bool,errores:array<int,string>,id:int}
 */
function mass_admin_producto_guardar(int $id, array $data): array
{
    return (new ProductoService())->guardarAdmin($id, $data);
}

/**
 * @param array<string, mixed> $data
 * @return array{ok:bool,errores:array<int,string>,id:int}
 */
function mass_admin_producto_crear(array $data): array
{
    return (new ProductoService())->crearAdmin($data);
}
