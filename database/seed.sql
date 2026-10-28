-- Seed Mass Online (generado desde datos demo)
USE mass_online;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM pedido_detalles;
DELETE FROM pedidos;
DELETE FROM inventario;
DELETE FROM productos;
DELETE FROM categorias;
DELETE FROM tiendas;
DELETE FROM cupones;
DELETE FROM usuarios;
DELETE FROM roles;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO roles (id, nombre) VALUES (1, 'admin'), (2, 'cliente');

INSERT INTO usuarios (usuario, password_hash, rol_id, nombre, cargo) VALUES
('admin.operaciones@mass.pe', '$2y$10$MWrE46ay48vQzn7hxxu7bekEQyTK8k1DMfvBt9L3WrBtkmaiQPOVG', 1, 'Gonzalo Méndez', 'Administrador de Tienda'),
('admin', '$2y$10$MWrE46ay48vQzn7hxxu7bekEQyTK8k1DMfvBt9L3WrBtkmaiQPOVG', 1, 'Gonzalo Méndez', 'Administrador WMS'),
('proveedor@mass.pe', '$2y$10$MWrE46ay48vQzn7hxxu7bekEQyTK8k1DMfvBt9L3WrBtkmaiQPOVG', 2, 'Ana Proveedor', 'Cliente / Proveedor Pedidos'),
('cliente', '$2y$10$MWrE46ay48vQzn7hxxu7bekEQyTK8k1DMfvBt9L3WrBtkmaiQPOVG', 2, 'Cliente Demo', 'Portal Pedidos');

INSERT INTO categorias (id, nombre, badge, badge_tone, count_hint, cat_filtro, img) VALUES
('frutas', 'Frutas y Verduras', 'Diario', '', 85, 'Frutas', 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=400&h=400&fit=crop'),
('abarrotes', 'Abarrotes', 'Básicos', 'o', 240, 'Abarrotes', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
('lacteos', 'Lácteos y Huevos', 'Fresco', '', 64, 'Lácteos', 'https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=400&h=400&fit=crop'),
('bebidas', 'Bebidas y Jugos', 'Refresco', 'o', 70, 'Bebidas', 'https://images.unsplash.com/photo-1625772299848-391b6a87d7b3?w=400&h=400&fit=crop'),
('snacks', 'Snacks y Piques', 'Antojos', '', 110, 'Abarrotes', 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=400&h=400&fit=crop'),
('limpieza', 'Limpieza Hogar', 'Hogar', '', 95, 'Limpieza', 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=400&h=400&fit=crop'),
('cuidado', 'Cuidado Personal', 'Cuidado', 'o', 78, 'Limpieza', 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop');

INSERT INTO tiendas (id, nombre, direccion, distrito, stock, eta, estado, img) VALUES
('magdalena', 'Tienda Magdalena Sucre', 'Av. Sucre 420, Magdalena del Mar', '', 42, '', 'actual', ''),
('pueblo_libre', 'Tienda Pueblo Libre', 'Av. Bolívar 598, Pueblo Libre', '', 18, '', 'ok', ''),
('san_miguel', 'Tienda San Miguel', 'Av. La Marina 2055, San Miguel', '', 4, '', 'bajo', ''),
('brena', 'Tienda Breña', 'Jr. Huáscar 1120, Breña', '', 0, '', 'agotado', '');

INSERT INTO cupones (codigo, descuento_fijo, activo) VALUES ('CLUBMASS10', 5.00, 1), ('CLUBMASS60', 6.00, 1);

INSERT INTO productos (id, nombre, marca, categoria, subcategoria, unidad, descripcion, ean, precio, precio_antes, costo, stock_label, stock_tone, badge_text, badge_tone, img, img_nombre, favorito, punto_reorden, capacidad_gondola, abc, activo) VALUES
(1, 'Aceite Vegetal Clásico Primor 900 ml', 'PRIMOR', 'Abarrotes', 'Aceites y Grasas', 'Botella 900 ml', 'Artículo comercial Mass · PRIMOR · presentación Botella 900 ml. Listo para góndola y reposición WMS.', '7756123400001', 8.90, 11.50, 6.05, '42 unidades disponibles en Magdalena', 'ok', '-22% OFERTA', 'oferta', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=800&h=800&fit=crop', 'primor_1.jpg', 1, 25, 200, 'A', 1),
(2, 'Arroz Extra Grano Largo', 'COSTEÑO', 'Abarrotes', 'Aceites y Grasas', 'Bolsa 5 kg', 'Artículo comercial Mass · COSTEÑO · presentación Bolsa 5 kg. Listo para góndola y reposición WMS.', '7756123400002', 22.90, 26.50, 15.57, '41 unidades disponibles en Magdalena', 'ok', 'MÁS VENDIDO', 'verde', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500&h=500&fit=crop', 'coste_o_2.jpg', 1, 25, 200, 'B', 1),
(3, 'Leche Evaporada Entera Azul Pack', 'GLORIA', 'Lácteos', 'Leches y Derivados', 'Pack x 6 latas (400g c/u)', 'Artículo comercial Mass · GLORIA · presentación Pack x 6 latas (400g c/u). Listo para góndola y reposición WMS.', '7756123400003', 19.80, 23.50, 13.46, '42 packs disponibles', 'ok', 'AHORRO PACK x6', 'lima', 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=500&h=500&fit=crop', 'gloria_3.jpg', 0, 25, 200, 'C', 1),
(4, 'Palta Hass Seleccionada', 'CAMPO MASS', 'Frutas', 'Frescos Seleccionados', 'Malla 1 kg', 'Artículo comercial Mass · CAMPO MASS · presentación Malla 1 kg. Listo para góndola y reposición WMS.', '7756123400004', 8.50, 11.20, 5.78, '¡Bajo stock! Solo 6 mallas', 'bajo', 'COSECHA HOY', 'verde', 'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=500&h=500&fit=crop', 'campo_mass_4.jpg', 1, 40, 200, 'A', 1),
(5, 'Detergente en Polvo Floral', 'BOLÍVAR', 'Limpieza', 'Hogar y Cuidado', 'Bolsa 1 kg', 'Artículo comercial Mass · BOLÍVAR · presentación Bolsa 1 kg. Listo para góndola y reposición WMS.', '7756123400005', 11.90, 14.50, 8.09, '22 unidades disponibles en Magdalena', 'ok', '-18% OFERTA', 'oferta', 'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?w=500&h=500&fit=crop', 'bol_var_5.jpg', 0, 25, 200, 'B', 1),
(6, 'Gaseosa Cola Familiar', 'INKA KOLA', 'Bebidas', 'Gaseosas y Jugos', 'Botella 2.25 L', 'Artículo comercial Mass · INKA KOLA · presentación Botella 2.25 L. Listo para góndola y reposición WMS.', '7756123400006', 7.50, 8.90, 5.10, '35 unidades disponibles en Magdalena', 'ok', 'MÁS VENDIDO', 'verde', 'https://images.unsplash.com/photo-1625772299848-391b6a87d7b3?w=500&h=500&fit=crop', 'inka_kola_6.jpg', 1, 25, 200, 'A', 1),
(7, 'Huevos Blancos Frescos', 'LA CALERA', 'Lácteos', 'Leches y Derivados', 'Pack x12', 'Artículo comercial Mass · LA CALERA · presentación Pack x12. Listo para góndola y reposición WMS.', '7756123400007', 9.90, 11.50, 6.73, '14 packs disponibles en Magdalena', 'ok', '-14% OFERTA', 'oferta', 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=500&h=500&fit=crop', 'la_calera_7.jpg', 0, 25, 200, 'B', 1),
(8, 'Plátano de Seda', 'CAMPO MASS', 'Frutas', 'Frescos Seleccionados', 'Kg', 'Artículo comercial Mass · CAMPO MASS · presentación Kg. Listo para góndola y reposición WMS.', '7756123400008', 3.50, 4.20, 2.38, '18 kg disponibles en Magdalena', 'ok', 'COSECHA HOY', 'verde', 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=500&h=500&fit=crop', 'campo_mass_8.jpg', 1, 25, 200, 'C', 1),
(9, 'Arroz Extra Añejo Selección Costeño', 'COSTEÑO', 'Abarrotes', 'Aceites y Grasas', 'Bolsa 5 kg', 'Artículo comercial Mass · COSTEÑO · presentación Bolsa 5 kg. Listo para góndola y reposición WMS.', '7756123400009', 23.50, 27.90, 15.98, '33 unidades disponibles', 'ok', 'MÁS AHORRO', 'verde', 'https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?w=500&h=500&fit=crop', 'coste_o_9.jpg', 0, 25, 200, 'A', 1),
(10, 'Avena en Hojuelas Enteras Mass', 'MASS', 'Abarrotes', 'Aceites y Grasas', 'Bolsa 500 g', 'Artículo comercial Mass · MASS · presentación Bolsa 500 g. Listo para góndola y reposición WMS.', '7756123400010', 4.90, NULL, 3.33, '50 unidades disponibles', 'ok', 'NUEVO', 'lima', 'https://images.unsplash.com/photo-1517673132405-a56a62b18caf?w=500&h=500&fit=crop', 'mass_10.jpg', 0, 25, 200, 'B', 1),
(11, 'Café Gourmet Tostado Molido', 'ALTOMAYO', 'Abarrotes', 'Aceites y Grasas', 'Frasco 250 g', 'Artículo comercial Mass · ALTOMAYO · presentación Frasco 250 g. Listo para góndola y reposición WMS.', '7756123400011', 14.80, 17.50, 10.06, '19 unidades disponibles', 'ok', 'AHORRO PACK', 'oferta', 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=500&h=500&fit=crop', 'altomayo_11.jpg', 1, 25, 200, 'A', 1),
(12, 'Fideos Spaghetti Clásicos Don Vittorio', 'DON VITTORIO', 'Abarrotes', 'Aceites y Grasas', 'Paquete 500 g', 'Artículo comercial Mass · DON VITTORIO · presentación Paquete 500 g. Listo para góndola y reposición WMS.', '7756123400012', 4.50, 5.20, 3.06, '60 unidades disponibles', 'ok', NULL, NULL, 'https://images.unsplash.com/photo-1551462147-3787042458dae?w=500&h=500&fit=crop', 'don_vittorio_12.jpg', 0, 25, 200, 'B', 1),
(13, 'Lentejas Bebé Seleccionadas', 'COSTENITO', 'Abarrotes', 'Aceites y Grasas', 'Bolsa 500 g', 'Artículo comercial Mass · COSTENITO · presentación Bolsa 500 g. Listo para góndola y reposición WMS.', '7756123400013', 4.30, 4.90, 2.92, '27 unidades disponibles', 'ok', NULL, NULL, 'https://images.unsplash.com/photo-1596797038530-2c107229654b?w=500&h=500&fit=crop', 'costenito_13.jpg', 0, 25, 200, 'C', 1),
(14, 'Leche Evaporada Entera Gloria Pack 6', 'GLORIA', 'Lácteos', 'Leches y Derivados', 'Pack x6 latas', 'Artículo comercial Mass · GLORIA · presentación Pack x6 latas. Listo para góndola y reposición WMS.', '7756123400014', 24.20, 27.00, 16.46, '38 packs disponibles', 'ok', 'AHORRO PACK', 'lima', 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=500&h=500&fit=crop', 'gloria_14.jpg', 1, 25, 200, 'A', 1),
(15, 'Yogurt Natural Bebible', 'LAIVE', 'Lácteos', 'Leches y Derivados', 'Botella 1 L', 'Artículo comercial Mass · LAIVE · presentación Botella 1 L. Listo para góndola y reposición WMS.', '7756123400015', 6.50, 7.90, 4.42, '¡Bajo stock! Solo 8 unidades', 'bajo', '-18% OFERTA', 'oferta', 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=500&h=500&fit=crop', 'laive_15.jpg', 0, 40, 200, 'B', 1);

ALTER TABLE productos AUTO_INCREMENT = 100;

INSERT INTO inventario (producto_id, tienda_id, stock_piso, stock_almacen) VALUES
(1, 'magdalena', 42, 24),
(2, 'magdalena', 42, 24),
(3, 'magdalena', 42, 24),
(4, 'magdalena', 8, 4),
(5, 'magdalena', 42, 24),
(6, 'magdalena', 42, 24),
(7, 'magdalena', 42, 24),
(8, 'magdalena', 42, 24),
(9, 'magdalena', 42, 24),
(10, 'magdalena', 42, 24),
(11, 'magdalena', 42, 24),
(12, 'magdalena', 42, 24),
(13, 'magdalena', 42, 24),
(14, 'magdalena', 42, 24),
(15, 'magdalena', 8, 4);

