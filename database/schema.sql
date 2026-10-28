-- Mass Online / SGC-Mass — esquema MySQL/MariaDB
-- Charset recomendado: utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS mass_online
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mass_online;

DROP TABLE IF EXISTS pedido_detalles;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS inventario;
DROP TABLE IF EXISTS cupones;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS tiendas;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(32) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario VARCHAR(120) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol_id TINYINT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  cargo VARCHAR(120) NOT NULL DEFAULT '',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_usuario (usuario),
  KEY idx_usuarios_rol (rol_id),
  CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categorias (
  id VARCHAR(40) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  badge VARCHAR(40) NOT NULL DEFAULT '',
  badge_tone VARCHAR(20) NOT NULL DEFAULT '',
  count_hint INT UNSIGNED NOT NULL DEFAULT 0,
  cat_filtro VARCHAR(80) NOT NULL DEFAULT '',
  img VARCHAR(500) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(200) NOT NULL,
  marca VARCHAR(80) NOT NULL DEFAULT 'MASS',
  categoria VARCHAR(80) NOT NULL DEFAULT '',
  subcategoria VARCHAR(80) NOT NULL DEFAULT '',
  unidad VARCHAR(80) NOT NULL DEFAULT '',
  descripcion TEXT NULL,
  ean CHAR(13) NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  precio_antes DECIMAL(10,2) NULL,
  costo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock_label VARCHAR(200) NOT NULL DEFAULT '',
  stock_tone VARCHAR(20) NOT NULL DEFAULT 'ok',
  badge_text VARCHAR(80) NULL,
  badge_tone VARCHAR(40) NULL,
  img VARCHAR(500) NOT NULL DEFAULT '',
  img_nombre VARCHAR(120) NOT NULL DEFAULT '',
  favorito TINYINT(1) NOT NULL DEFAULT 0,
  punto_reorden INT UNSIGNED NOT NULL DEFAULT 10,
  capacidad_gondola INT UNSIGNED NOT NULL DEFAULT 50,
  abc CHAR(1) NOT NULL DEFAULT 'B',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_productos_ean (ean),
  KEY idx_productos_categoria (categoria),
  KEY idx_productos_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tiendas (
  id VARCHAR(40) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  direccion VARCHAR(255) NOT NULL DEFAULT '',
  distrito VARCHAR(120) NOT NULL DEFAULT '',
  stock INT NOT NULL DEFAULT 0,
  eta VARCHAR(80) NOT NULL DEFAULT '',
  estado VARCHAR(40) NOT NULL DEFAULT 'ok',
  img VARCHAR(500) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inventario (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  producto_id INT UNSIGNED NOT NULL,
  tienda_id VARCHAR(40) NOT NULL,
  stock_piso INT NOT NULL DEFAULT 0,
  stock_almacen INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_inventario_prod_tienda (producto_id, tienda_id),
  KEY idx_inventario_tienda (tienda_id),
  CONSTRAINT fk_inventario_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON DELETE CASCADE,
  CONSTRAINT fk_inventario_tienda FOREIGN KEY (tienda_id) REFERENCES tiendas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cupones (
  codigo VARCHAR(40) NOT NULL,
  descuento_fijo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedidos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(40) NOT NULL,
  usuario_id INT UNSIGNED NULL,
  entrega VARCHAR(20) NOT NULL DEFAULT 'retiro',
  pago VARCHAR(40) NOT NULL DEFAULT 'yape',
  comprobante VARCHAR(40) NOT NULL DEFAULT 'boleta',
  nombre VARCHAR(120) NOT NULL DEFAULT '',
  apellido VARCHAR(120) NOT NULL DEFAULT '',
  documento VARCHAR(20) NOT NULL DEFAULT '',
  telefono VARCHAR(30) NOT NULL DEFAULT '',
  direccion VARCHAR(255) NOT NULL DEFAULT '',
  referencia VARCHAR(255) NOT NULL DEFAULT '',
  tienda_id VARCHAR(40) NULL,
  horario VARCHAR(40) NOT NULL DEFAULT '',
  cupon VARCHAR(40) NOT NULL DEFAULT '',
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  envio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estado VARCHAR(40) NOT NULL DEFAULT 'confirmado',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pedidos_codigo (codigo),
  KEY idx_pedidos_usuario (usuario_id),
  KEY idx_pedidos_tienda (tienda_id),
  CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT fk_pedidos_tienda FOREIGN KEY (tienda_id) REFERENCES tiendas (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedido_detalles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pedido_id INT UNSIGNED NOT NULL,
  producto_id INT UNSIGNED NULL,
  nombre VARCHAR(200) NOT NULL,
  precio_unit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  cantidad INT UNSIGNED NOT NULL DEFAULT 1,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id),
  KEY idx_detalles_pedido (pedido_id),
  CONSTRAINT fk_detalles_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos (id) ON DELETE CASCADE,
  CONSTRAINT fk_detalles_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
