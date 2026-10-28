<?php
declare(strict_types=1);

/**
 * Datos demo Tiendas Mass (equivalente al diseño Figma).
 */
function mass_categorias(): array
{
    if (class_exists('CategoriaRepository') && Database::connected()) {
        $rows = (new CategoriaRepository())->all();
        if ($rows !== []) {
            return $rows;
        }
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
    if (class_exists('ProductoRepository') && Database::connected()) {
        $rows = (new ProductoRepository())->all();
        if ($rows !== []) {
            return $rows;
        }
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
    if (class_exists('TiendaRepository') && Database::connected()) {
        $rows = (new TiendaRepository())->all();
        if ($rows !== []) {
            return $rows;
        }
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
    foreach (mass_tiendas() as $t) {
        if (($t['id'] ?? '') === $id) {
            return $t;
        }
    }
    return null;
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

/** Dashboard admin — Figma node 20-8275 (demo sin MySQL). */
function mass_admin_kpis(): array
{
    // Dashboard Figma 30-9174 — KPIs principales + métricas auxiliares
    return [
        'ventaDia' => 482910.50,
        'ventaTrend' => 3.4,
        'pedidosCurso' => 142,
        'pedidosPicking' => 64,
        'pedidosRuta' => 38,
        'pedidosListos' => 40,
        'stockCritico' => 28,
        'quiebresGondola' => 6,
        'sugerenciaOc' => 19,
        'conteosPasillos' => 3,
        'mermaPct' => 0.82,
        'mermaMonto' => 3850.00,
        'inventarioTotal' => 3450000.00,
        // Compatibilidad páginas legacy
        'ventaNeta' => 482910.50,
        'boletas' => 184,
        'ticketPromedio' => 100.27,
        'pickingPct' => 72,
        'tiempoPromedioMin' => 14.2,
        'disponibilidad' => 96.4,
        'disponibilidadEstado' => 'Óptimo',
        'aprovechamiento' => 1420.50,
        'mermaEvitada' => 890.00,
        'mermaProyectada' => 320.00,
    ];
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
    // Reutilizado como Clasificación ABC (Figma 30-9174)
    return [
        ['id' => 'a', 'nombre' => 'Clase A · Alta rotación', 'pct' => 21, 'skus' => 432, 'color' => '#006b45'],
        ['id' => 'b', 'nombre' => 'Clase B · Rotación media', 'pct' => 26, 'skus' => 526, 'color' => '#ff8a3d'],
        ['id' => 'c', 'nombre' => 'Clase C · Baja rotación', 'pct' => 53, 'skus' => 1086, 'color' => '#f5c542'],
    ];
}

function mass_admin_abc_total(): int
{
    return 2044;
}

function mass_admin_stock_global(): array
{
    return [
        ['id' => 'normal', 'label' => 'Normal / Saludable', 'cantidad' => 1824, 'color' => '#006b45'],
        ['id' => 'bajo', 'label' => 'Stock bajo', 'cantidad' => 202, 'color' => '#ff8a3d'],
        ['id' => 'agotado', 'label' => 'Agotado / Quiebre', 'cantidad' => 18, 'color' => '#e53935'],
    ];
}

function mass_admin_top_rotacion(): array
{
    return [
        ['nombre' => 'Aceite Vegetal Primor 1L', 'unidades' => 842, 'monto' => 12630.00, 'abc' => 'A'],
        ['nombre' => 'Arroz Costeño 5kg', 'unidades' => 610, 'monto' => 10980.00, 'abc' => 'A'],
        ['nombre' => 'Leche Gloria Evaporada', 'unidades' => 1284, 'monto' => 6420.00, 'abc' => 'A'],
        ['nombre' => 'Huevos Pardos x12', 'unidades' => 496, 'monto' => 4464.00, 'abc' => 'B'],
        ['nombre' => 'Azúcar Rubia 1kg', 'unidades' => 388, 'monto' => 3104.00, 'abc' => 'B'],
    ];
}

/**
 * @return array<int, array<string, mixed>>
 */
function mass_admin_alertas_gondola(): array
{
    $productos = mass_productos();
    $map = [];
    foreach ($productos as $p) {
        $map[(int) $p['id']] = $p;
    }

    $rows = [
        [
            'id' => 2,
            'ean' => '7750243001829',
            'piso' => 4,
            'almacen' => 0,
            'minimo' => 18,
            'abc' => 'A',
            'ubicacion' => 'Góndola B4 · Pasillo 02',
            'estado' => 'critico',
            'estadoLabel' => 'Quiebre detectado',
            'accion' => 'Generar OC',
            'filtro' => 'agotados',
        ],
        [
            'id' => 1,
            'ean' => '7750885012345',
            'piso' => 8,
            'almacen' => 6,
            'minimo' => 24,
            'abc' => 'A',
            'ubicacion' => 'Góndola A2 · Pasillo 01',
            'estado' => 'bajo',
            'estadoLabel' => 'Stock crítico',
            'accion' => 'Pedir +10',
            'filtro' => 'critico',
        ],
        [
            'id' => 11,
            'ean' => '7751234009876',
            'piso' => 2,
            'almacen' => 12,
            'minimo' => 20,
            'abc' => 'B',
            'ubicacion' => 'Góndola C1 · Pasillo 05',
            'estado' => 'bajo',
            'estadoLabel' => 'Stock crítico',
            'accion' => 'Pedir +15',
            'filtro' => 'critico',
        ],
        [
            'id' => 8,
            'ean' => '7750001122334',
            'piso' => 0,
            'almacen' => 3,
            'minimo' => 16,
            'abc' => 'A',
            'ubicacion' => 'Almacén · Rack 07',
            'estado' => 'critico',
            'estadoLabel' => 'Quiebre detectado',
            'accion' => 'Solicitar lote',
            'filtro' => 'agotados',
        ],
        [
            'id' => 3,
            'ean' => '7754567890123',
            'piso' => 14,
            'almacen' => 22,
            'minimo' => 12,
            'abc' => 'B',
            'ubicacion' => 'Góndola D3 · Pasillo 04',
            'estado' => 'vencer',
            'estadoLabel' => 'Próximo a vencer',
            'accion' => 'Revisar lote',
            'filtro' => 'vencer',
        ],
        [
            'id' => 5,
            'ean' => '7759988776655',
            'piso' => 20,
            'almacen' => 40,
            'minimo' => 15,
            'abc' => 'C',
            'ubicacion' => 'Góndola E1 · Pasillo 06',
            'estado' => 'ok',
            'estadoLabel' => 'OC generada',
            'accion' => 'Ver OC',
            'filtro' => 'todos',
        ],
    ];

    $out = [];
    foreach ($rows as $r) {
        $p = $map[$r['id']] ?? null;
        if (!$p) {
            continue;
        }
        $out[] = array_merge($r, [
            'nombre' => $p['nombre'],
            'sku' => 'SKU-' . str_pad((string) $p['id'], 4, '0', STR_PAD_LEFT),
            'img' => $p['img'],
            'categoria' => $p['categoria'] ?? ($p['marca'] ?? ''),
            'marca' => $p['marca'],
        ]);
    }
    return $out;
}

function mass_admin_gondola(): array
{
    $productos = mass_productos();
    $map = [];
    foreach ($productos as $p) {
        $map[(int) $p['id']] = $p;
    }

    $rows = [
        ['id' => 1, 'gondola' => 18, 'trastienda' => 24, 'estado' => 'ok'],
        ['id' => 2, 'gondola' => 6, 'trastienda' => 12, 'estado' => 'bajo'],
        ['id' => 11, 'gondola' => 0, 'trastienda' => 4, 'estado' => 'critico'],
        ['id' => 3, 'gondola' => 22, 'trastienda' => 30, 'estado' => 'ok'],
        ['id' => 8, 'gondola' => 3, 'trastienda' => 0, 'estado' => 'critico'],
    ];

    $out = [];
    foreach ($rows as $r) {
        $p = $map[$r['id']] ?? null;
        if (!$p) {
            continue;
        }
        $out[] = array_merge($r, [
            'nombre' => $p['nombre'],
            'sku' => 'SKU-' . str_pad((string) $p['id'], 4, '0', STR_PAD_LEFT),
            'img' => $p['img'],
            'marca' => $p['marca'],
        ]);
    }
    return $out;
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
 * Catálogo admin (Figma 35-9928) enriquecido con costos, ABC, WMS,
 * overrides de edición y productos nuevos en sesión.
 *
 * @return array<int, array<string, mixed>>
 */
function mass_admin_productos(): array
{
    $subs = [
        'Abarrotes' => 'Aceites y Grasas',
        'Lácteos' => 'Leches y Derivados',
        'Frutas' => 'Frescos Seleccionados',
        'Bebidas' => 'Gaseosas y Jugos',
        'Limpieza' => 'Hogar y Cuidado',
    ];
    $abcCycle = ['A', 'B', 'C', 'A', 'B'];
    $overrides = $_SESSION['admin_producto_overrides'] ?? [];
    if (!is_array($overrides)) {
        $overrides = [];
    }

    $out = [];
    foreach (mass_productos() as $p) {
        $id = (int) $p['id'];
        $skuNum = str_pad((string) $id, 5, '0', STR_PAD_LEFT);
        $precio = (float) $p['precio'];
        $costo = round($precio * 0.68, 2);
        $margenBruto = round($precio - $costo, 2);
        $margenPct = $precio > 0 ? round(($margenBruto / $precio) * 100, 2) : 0.0;
        $tone = (string) ($p['stockTone'] ?? 'ok');
        $stockPiso = $tone === 'bajo' ? 8 : ($tone === 'critico' ? 0 : 42);
        $stockAlmacen = $tone === 'bajo' ? 4 : 24;
        $abc = $abcCycle[($id - 1) % count($abcCycle)];
        $cat = (string) $p['categoria'];
        $marca = (string) $p['marca'];
        $imgNombre = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $marca)) . '_' . $id . '.jpg';

        $row = [
            'id' => $id,
            'sku' => 'SKU-' . $skuNum,
            'skuInterno' => $skuNum,
            'ean' => '7756' . str_pad((string) (123400000 + $id), 9, '0', STR_PAD_LEFT),
            'nombre' => (string) $p['nombre'],
            'marca' => $marca,
            'categoria' => $cat,
            'subcategoria' => $subs[$cat] ?? 'General',
            'unidad' => (string) ($p['unidad'] ?? 'Unidad'),
            'descripcion' => 'Artículo comercial Mass · ' . $marca . ' · presentación ' . ($p['unidad'] ?? 'unidad') . '. Listo para góndola y reposición WMS.',
            'precio' => $precio,
            'costo' => $costo,
            'margenPct' => $margenPct,
            'margenBruto' => $margenBruto,
            'stockTone' => $tone,
            'stockPiso' => $stockPiso,
            'stockAlmacen' => $stockAlmacen,
            'puntoReorden' => $tone === 'bajo' ? 40 : 25,
            'capacidadGondola' => 200,
            'abc' => $abc,
            'activo' => true,
            'img' => (string) $p['img'],
            'imgNombre' => $imgNombre,
            'esNuevo' => false,
        ];

        if (isset($overrides[$id]) && is_array($overrides[$id])) {
            $row = mass_admin_producto_aplicar_campos($row, $overrides[$id]);
        }

        $out[] = $row;
    }

    $extras = $_SESSION['admin_productos_extra'] ?? [];
    if (is_array($extras)) {
        foreach ($extras as $extra) {
            if (!is_array($extra) || !isset($extra['id'])) {
                continue;
            }
            $out[] = mass_admin_producto_normalizar_fila($extra);
        }
    }

    return $out;
}

/**
 * @param array<string, mixed> $row
 * @param array<string, mixed> $campos
 * @return array<string, mixed>
 */
function mass_admin_producto_aplicar_campos(array $row, array $campos): array
{
    $row = array_merge($row, $campos);
    $row['id'] = (int) ($row['id'] ?? 0);
    $row['precio'] = (float) ($row['precio'] ?? 0);
    $row['costo'] = (float) ($row['costo'] ?? 0);
    $row['margenBruto'] = round($row['precio'] - $row['costo'], 2);
    $row['margenPct'] = $row['precio'] > 0
        ? round(($row['margenBruto'] / $row['precio']) * 100, 2)
        : 0.0;
    $skuNum = str_pad((string) $row['id'], 5, '0', STR_PAD_LEFT);
    $row['skuInterno'] = $skuNum;
    $row['sku'] = 'SKU-' . $skuNum;
    return $row;
}

/**
 * @param array<string, mixed> $fila
 * @return array<string, mixed>
 */
function mass_admin_producto_normalizar_fila(array $fila): array
{
    $id = (int) ($fila['id'] ?? 0);
    $skuNum = str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    $precio = (float) ($fila['precio'] ?? 0);
    $costo = (float) ($fila['costo'] ?? 0);
    $margenBruto = round($precio - $costo, 2);
    $margenPct = $precio > 0 ? round(($margenBruto / $precio) * 100, 2) : 0.0;
    $marca = (string) ($fila['marca'] ?? 'MASS');

    return [
        'id' => $id,
        'sku' => 'SKU-' . $skuNum,
        'skuInterno' => $skuNum,
        'ean' => (string) ($fila['ean'] ?? ''),
        'nombre' => (string) ($fila['nombre'] ?? ''),
        'marca' => $marca,
        'categoria' => (string) ($fila['categoria'] ?? 'Abarrotes'),
        'subcategoria' => (string) ($fila['subcategoria'] ?? 'General'),
        'unidad' => (string) ($fila['unidad'] ?? 'Unidad'),
        'descripcion' => (string) ($fila['descripcion'] ?? ''),
        'precio' => $precio,
        'costo' => $costo,
        'margenPct' => $margenPct,
        'margenBruto' => $margenBruto,
        'stockTone' => (string) ($fila['stockTone'] ?? 'ok'),
        'stockPiso' => (int) ($fila['stockPiso'] ?? 0),
        'stockAlmacen' => (int) ($fila['stockAlmacen'] ?? 0),
        'puntoReorden' => (int) ($fila['puntoReorden'] ?? 25),
        'capacidadGondola' => (int) ($fila['capacidadGondola'] ?? 200),
        'abc' => (string) ($fila['abc'] ?? 'B'),
        'activo' => !empty($fila['activo']),
        'img' => (string) ($fila['img'] ?? 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
        'imgNombre' => (string) ($fila['imgNombre'] ?? ('nuevo_' . $id . '.jpg')),
        'esNuevo' => true,
    ];
}

function mass_admin_producto_por_id(int $id): ?array
{
    foreach (mass_admin_productos() as $p) {
        if ((int) $p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

function mass_admin_producto_siguiente_id(): int
{
    $max = 0;
    foreach (mass_productos() as $p) {
        $max = max($max, (int) $p['id']);
    }
    $extras = $_SESSION['admin_productos_extra'] ?? [];
    if (is_array($extras)) {
        foreach ($extras as $p) {
            if (is_array($p)) {
                $max = max($max, (int) ($p['id'] ?? 0));
            }
        }
    }
    return $max + 1;
}

function mass_admin_producto_plantilla_nueva(): array
{
    $id = mass_admin_producto_siguiente_id();
    $skuNum = str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    return [
        'id' => 0,
        'sku' => 'SKU-' . $skuNum,
        'skuInterno' => $skuNum,
        'ean' => '',
        'nombre' => '',
        'marca' => 'MASS',
        'categoria' => '',
        'subcategoria' => '',
        'unidad' => '',
        'descripcion' => '',
        'precio' => 0.0,
        'costo' => 0.0,
        'margenPct' => 0.0,
        'margenBruto' => 0.0,
        'stockTone' => 'ok',
        'stockPiso' => 0,
        'stockAlmacen' => 0,
        'puntoReorden' => 0,
        'capacidadGondola' => 0,
        'abc' => '',
        'activo' => true,
        'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop',
        'imgNombre' => 'nuevo_producto.jpg',
        'esNuevo' => true,
    ];
}

/**
 * Reglas de negocio catálogo admin (alta / edición).
 *
 * @param array<string, mixed> $data
 * @return array{ok:bool,errores:array<int,string>,data:?array<string,mixed>}
 */
function producto_admin_validar(array $data, int $idExcluir = 0): array
{
    $errores = [];
    $nombre = trim((string) ($data['nombre'] ?? ''));
    $ean = preg_replace('/\D+/', '', (string) ($data['ean'] ?? '')) ?? '';
    $unidad = trim((string) ($data['unidad'] ?? ''));
    $categoria = trim((string) ($data['categoria'] ?? ''));
    $subcategoria = trim((string) ($data['subcategoria'] ?? ''));
    $descripcion = trim((string) ($data['descripcion'] ?? ''));
    $marca = trim((string) ($data['marca'] ?? 'MASS'));
    $precio = (float) ($data['precio'] ?? 0);
    $costo = (float) ($data['costo'] ?? 0);
    $puntoReorden = (int) ($data['puntoReorden'] ?? 0);
    $capacidad = (int) ($data['capacidadGondola'] ?? 0);
    $abc = strtoupper(trim((string) ($data['abc'] ?? '')));
    $activo = !empty($data['activo']) || !empty($data['activo_ui']);

    if ($nombre === '' || mb_strlen($nombre) < 3) {
        $errores[] = 'El nombre comercial debe tener al menos 3 caracteres';
    }
    if (strlen($ean) !== 13) {
        $errores[] = 'El EAN-13 debe tener exactamente 13 dígitos';
    }
    if ($unidad === '') {
        $errores[] = 'Indica la unidad de medida';
    }
    if ($categoria === '') {
        $errores[] = 'Indica la categoría';
    }
    if ($subcategoria === '') {
        $errores[] = 'Indica la subcategoría';
    }
    if ($descripcion === '' || mb_strlen($descripcion) < 10) {
        $errores[] = 'La descripción de góndola debe tener al menos 10 caracteres';
    }
    if ($costo <= 0) {
        $errores[] = 'El costo unitario debe ser mayor a 0';
    }
    if ($precio <= 0) {
        $errores[] = 'El PVP debe ser mayor a 0';
    }
    if ($precio > 0 && $costo > 0 && $precio <= $costo) {
        $errores[] = 'El PVP debe ser mayor al costo (margen comercial positivo)';
    }
    if ($puntoReorden < 1) {
        $errores[] = 'El punto de reorden debe ser al menos 1';
    }
    if ($capacidad < 1) {
        $errores[] = 'La capacidad de góndola debe ser al menos 1';
    }
    if ($capacidad > 0 && $puntoReorden >= $capacidad) {
        $errores[] = 'El punto de reorden debe ser menor a la capacidad máxima de góndola';
    }
    if (!in_array($abc, ['A', 'B', 'C'], true)) {
        $errores[] = 'Selecciona una clasificación ABC (A, B o C)';
    }

    // Unicidad EAN / nombre (alta y edición) — un solo recorrido del catálogo
    $debeValidarUnicidad = $errores === [] || strlen($ean) === 13 || mb_strlen($nombre) >= 3;
    if ($debeValidarUnicidad) {
        $nombreNorm = mb_strtolower($nombre);
        $eanDuplicado = false;
        $nombreDuplicado = false;
        foreach (mass_admin_productos() as $p) {
            if ((int) ($p['id'] ?? 0) === $idExcluir) {
                continue;
            }
            if (!$eanDuplicado && strlen($ean) === 13 && (string) ($p['ean'] ?? '') === $ean) {
                $errores[] = 'El EAN-13 ya está registrado en otro SKU del WMS';
                $eanDuplicado = true;
            }
            if (
                !$nombreDuplicado
                && $nombreNorm !== ''
                && mb_strtolower((string) ($p['nombre'] ?? '')) === $nombreNorm
            ) {
                $errores[] = 'Ya existe un producto con el mismo nombre comercial';
                $nombreDuplicado = true;
            }
            if ($eanDuplicado && $nombreDuplicado) {
                break;
            }
        }
    }

    // Deduplicar mensajes
    $errores = array_values(array_unique($errores));

    if ($errores) {
        return ['ok' => false, 'errores' => $errores, 'data' => null];
    }

    return [
        'ok' => true,
        'errores' => [],
        'data' => [
            'nombre' => $nombre,
            'ean' => $ean,
            'unidad' => $unidad,
            'categoria' => $categoria,
            'subcategoria' => $subcategoria,
            'descripcion' => $descripcion,
            'marca' => $marca !== '' ? $marca : 'MASS',
            'precio' => round($precio, 2),
            'costo' => round($costo, 2),
            'puntoReorden' => $puntoReorden,
            'capacidadGondola' => $capacidad,
            'abc' => $abc,
            'activo' => $activo,
        ],
    ];
}

/**
 * @param array<string, mixed> $data
 * @return array{ok:bool,errores:array<int,string>,id:int}
 */
function mass_admin_producto_guardar(int $id, array $data): array
{
    $valid = producto_admin_validar($data, $id);
    if (!$valid['ok'] || $valid['data'] === null) {
        return ['ok' => false, 'errores' => $valid['errores'], 'id' => $id];
    }
    $campos = $valid['data'];

    // Producto creado en sesión
    $extras = $_SESSION['admin_productos_extra'] ?? [];
    if (!is_array($extras)) {
        $extras = [];
    }
    foreach ($extras as $i => $extra) {
        if (!is_array($extra) || (int) ($extra['id'] ?? 0) !== $id) {
            continue;
        }
        $extras[$i] = array_merge($extra, $campos, [
            'id' => $id,
            'esNuevo' => true,
        ]);
        $_SESSION['admin_productos_extra'] = $extras;
        return ['ok' => true, 'errores' => [], 'id' => $id];
    }

    // Producto del catálogo base → override
    $base = null;
    foreach (mass_productos() as $p) {
        if ((int) $p['id'] === $id) {
            $base = $p;
            break;
        }
    }
    if ($id < 1 || $base === null) {
        return ['ok' => false, 'errores' => ['Producto no encontrado'], 'id' => $id];
    }

    if (!isset($_SESSION['admin_producto_overrides']) || !is_array($_SESSION['admin_producto_overrides'])) {
        $_SESSION['admin_producto_overrides'] = [];
    }
    $_SESSION['admin_producto_overrides'][$id] = $campos;

    if (class_exists('ProductoRepository') && Database::connected()) {
        (new ProductoRepository())->update($id, $campos);
    }

    return ['ok' => true, 'errores' => [], 'id' => $id];
}

/**
 * @param array<string, mixed> $data
 * @return array{ok:bool,errores:array<int,string>,id:int}
 */
function mass_admin_producto_crear(array $data): array
{
    // Alta: idExcluir=0 fuerza unicidad de EAN y nombre contra todo el catálogo
    $valid = producto_admin_validar($data, 0);
    if (!$valid['ok'] || $valid['data'] === null) {
        return ['ok' => false, 'errores' => $valid['errores'], 'id' => 0];
    }

    $id = mass_admin_producto_siguiente_id();
    $campos = $valid['data'];
    if (!isset($_SESSION['admin_productos_extra']) || !is_array($_SESSION['admin_productos_extra'])) {
        $_SESSION['admin_productos_extra'] = [];
    }

    $payload = array_merge($campos, [
        'id' => $id,
        'marca' => $campos['marca'],
        'stockTone' => 'ok',
        'stockPiso' => 0,
        'stockAlmacen' => 0,
        'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop',
        'imgNombre' => 'nuevo_' . $id . '.jpg',
        'esNuevo' => true,
    ]);

    // Con MySQL: fuente de verdad = BD (evita duplicar en sesión + catálogo)
    if (class_exists('ProductoRepository') && Database::connected()) {
        $dbId = (new ProductoRepository())->create($payload);
        if ($dbId > 0) {
            return ['ok' => true, 'errores' => [], 'id' => $dbId];
        }
    }

    $_SESSION['admin_productos_extra'][] = $payload;

    return ['ok' => true, 'errores' => [], 'id' => $id];
}

/** Perfil demo del proveedor conectado (B2B Mass). */
function mass_proveedor_perfil(): array
{
    $u = function_exists('auth_usuario') ? auth_usuario() : null;
    return [
        'nombre' => (string) ($u['nombre'] ?? 'Ana Ríos'),
        'cargo' => (string) ($u['cargo'] ?? 'Ejecutiva de abastecimiento'),
        'empresa' => (string) ($u['empresa'] ?? 'Distribuidora Primor SAC'),
        'ruc' => (string) ($u['ruc'] ?? '20123456789'),
        'email' => (string) ($u['usuario'] ?? 'proveedor@mass.pe'),
        'telefono' => '+51 987 654 321',
        'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=96&h=96&fit=crop',
        'categoria' => 'Abarrotes · Aceites · Granos',
        'slaEntrega' => '48 h',
        'calificacion' => 4.8,
    ];
}

function mass_proveedor_kpis(): array
{
    $ordenes = mass_proveedor_ordenes();
    $pendientes = count(array_filter($ordenes, static fn (array $o): bool => ($o['estado'] ?? '') === 'pendiente'));
    $confirmadas = count(array_filter($ordenes, static fn (array $o): bool => ($o['estado'] ?? '') === 'confirmada'));
    $enRuta = count(array_filter($ordenes, static fn (array $o): bool => ($o['estado'] ?? '') === 'en_ruta'));
    $montoMes = 0.0;
    foreach ($ordenes as $o) {
        if (in_array($o['estado'] ?? '', ['confirmada', 'en_ruta', 'entregada'], true)) {
            $montoMes += (float) ($o['total'] ?? 0);
        }
    }
    return [
        'ocPendientes' => $pendientes,
        'ocConfirmadas' => $confirmadas,
        'entregasRuta' => $enRuta,
        'skusActivos' => count(mass_proveedor_skus()),
        'montoMes' => $montoMes,
        'fillRate' => 96.4,
        'slaCumplido' => 98.1,
    ];
}

function mass_proveedor_ordenes_base(): array
{
    return [
        [
            'id' => 'OC-Mass-2401',
            'tienda' => 'Mass Magdalena',
            'fecha' => '2026-10-26',
            'entrega' => '2026-10-28',
            'estado' => 'pendiente',
            'estadoLabel' => 'Pendiente de confirmar',
            'items' => 4,
            'unidades' => 180,
            'total' => 4280.50,
            'prioridad' => 'alta',
            'lineas' => [
                ['sku' => 'ACE-PRI-900', 'nombre' => 'Aceite Primor 900 ml', 'cant' => 80, 'precio' => 7.20],
                ['sku' => 'ARR-COS-5K', 'nombre' => 'Arroz Costeño 5 kg', 'cant' => 40, 'precio' => 18.50],
                ['sku' => 'FID-DON-1K', 'nombre' => 'Fideos Don Vittorio 1 kg', 'cant' => 36, 'precio' => 4.80],
                ['sku' => 'AZU-BEL-1K', 'nombre' => 'Azúcar Bell\'s 1 kg', 'cant' => 24, 'precio' => 3.90],
            ],
        ],
        [
            'id' => 'OC-Mass-2398',
            'tienda' => 'Mass San Miguel',
            'fecha' => '2026-10-24',
            'entrega' => '2026-10-27',
            'estado' => 'confirmada',
            'estadoLabel' => 'Confirmada',
            'items' => 3,
            'unidades' => 120,
            'total' => 2650.00,
            'prioridad' => 'media',
            'lineas' => [
                ['sku' => 'ACE-PRI-900', 'nombre' => 'Aceite Primor 900 ml', 'cant' => 60, 'precio' => 7.20],
                ['sku' => 'LEC-GLOR-1L', 'nombre' => 'Leche Gloria 1 L', 'cant' => 40, 'precio' => 4.50],
                ['sku' => 'YOG-GLOR-1K', 'nombre' => 'Yogurt Gloria 1 kg', 'cant' => 20, 'precio' => 6.80],
            ],
        ],
        [
            'id' => 'OC-Mass-2390',
            'tienda' => 'Mass Miraflores',
            'fecha' => '2026-10-22',
            'entrega' => '2026-10-25',
            'estado' => 'en_ruta',
            'estadoLabel' => 'En ruta a tienda',
            'items' => 2,
            'unidades' => 90,
            'total' => 1890.00,
            'prioridad' => 'media',
            'lineas' => [
                ['sku' => 'ARR-COS-5K', 'nombre' => 'Arroz Costeño 5 kg', 'cant' => 50, 'precio' => 18.50],
                ['sku' => 'ACE-PRI-900', 'nombre' => 'Aceite Primor 900 ml', 'cant' => 40, 'precio' => 7.20],
            ],
        ],
        [
            'id' => 'OC-Mass-2375',
            'tienda' => 'Mass Magdalena',
            'fecha' => '2026-10-18',
            'entrega' => '2026-10-20',
            'estado' => 'entregada',
            'estadoLabel' => 'Entregada',
            'items' => 5,
            'unidades' => 210,
            'total' => 5120.75,
            'prioridad' => 'baja',
            'lineas' => [
                ['sku' => 'ACE-PRI-900', 'nombre' => 'Aceite Primor 900 ml', 'cant' => 100, 'precio' => 7.20],
                ['sku' => 'ARR-COS-5K', 'nombre' => 'Arroz Costeño 5 kg', 'cant' => 40, 'precio' => 18.50],
                ['sku' => 'FID-DON-1K', 'nombre' => 'Fideos Don Vittorio 1 kg', 'cant' => 30, 'precio' => 4.80],
                ['sku' => 'AZU-BEL-1K', 'nombre' => 'Azúcar Bell\'s 1 kg', 'cant' => 20, 'precio' => 3.90],
                ['sku' => 'LEC-GLOR-1L', 'nombre' => 'Leche Gloria 1 L', 'cant' => 20, 'precio' => 4.50],
            ],
        ],
        [
            'id' => 'OC-Mass-2360',
            'tienda' => 'Mass Surco',
            'fecha' => '2026-10-15',
            'entrega' => '2026-10-17',
            'estado' => 'rechazada',
            'estadoLabel' => 'Rechazada (sin stock)',
            'items' => 1,
            'unidades' => 48,
            'total' => 345.60,
            'prioridad' => 'baja',
            'lineas' => [
                ['sku' => 'ACE-PRI-900', 'nombre' => 'Aceite Primor 900 ml', 'cant' => 48, 'precio' => 7.20],
            ],
        ],
    ];
}

function mass_proveedor_ordenes(?string $filtroEstado = null): array
{
    $overrides = $_SESSION['proveedor_ordenes'] ?? [];
    if (!is_array($overrides)) {
        $overrides = [];
    }
    $labels = [
        'pendiente' => 'Pendiente de confirmar',
        'confirmada' => 'Confirmada',
        'en_ruta' => 'En ruta a tienda',
        'entregada' => 'Entregada',
        'rechazada' => 'Rechazada',
    ];
    $out = [];
    foreach (mass_proveedor_ordenes_base() as $o) {
        $id = (string) $o['id'];
        if (isset($overrides[$id]) && is_array($overrides[$id])) {
            $o = array_merge($o, $overrides[$id]);
            $est = (string) ($o['estado'] ?? 'pendiente');
            $o['estadoLabel'] = $labels[$est] ?? $o['estadoLabel'];
        }
        if ($filtroEstado !== null && $filtroEstado !== '' && $filtroEstado !== 'todos') {
            if (($o['estado'] ?? '') !== $filtroEstado) {
                continue;
            }
        }
        $out[] = $o;
    }
    return $out;
}

function mass_proveedor_orden_por_id(string $id): ?array
{
    foreach (mass_proveedor_ordenes() as $o) {
        if ((string) $o['id'] === $id) {
            return $o;
        }
    }
    return null;
}

/**
 * @return array{ok:bool, errores:array<int,string>}
 */
function proveedor_orden_cambiar_estado(string $id, string $nuevoEstado): array
{
    $permitidos = ['confirmada', 'rechazada', 'en_ruta', 'entregada'];
    if (!in_array($nuevoEstado, $permitidos, true)) {
        return ['ok' => false, 'errores' => ['Estado no permitido']];
    }
    $orden = mass_proveedor_orden_por_id($id);
    if ($orden === null) {
        return ['ok' => false, 'errores' => ['Orden de compra no encontrada']];
    }
    $actual = (string) ($orden['estado'] ?? '');
    $transiciones = [
        'pendiente' => ['confirmada', 'rechazada'],
        'confirmada' => ['en_ruta', 'rechazada'],
        'en_ruta' => ['entregada'],
    ];
    if (!isset($transiciones[$actual]) || !in_array($nuevoEstado, $transiciones[$actual], true)) {
        return ['ok' => false, 'errores' => ['No se puede pasar de ' . $actual . ' a ' . $nuevoEstado]];
    }
    if (!isset($_SESSION['proveedor_ordenes']) || !is_array($_SESSION['proveedor_ordenes'])) {
        $_SESSION['proveedor_ordenes'] = [];
    }
    $_SESSION['proveedor_ordenes'][$id] = ['estado' => $nuevoEstado];
    return ['ok' => true, 'errores' => []];
}

function mass_proveedor_skus(): array
{
    return [
        ['sku' => 'ACE-PRI-900', 'ean' => '7750123456789', 'nombre' => 'Aceite Vegetal Primor 900 ml', 'categoria' => 'Abarrotes', 'precio' => 7.20, 'stock' => 2400, 'moq' => 24, 'lead' => '48 h', 'estado' => 'activo'],
        ['sku' => 'ARR-COS-5K', 'ean' => '7750987654321', 'nombre' => 'Arroz Extra Costeño 5 kg', 'categoria' => 'Abarrotes', 'precio' => 18.50, 'stock' => 860, 'moq' => 10, 'lead' => '72 h', 'estado' => 'activo'],
        ['sku' => 'FID-DON-1K', 'ean' => '7750111222333', 'nombre' => 'Fideos Spaghetti Don Vittorio 1 kg', 'categoria' => 'Abarrotes', 'precio' => 4.80, 'stock' => 1500, 'moq' => 20, 'lead' => '48 h', 'estado' => 'activo'],
        ['sku' => 'AZU-BEL-1K', 'ean' => '7750444555666', 'nombre' => 'Azúcar Rubia Bell\'s 1 kg', 'categoria' => 'Abarrotes', 'precio' => 3.90, 'stock' => 420, 'moq' => 20, 'lead' => '48 h', 'estado' => 'bajo'],
        ['sku' => 'LEC-GLOR-1L', 'ean' => '7750777888999', 'nombre' => 'Leche Evaporada Gloria 1 L', 'categoria' => 'Lácteos', 'precio' => 4.50, 'stock' => 0, 'moq' => 24, 'lead' => '24 h', 'estado' => 'agotado'],
        ['sku' => 'YOG-GLOR-1K', 'ean' => '7750123123456', 'nombre' => 'Yogurt Natural Gloria 1 kg', 'categoria' => 'Lácteos', 'precio' => 6.80, 'stock' => 310, 'moq' => 12, 'lead' => '24 h', 'estado' => 'activo'],
    ];
}

function mass_proveedor_entregas(): array
{
    return [
        ['id' => 'ENT-8821', 'oc' => 'OC-Mass-2390', 'tienda' => 'Mass Miraflores', 'fecha' => '2026-10-25', 'ventana' => '08:00 – 12:00', 'estado' => 'en_ruta', 'chofer' => 'Jorge Palacios', 'placa' => 'B2C-814'],
        ['id' => 'ENT-8810', 'oc' => 'OC-Mass-2398', 'tienda' => 'Mass San Miguel', 'fecha' => '2026-10-27', 'ventana' => '14:00 – 18:00', 'estado' => 'programada', 'chofer' => 'Por asignar', 'placa' => '—'],
        ['id' => 'ENT-8799', 'oc' => 'OC-Mass-2375', 'tienda' => 'Mass Magdalena', 'fecha' => '2026-10-20', 'ventana' => '09:00 – 13:00', 'estado' => 'entregada', 'chofer' => 'Luis Cárdenas', 'placa' => 'A1F-302'],
    ];
}
