/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `actividades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actividades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_sia_id` bigint unsigned NOT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `meta` text COLLATE utf8mb4_unicode_ci,
  `unidad_medida` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad_meta` decimal(15,2) DEFAULT NULL,
  `estado` enum('formulacion','activo','suspendida','terminada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'formulacion',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `actividades_proyecto_sia_id_codigo_unique` (`proyecto_sia_id`,`codigo`),
  CONSTRAINT `actividades_proyecto_sia_id_foreign` FOREIGN KEY (`proyecto_sia_id`) REFERENCES `proyectos_sia` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `almacenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `almacenes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsable` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `almacenes_codigo_unique` (`codigo`),
  KEY `almacenes_creado_por_foreign` (`creado_por`),
  CONSTRAINT `almacenes_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `arqueos_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `arqueos_caja` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `caja_id` bigint unsigned NOT NULL,
  `fecha` date NOT NULL,
  `monto_apertura` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_ingresos` decimal(18,2) NOT NULL DEFAULT '0.00',
  `monto_cierre` decimal(18,2) NOT NULL DEFAULT '0.00',
  `diferencia` decimal(18,2) NOT NULL DEFAULT '0.00',
  `estado` enum('abierto','cerrado','aprobado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierto',
  `aprobado_por` bigint unsigned DEFAULT NULL,
  `fecha_aprobacion` timestamp NULL DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `arqueos_caja_caja_id_foreign` (`caja_id`),
  KEY `arqueos_caja_aprobado_por_foreign` (`aprobado_por`),
  KEY `arqueos_caja_creado_por_foreign` (`creado_por`),
  CONSTRAINT `arqueos_caja_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `arqueos_caja_caja_id_foreign` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `arqueos_caja_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `articulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articulos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('bien','servicio','material','equipo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'material',
  `unidad_medida` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unidad',
  `precio_referencia` decimal(18,2) NOT NULL DEFAULT '0.00',
  `stock_actual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stock_minimo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `categoria` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `almacen_id` bigint unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articulos_codigo_unique` (`codigo`),
  KEY `articulos_almacen_id_foreign` (`almacen_id`),
  KEY `articulos_creado_por_foreign` (`creado_por`),
  CONSTRAINT `articulos_almacen_id_foreign` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `articulos_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asientos_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asientos_contables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `periodo_contable_id` bigint unsigned NOT NULL,
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `tipo` enum('manual','apertura','ajuste','cierre','reclasificacion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `concepto` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_asiento` date NOT NULL,
  `total_debe` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_haber` decimal(18,2) NOT NULL DEFAULT '0.00',
  `estado` enum('borrador','registrado','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `motivo_anulacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT NULL,
  `fecha_anulacion` timestamp NULL DEFAULT NULL,
  `registrado_por` bigint unsigned DEFAULT NULL,
  `anulado_por` bigint unsigned DEFAULT NULL,
  `creado_por` bigint unsigned DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asientos_contables_numero_unique` (`numero`),
  KEY `asientos_contables_periodo_contable_id_foreign` (`periodo_contable_id`),
  KEY `asientos_contables_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  KEY `asientos_contables_registrado_por_foreign` (`registrado_por`),
  KEY `asientos_contables_anulado_por_foreign` (`anulado_por`),
  KEY `asientos_contables_creado_por_foreign` (`creado_por`),
  CONSTRAINT `asientos_contables_anulado_por_foreign` FOREIGN KEY (`anulado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asientos_contables_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asientos_contables_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `asientos_contables_periodo_contable_id_foreign` FOREIGN KEY (`periodo_contable_id`) REFERENCES `periodos_contables` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `asientos_contables_registrado_por_foreign` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asientos_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asientos_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asiento_contable_id` bigint unsigned NOT NULL,
  `cuenta_contable_id` bigint unsigned NOT NULL,
  `descripcion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `debe` decimal(18,2) NOT NULL DEFAULT '0.00',
  `haber` decimal(18,2) NOT NULL DEFAULT '0.00',
  `orden` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asientos_detalle_asiento_contable_id_foreign` (`asiento_contable_id`),
  KEY `asientos_detalle_cuenta_contable_id_foreign` (`cuenta_contable_id`),
  CONSTRAINT `asientos_detalle_asiento_contable_id_foreign` FOREIGN KEY (`asiento_contable_id`) REFERENCES `asientos_contables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asientos_detalle_cuenta_contable_id_foreign` FOREIGN KEY (`cuenta_contable_id`) REFERENCES `cuentas_contables` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint unsigned NOT NULL,
  `old_values` text COLLATE utf8mb4_unicode_ci,
  `new_values` text COLLATE utf8mb4_unicode_ci,
  `url` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(1023) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audits_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `audits_user_id_user_type_index` (`user_id`,`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `beneficiarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `beneficiarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rif` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon_social` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comercial` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('proveedor','contratista','funcionario','otro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'proveedor',
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banco_nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banco_cuenta` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banco_tipo_cuenta` enum('corriente','ahorro','otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `beneficiarios_rif_unique` (`rif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bienes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bienes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ejercicio_fiscal_id` bigint unsigned DEFAULT NULL,
  `numero_inventario` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_bien_id` bigint unsigned NOT NULL,
  `unidad_ejecutora_id` bigint unsigned NOT NULL,
  `descripcion` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `marca` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anio_adquisicion` smallint DEFAULT NULL,
  `valor_adquisicion` decimal(18,2) NOT NULL DEFAULT '0.00',
  `valor_actual` decimal(18,2) NOT NULL DEFAULT '0.00',
  `ubicacion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsable` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('activo','en_reparacion','dado_de_baja','extraviado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_incorporacion` date NOT NULL,
  `fecha_baja` date DEFAULT NULL,
  `motivo_baja` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bienes_numero_inventario_unique` (`numero_inventario`),
  KEY `bienes_categoria_bien_id_foreign` (`categoria_bien_id`),
  KEY `bienes_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  KEY `bienes_creado_por_foreign` (`creado_por`),
  KEY `bienes_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  CONSTRAINT `bienes_categoria_bien_id_foreign` FOREIGN KEY (`categoria_bien_id`) REFERENCES `categorias_bien` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `bienes_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bienes_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bienes_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cajas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cajas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsable_id` bigint unsigned DEFAULT NULL,
  `unidad_ejecutora_id` bigint unsigned NOT NULL,
  `estado` enum('abierta','cerrada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cerrada',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cajas_codigo_unique` (`codigo`),
  KEY `cajas_responsable_id_foreign` (`responsable_id`),
  KEY `cajas_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  CONSTRAINT `cajas_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cajas_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel` enum('directivo','profesional','tecnico','administrativo','obrero') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'administrativo',
  `salario_base` decimal(18,2) NOT NULL DEFAULT '0.00',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cargos_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorias_bien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_bien` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vida_util_anios` tinyint unsigned NOT NULL DEFAULT '5',
  `tasa_depreciacion` decimal(8,4) NOT NULL DEFAULT '20.0000',
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorias_bien_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `causaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `causaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `compromiso_id` bigint unsigned DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nro. de la causación/orden de pago',
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `unidad_ejecutora_id` bigint unsigned NOT NULL,
  `credito_presupuestario_id` bigint unsigned DEFAULT NULL,
  `partida_presupuestaria_id` bigint unsigned NOT NULL,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `beneficiario` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Proveedor o beneficiario',
  `rif_beneficiario` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_documento` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'factura' COMMENT 'factura, contrato, recibo, planilla, otro',
  `numero_documento` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_documento` date DEFAULT NULL,
  `descripcion_documento` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto_causado` decimal(18,2) NOT NULL DEFAULT '0.00',
  `monto_sin_iva` decimal(15,2) DEFAULT NULL COMMENT 'Monto base de la factura sin IVA',
  `alicuota_iva` decimal(5,2) DEFAULT NULL COMMENT 'Alícuota IVA cobrada por el proveedor (ej: 16.00%)',
  `monto_retencion` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT 'ISLR, IVA u otras retenciones',
  `monto_neto` decimal(18,2) GENERATED ALWAYS AS ((`monto_causado` - `monto_retencion`)) STORED,
  `concepto` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Descripción del gasto',
  `estado` enum('borrador','aprobada','pagada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `fecha_causacion` date NOT NULL,
  `fecha_aprobacion` date DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_anulacion` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `aprobado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `causaciones_numero_unique` (`numero`),
  KEY `causaciones_credito_presupuestario_id_foreign` (`credito_presupuestario_id`),
  KEY `causaciones_proyecto_id_foreign` (`proyecto_id`),
  KEY `causaciones_created_by_foreign` (`created_by`),
  KEY `causaciones_aprobado_por_foreign` (`aprobado_por`),
  KEY `causaciones_ejercicio_estado_idx` (`ejercicio_fiscal_id`,`estado`),
  KEY `causaciones_partida_estado_idx` (`partida_presupuestaria_id`,`estado`),
  KEY `causaciones_unidad_estado_idx` (`unidad_ejecutora_id`,`estado`),
  KEY `causaciones_compromiso_idx` (`compromiso_id`),
  CONSTRAINT `causaciones_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`),
  CONSTRAINT `causaciones_compromiso_id_foreign` FOREIGN KEY (`compromiso_id`) REFERENCES `compromisos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `causaciones_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `causaciones_credito_presupuestario_id_foreign` FOREIGN KEY (`credito_presupuestario_id`) REFERENCES `creditos_presupuestarios` (`id`),
  CONSTRAINT `causaciones_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`),
  CONSTRAINT `causaciones_partida_presupuestaria_id_foreign` FOREIGN KEY (`partida_presupuestaria_id`) REFERENCES `partidas_presupuestarias` (`id`),
  CONSTRAINT `causaciones_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos_sia` (`id`),
  CONSTRAINT `causaciones_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `compromisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compromisos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `unidad_ejecutora_id` bigint unsigned NOT NULL,
  `credito_presupuestario_id` bigint unsigned DEFAULT NULL,
  `partida_presupuestaria_id` bigint unsigned NOT NULL,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `beneficiario` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rif_beneficiario` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiario_id` bigint unsigned DEFAULT NULL,
  `concepto` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` enum('factura','contrato','recibo','planilla','otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_documento` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_documento` date DEFAULT NULL,
  `descripcion_documento` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(18,2) NOT NULL,
  `monto_sin_iva` decimal(15,2) DEFAULT NULL COMMENT 'Monto base de la factura antes del IVA',
  `alicuota_iva` decimal(5,2) DEFAULT '16.00' COMMENT 'Alícuota IVA del proveedor (ej: 16.00%)',
  `fecha_compromiso` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('borrador','aprobado','causado','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_anulacion` text COLLATE utf8mb4_unicode_ci,
  `fecha_aprobacion` date DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `aprobado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `compromisos_numero_unique` (`numero`),
  KEY `compromisos_credito_presupuestario_id_foreign` (`credito_presupuestario_id`),
  KEY `compromisos_proyecto_id_foreign` (`proyecto_id`),
  KEY `compromisos_created_by_foreign` (`created_by`),
  KEY `compromisos_aprobado_por_foreign` (`aprobado_por`),
  KEY `compromisos_beneficiario_id_foreign` (`beneficiario_id`),
  KEY `compromisos_ejercicio_estado_idx` (`ejercicio_fiscal_id`,`estado`),
  KEY `compromisos_partida_estado_idx` (`partida_presupuestaria_id`,`estado`),
  KEY `compromisos_unidad_estado_idx` (`unidad_ejecutora_id`,`estado`),
  CONSTRAINT `compromisos_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`),
  CONSTRAINT `compromisos_beneficiario_id_foreign` FOREIGN KEY (`beneficiario_id`) REFERENCES `beneficiarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `compromisos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `compromisos_credito_presupuestario_id_foreign` FOREIGN KEY (`credito_presupuestario_id`) REFERENCES `creditos_presupuestarios` (`id`),
  CONSTRAINT `compromisos_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`),
  CONSTRAINT `compromisos_partida_presupuestaria_id_foreign` FOREIGN KEY (`partida_presupuestaria_id`) REFERENCES `partidas_presupuestarias` (`id`),
  CONSTRAINT `compromisos_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos_sia` (`id`),
  CONSTRAINT `compromisos_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conceptos_ingreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conceptos_ingreso` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('tasa','multa','devolucion','transferencia','intereses','otro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tasa',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conceptos_ingreso_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conceptos_nomina`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conceptos_nomina` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('asignacion','deduccion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'asignacion',
  `calculo` enum('fijo','porcentaje') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fijo',
  `valor` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `aplica_a` enum('todos','fijos','contratados','obreros') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'todos',
  `es_obligatorio` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conceptos_nomina_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conciliacion_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conciliacion_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conciliacion_bancaria_id` bigint unsigned NOT NULL,
  `movimiento_bancario_id` bigint unsigned DEFAULT NULL,
  `tipo_partida` enum('en_banco_no_en_libros','en_libros_no_en_banco','conciliado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'conciliado',
  `descripcion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conciliacion_detalles_conciliacion_bancaria_id_foreign` (`conciliacion_bancaria_id`),
  KEY `conciliacion_detalles_movimiento_bancario_id_foreign` (`movimiento_bancario_id`),
  CONSTRAINT `conciliacion_detalles_conciliacion_bancaria_id_foreign` FOREIGN KEY (`conciliacion_bancaria_id`) REFERENCES `conciliaciones_bancarias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conciliacion_detalles_movimiento_bancario_id_foreign` FOREIGN KEY (`movimiento_bancario_id`) REFERENCES `movimientos_bancarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conciliaciones_bancarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conciliaciones_bancarias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuenta_bancaria_id` bigint unsigned NOT NULL,
  `ejercicio_fiscal_id` bigint unsigned DEFAULT NULL,
  `anio` year NOT NULL,
  `mes` tinyint NOT NULL,
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date NOT NULL,
  `saldo_segun_banco` decimal(18,2) NOT NULL DEFAULT '0.00',
  `saldo_segun_libros` decimal(18,2) NOT NULL DEFAULT '0.00',
  `diferencia` decimal(18,2) NOT NULL DEFAULT '0.00',
  `estado` enum('borrador','aprobada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `fecha_aprobacion` date DEFAULT NULL,
  `aprobado_por` bigint unsigned DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conciliaciones_bancarias_cuenta_bancaria_id_anio_mes_unique` (`cuenta_bancaria_id`,`anio`,`mes`),
  UNIQUE KEY `conciliaciones_bancarias_numero_unique` (`numero`),
  KEY `conciliaciones_bancarias_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  KEY `conciliaciones_bancarias_aprobado_por_foreign` (`aprobado_por`),
  KEY `conciliaciones_bancarias_creado_por_foreign` (`creado_por`),
  CONSTRAINT `conciliaciones_bancarias_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conciliaciones_bancarias_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conciliaciones_bancarias_cuenta_bancaria_id_foreign` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conciliaciones_bancarias_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `creditos_presupuestarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `creditos_presupuestarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `partida_presupuestaria_id` bigint unsigned NOT NULL,
  `unidad_ejecutora_id` bigint unsigned NOT NULL,
  `fuente_financiamiento_id` bigint unsigned DEFAULT NULL,
  `proyecto_sia_id` bigint unsigned DEFAULT NULL,
  `actividad_id` bigint unsigned DEFAULT NULL,
  `monto_aprobado` decimal(18,2) NOT NULL DEFAULT '0.00',
  `monto_modificado` decimal(18,2) NOT NULL DEFAULT '0.00',
  `monto_comprometido` decimal(18,2) NOT NULL DEFAULT '0.00',
  `monto_causado` decimal(18,2) NOT NULL DEFAULT '0.00',
  `monto_pagado` decimal(18,2) NOT NULL DEFAULT '0.00',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `credito_unico` (`ejercicio_fiscal_id`,`partida_presupuestaria_id`,`unidad_ejecutora_id`,`fuente_financiamiento_id`),
  KEY `creditos_presupuestarios_partida_presupuestaria_id_foreign` (`partida_presupuestaria_id`),
  KEY `creditos_presupuestarios_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  KEY `creditos_presupuestarios_fuente_financiamiento_id_foreign` (`fuente_financiamiento_id`),
  KEY `creditos_presupuestarios_proyecto_sia_id_foreign` (`proyecto_sia_id`),
  KEY `creditos_presupuestarios_actividad_id_foreign` (`actividad_id`),
  KEY `creditos_presupuestarios_creado_por_foreign` (`creado_por`),
  CONSTRAINT `creditos_presupuestarios_actividad_id_foreign` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `creditos_presupuestarios_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `creditos_presupuestarios_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `creditos_presupuestarios_fuente_financiamiento_id_foreign` FOREIGN KEY (`fuente_financiamiento_id`) REFERENCES `fuentes_financiamiento` (`id`) ON DELETE SET NULL,
  CONSTRAINT `creditos_presupuestarios_partida_presupuestaria_id_foreign` FOREIGN KEY (`partida_presupuestaria_id`) REFERENCES `partidas_presupuestarias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `creditos_presupuestarios_proyecto_sia_id_foreign` FOREIGN KEY (`proyecto_sia_id`) REFERENCES `proyectos_sia` (`id`) ON DELETE SET NULL,
  CONSTRAINT `creditos_presupuestarios_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cuentas_bancarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuentas_bancarias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `banco` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_cuenta` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('corriente','ahorro','fondo','otro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'corriente',
  `moneda` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VES',
  `saldo_inicial` decimal(18,2) NOT NULL DEFAULT '0.00',
  `saldo_actual` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha_apertura` date DEFAULT NULL,
  `estado` enum('activa','inactiva','bloqueada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `firmante_1` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firmante_2` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ejercicio_fiscal_id` bigint unsigned DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuentas_bancarias_codigo_unique` (`codigo`),
  UNIQUE KEY `cuentas_bancarias_numero_cuenta_unique` (`numero_cuenta`),
  KEY `cuentas_bancarias_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  KEY `cuentas_bancarias_creado_por_foreign` (`creado_por`),
  CONSTRAINT `cuentas_bancarias_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cuentas_bancarias_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cuentas_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuentas_contables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clase` enum('1','2','3','4','5','6','7') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '1=Activo,2=Pasivo,3=Patrimonio,4=Gastos,5=Ingresos,6=OrdenDeudora,7=OrdenAcreedora',
  `tipo` enum('activo','pasivo','patrimonio','gasto','ingreso','orden_deudora','orden_acreedora') COLLATE utf8mb4_unicode_ci NOT NULL,
  `naturaleza` enum('deudora','acreedora') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Deudora=Activos+Gastos, Acreedora=Pasivos+Patrimonio+Ingresos',
  `nivel` int NOT NULL DEFAULT '1' COMMENT 'Profundidad en la jerarquía',
  `parent_id` bigint unsigned DEFAULT NULL,
  `permite_movimiento` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Solo cuentas de detalle (hoja) aceptan asientos',
  `saldo_inicial` decimal(18,2) NOT NULL DEFAULT '0.00',
  `saldo_actual` decimal(18,2) NOT NULL DEFAULT '0.00',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuentas_contables_codigo_unique` (`codigo`),
  KEY `cuentas_contables_parent_id_foreign` (`parent_id`),
  KEY `cuentas_contables_creado_por_foreign` (`creado_por`),
  CONSTRAINT `cuentas_contables_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cuentas_contables_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `cuentas_contables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ejercicios_fiscales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ejercicios_fiscales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `anio` year NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('borrador','activo','cerrado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `cerrado_por` bigint unsigned DEFAULT NULL,
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ejercicios_fiscales_anio_unique` (`anio`),
  KEY `ejercicios_fiscales_creado_por_foreign` (`creado_por`),
  KEY `ejercicios_fiscales_cerrado_por_foreign` (`cerrado_por`),
  CONSTRAINT `ejercicios_fiscales_cerrado_por_foreign` FOREIGN KEY (`cerrado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ejercicios_fiscales_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empleado_bonificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleado_bonificaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `concepto_nomina_id` bigint unsigned NOT NULL,
  `monto` decimal(18,2) DEFAULT NULL COMMENT 'Monto personalizado. Si es NULL, se calcula usando la regla del concepto general.',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `registrado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empleado_bonificaciones_empleado_id_foreign` (`empleado_id`),
  KEY `empleado_bonificaciones_concepto_nomina_id_foreign` (`concepto_nomina_id`),
  KEY `empleado_bonificaciones_registrado_por_foreign` (`registrado_por`),
  CONSTRAINT `empleado_bonificaciones_concepto_nomina_id_foreign` FOREIGN KEY (`concepto_nomina_id`) REFERENCES `conceptos_nomina` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `empleado_bonificaciones_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `empleado_bonificaciones_registrado_por_foreign` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empleado_familiares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleado_familiares` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `nombre_completo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parentesco` enum('hijo','conyuge','padre','madre','hermano','otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_carga_familiar` tinyint(1) NOT NULL DEFAULT '0',
  `observaciones` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empleado_familiares_empleado_id_foreign` (`empleado_id`),
  CONSTRAINT `empleado_familiares_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empleado_formaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleado_formaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `tipo` enum('curso','certificacion','diplomado','postgrado','maestria','doctorado','idioma','otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `institucion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `en_curso` tinyint(1) NOT NULL DEFAULT '0',
  `duracion_horas` int DEFAULT NULL,
  `nivel_idioma` enum('basico','intermedio','avanzado','nativo') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_registro` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `documento_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registrado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empleado_formaciones_empleado_id_foreign` (`empleado_id`),
  KEY `empleado_formaciones_registrado_por_foreign` (`registrado_por`),
  CONSTRAINT `empleado_formaciones_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `empleado_formaciones_registrado_por_foreign` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empleado_historial_cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleado_historial_cargos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `cargo_id` bigint unsigned DEFAULT NULL,
  `cargo_texto` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institucion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'null = institución actual; texto libre para empleos anteriores externos',
  `unidad_ejecutora_id` bigint unsigned DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL COMMENT 'null = cargo actual',
  `motivo_cambio` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registrado_por` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empleado_historial_cargos_empleado_id_foreign` (`empleado_id`),
  KEY `empleado_historial_cargos_cargo_id_foreign` (`cargo_id`),
  KEY `empleado_historial_cargos_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  KEY `empleado_historial_cargos_registrado_por_foreign` (`registrado_por`),
  CONSTRAINT `empleado_historial_cargos_cargo_id_foreign` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empleado_historial_cargos_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `empleado_historial_cargos_registrado_por_foreign` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `empleado_historial_cargos_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cedula` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pasaporte` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexo` enum('F','M') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'M',
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `primer_apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `segundo_apellido` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_id` bigint unsigned NOT NULL,
  `unidad_ejecutora_id` bigint unsigned NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `tipo` enum('fijo','contratado','obrero') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fijo',
  `banco` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_cuenta` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_civil` enum('soltero','casado','divorciado','viudo','concubinato') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nacionalidad` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais_origen` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_carnet_militar` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_expedicion_militar` date DEFAULT NULL,
  `carnet_militar_foto_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `pais_nacimiento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_nacimiento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `municipio_nacimiento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lugar_nacimiento` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel_instruccion` enum('sin_instruccion','primaria','secundaria','tsu','universitario','postgrado','doctorado') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institucion_educativa` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais_residencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_residencia` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `municipio` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parroquia` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion_completa` text COLLATE utf8mb4_unicode_ci,
  `curriculum_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_sangre` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiene_discapacidad` tinyint(1) NOT NULL DEFAULT '0',
  `tipo_discapacidad` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condicion_medica` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto_emergencia_nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto_emergencia_parentesco` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto_emergencia_telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('activo','inactivo','jubilado','retirado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_egreso` date DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `anos_experiencia_publica` int NOT NULL DEFAULT '0',
  `meses_experiencia_publica` int NOT NULL DEFAULT '0',
  `anos_experiencia_privada` int NOT NULL DEFAULT '0',
  `meses_experiencia_privada` int NOT NULL DEFAULT '0',
  `anos_experiencia_independiente` int NOT NULL DEFAULT '0',
  `meses_experiencia_independiente` int NOT NULL DEFAULT '0',
  `inhabilitado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `empleados_cedula_unique` (`cedula`),
  KEY `empleados_cargo_id_foreign` (`cargo_id`),
  KEY `empleados_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  KEY `empleados_creado_por_foreign` (`creado_por`),
  CONSTRAINT `empleados_cargo_id_foreign` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `empleados_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empleados_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fuentes_financiamiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fuentes_financiamiento` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('ordinario','propio','credito_externo','donacion','otro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ordinario',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fuentes_financiamiento_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ingresos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingresos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ejercicio_fiscal_id` bigint unsigned DEFAULT NULL,
  `numero_recibo` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caja_id` bigint unsigned NOT NULL,
  `concepto_ingreso_id` bigint unsigned NOT NULL,
  `monto` decimal(18,2) NOT NULL,
  `forma_pago` enum('efectivo','transferencia','cheque','punto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `referencia_bancaria` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pagador_nombre` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pagador_rif` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` date NOT NULL,
  `estado` enum('registrado','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registrado',
  `motivo_anulacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ingresos_numero_recibo_unique` (`numero_recibo`),
  KEY `ingresos_caja_id_foreign` (`caja_id`),
  KEY `ingresos_concepto_ingreso_id_foreign` (`concepto_ingreso_id`),
  KEY `ingresos_creado_por_foreign` (`creado_por`),
  KEY `ingresos_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  CONSTRAINT `ingresos_caja_id_foreign` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ingresos_concepto_ingreso_id_foreign` FOREIGN KEY (`concepto_ingreso_id`) REFERENCES `conceptos_ingreso` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ingresos_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ingresos_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventario_movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario_movimientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ejercicio_fiscal_id` bigint unsigned DEFAULT NULL,
  `articulo_id` bigint unsigned NOT NULL,
  `almacen_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('entrada','salida','ajuste','traslado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'entrada',
  `origen_tipo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origen_id` bigint unsigned DEFAULT NULL,
  `cantidad` decimal(12,2) NOT NULL,
  `precio_unitario` decimal(18,2) NOT NULL DEFAULT '0.00',
  `stock_anterior` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stock_nuevo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `concepto` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventario_movimientos_articulo_id_foreign` (`articulo_id`),
  KEY `inventario_movimientos_almacen_id_foreign` (`almacen_id`),
  KEY `inventario_movimientos_creado_por_foreign` (`creado_por`),
  KEY `inventario_movimientos_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  CONSTRAINT `inventario_movimientos_almacen_id_foreign` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventario_movimientos_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventario_movimientos_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventario_movimientos_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `modificaciones_presupuestarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modificaciones_presupuestarias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `tipo` enum('traspaso','credito_adicional','reduccion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'traspaso',
  `concepto` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL,
  `credito_origen_id` bigint unsigned DEFAULT NULL,
  `credito_destino_id` bigint unsigned DEFAULT NULL,
  `fecha_modificacion` date NOT NULL,
  `estado` enum('borrador','aprobada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_anulacion` text COLLATE utf8mb4_unicode_ci,
  `fecha_aprobacion` date DEFAULT NULL,
  `aprobado_por` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `modificaciones_presupuestarias_numero_unique` (`numero`),
  KEY `modificaciones_presupuestarias_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  KEY `modificaciones_presupuestarias_credito_origen_id_foreign` (`credito_origen_id`),
  KEY `modificaciones_presupuestarias_credito_destino_id_foreign` (`credito_destino_id`),
  KEY `modificaciones_presupuestarias_aprobado_por_foreign` (`aprobado_por`),
  KEY `modificaciones_presupuestarias_created_by_foreign` (`created_by`),
  CONSTRAINT `modificaciones_presupuestarias_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`),
  CONSTRAINT `modificaciones_presupuestarias_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `modificaciones_presupuestarias_credito_destino_id_foreign` FOREIGN KEY (`credito_destino_id`) REFERENCES `creditos_presupuestarios` (`id`),
  CONSTRAINT `modificaciones_presupuestarias_credito_origen_id_foreign` FOREIGN KEY (`credito_origen_id`) REFERENCES `creditos_presupuestarios` (`id`),
  CONSTRAINT `modificaciones_presupuestarias_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `movimientos_bancarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_bancarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuenta_bancaria_id` bigint unsigned NOT NULL,
  `tipo` enum('debito','credito','transferencia_entrada','transferencia_salida','nota_debito','nota_credito') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'debito',
  `concepto` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL,
  `fecha_movimiento` date NOT NULL,
  `fecha_valor` date DEFAULT NULL,
  `referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banco_origen` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuenta_origen` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiario_nombre` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiario_rif` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origen` enum('pago','ingreso','manual','conciliacion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `origen_modelo_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origen_modelo_id` bigint unsigned DEFAULT NULL,
  `estado` enum('pendiente','conciliado','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_anulacion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ejercicio_fiscal_id` bigint unsigned DEFAULT NULL,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `movimientos_bancarios_numero_unique` (`numero`),
  KEY `movimientos_bancarios_origen_modelo_type_origen_modelo_id_index` (`origen_modelo_type`,`origen_modelo_id`),
  KEY `movimientos_bancarios_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  KEY `movimientos_bancarios_creado_por_foreign` (`creado_por`),
  KEY `movimientos_bancarios_cuenta_bancaria_id_fecha_movimiento_index` (`cuenta_bancaria_id`,`fecha_movimiento`),
  KEY `movimientos_bancarios_estado_index` (`estado`),
  CONSTRAINT `movimientos_bancarios_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimientos_bancarios_cuenta_bancaria_id_foreign` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movimientos_bancarios_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `movimientos_bien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_bien` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bien_id` bigint unsigned NOT NULL,
  `tipo` enum('incorporacion','traslado','reasignacion','baja','reparacion','devolucion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_origen_id` bigint unsigned DEFAULT NULL,
  `unidad_destino_id` bigint unsigned DEFAULT NULL,
  `motivo` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimientos_bien_bien_id_foreign` (`bien_id`),
  KEY `movimientos_bien_unidad_origen_id_foreign` (`unidad_origen_id`),
  KEY `movimientos_bien_unidad_destino_id_foreign` (`unidad_destino_id`),
  KEY `movimientos_bien_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `movimientos_bien_bien_id_foreign` FOREIGN KEY (`bien_id`) REFERENCES `bienes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movimientos_bien_unidad_destino_id_foreign` FOREIGN KEY (`unidad_destino_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimientos_bien_unidad_origen_id_foreign` FOREIGN KEY (`unidad_origen_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimientos_bien_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `movimientos_partidas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_partidas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `partida_presupuestaria_id` bigint unsigned NOT NULL,
  `partida_contrapartida_id` bigint unsigned DEFAULT NULL,
  `movimiento_relacionado_id` bigint unsigned DEFAULT NULL,
  `cuenta_bancaria_id` bigint unsigned DEFAULT NULL,
  `ejercicio_fiscal_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('asignacion','credito_adicional','modificacion_entrada','modificacion_salida','ejecucion','reintegro','nota_credito','nota_debito','compromiso','causacion','pago') COLLATE utf8mb4_unicode_ci NOT NULL,
  `concepto` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL,
  `fecha_movimiento` date NOT NULL,
  `referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `saldo_anterior` decimal(18,2) NOT NULL DEFAULT '0.00',
  `saldo_posterior` decimal(18,2) NOT NULL DEFAULT '0.00',
  `estado` enum('pendiente','confirmado','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `motivo_anulacion` text COLLATE utf8mb4_unicode_ci,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `movimientos_partidas_numero_unique` (`numero`),
  KEY `movimientos_partidas_partida_contrapartida_id_foreign` (`partida_contrapartida_id`),
  KEY `movimientos_partidas_cuenta_bancaria_id_foreign` (`cuenta_bancaria_id`),
  KEY `movimientos_partidas_creado_por_foreign` (`creado_por`),
  KEY `movimientos_partidas_movimiento_relacionado_id_foreign` (`movimiento_relacionado_id`),
  KEY `movimientos_partidas_partida_presupuestaria_id_index` (`partida_presupuestaria_id`),
  KEY `movimientos_partidas_tipo_index` (`tipo`),
  KEY `movimientos_partidas_fecha_movimiento_index` (`fecha_movimiento`),
  KEY `movimientos_partidas_estado_index` (`estado`),
  KEY `movimientos_ejercicio_estado_idx` (`ejercicio_fiscal_id`,`estado`),
  KEY `movimientos_partida_estado_idx` (`partida_presupuestaria_id`,`estado`),
  KEY `movimientos_fecha_tipo_idx` (`fecha_movimiento`,`tipo`),
  CONSTRAINT `movimientos_partidas_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimientos_partidas_cuenta_bancaria_id_foreign` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimientos_partidas_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimientos_partidas_movimiento_relacionado_id_foreign` FOREIGN KEY (`movimiento_relacionado_id`) REFERENCES `movimientos_partidas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimientos_partidas_partida_contrapartida_id_foreign` FOREIGN KEY (`partida_contrapartida_id`) REFERENCES `partidas_presupuestarias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimientos_partidas_partida_presupuestaria_id_foreign` FOREIGN KEY (`partida_presupuestaria_id`) REFERENCES `partidas_presupuestarias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nominas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nominas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `partida_presupuestaria_id` bigint unsigned DEFAULT NULL,
  `tipo_nomina` enum('ordinaria','vacacional','utilidades','bono','liquidacion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ordinaria',
  `periodo_inicio` date NOT NULL,
  `periodo_fin` date NOT NULL,
  `total_asignaciones` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_deducciones` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_neto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `estado` enum('borrador','calculada','aprobada','pagada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `aprobado_por` bigint unsigned DEFAULT NULL,
  `fecha_aprobacion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nominas_numero_unique` (`numero`),
  KEY `nominas_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  KEY `nominas_creado_por_foreign` (`creado_por`),
  KEY `nominas_aprobado_por_foreign` (`aprobado_por`),
  KEY `nominas_partida_presupuestaria_id_foreign` (`partida_presupuestaria_id`),
  CONSTRAINT `nominas_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nominas_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nominas_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `nominas_partida_presupuestaria_id_foreign` FOREIGN KEY (`partida_presupuestaria_id`) REFERENCES `partidas_presupuestarias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nominas_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nominas_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nomina_id` bigint unsigned NOT NULL,
  `empleado_id` bigint unsigned NOT NULL,
  `salario_base` decimal(18,2) NOT NULL,
  `total_asignaciones` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_deducciones` decimal(18,2) NOT NULL DEFAULT '0.00',
  `neto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `conceptos_aplicados` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nominas_detalle_nomina_id_foreign` (`nomina_id`),
  KEY `nominas_detalle_empleado_id_foreign` (`empleado_id`),
  CONSTRAINT `nominas_detalle_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `nominas_detalle_nomina_id_foreign` FOREIGN KEY (`nomina_id`) REFERENCES `nominas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ordenes_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_compra` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `solicitud_compra_id` bigint unsigned DEFAULT NULL,
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `partida_presupuestaria_id` bigint unsigned DEFAULT NULL,
  `beneficiario_id` bigint unsigned DEFAULT NULL,
  `proveedor_nombre` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proveedor_rif` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `concepto` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `subtotal` decimal(18,2) NOT NULL DEFAULT '0.00',
  `iva_porcentaje` decimal(5,2) NOT NULL DEFAULT '16.00',
  `iva_monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total` decimal(18,2) NOT NULL DEFAULT '0.00',
  `monto_retencion` decimal(18,2) NOT NULL DEFAULT '0.00',
  `monto_neto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `estado` enum('emitida','confirmada','en_transito','completada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'emitida',
  `modalidad` enum('compra_directa','concurso','licitacion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'compra_directa',
  `numero_contrato` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo_anulacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condiciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ordenes_compra_numero_unique` (`numero`),
  KEY `ordenes_compra_solicitud_compra_id_foreign` (`solicitud_compra_id`),
  KEY `ordenes_compra_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  KEY `ordenes_compra_beneficiario_id_foreign` (`beneficiario_id`),
  KEY `ordenes_compra_creado_por_foreign` (`creado_por`),
  KEY `ordenes_compra_partida_presupuestaria_id_foreign` (`partida_presupuestaria_id`),
  CONSTRAINT `ordenes_compra_beneficiario_id_foreign` FOREIGN KEY (`beneficiario_id`) REFERENCES `beneficiarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_compra_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_compra_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ordenes_compra_partida_presupuestaria_id_foreign` FOREIGN KEY (`partida_presupuestaria_id`) REFERENCES `partidas_presupuestarias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_compra_solicitud_compra_id_foreign` FOREIGN KEY (`solicitud_compra_id`) REFERENCES `solicitudes_compra` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ordenes_compra_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_compra_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `orden_compra_id` bigint unsigned NOT NULL,
  `articulo_id` bigint unsigned DEFAULT NULL,
  `descripcion` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_medida` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unidad',
  `cantidad` decimal(12,2) NOT NULL,
  `precio_unitario` decimal(18,2) NOT NULL,
  `subtotal` decimal(18,2) NOT NULL,
  `cantidad_recibida` decimal(12,2) NOT NULL DEFAULT '0.00',
  `orden` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ordenes_compra_detalle_orden_compra_id_foreign` (`orden_compra_id`),
  KEY `ordenes_compra_detalle_articulo_id_foreign` (`articulo_id`),
  CONSTRAINT `ordenes_compra_detalle_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_compra_detalle_orden_compra_id_foreign` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ordenes_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_pago` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `unidad_ejecutora_id` bigint unsigned NOT NULL,
  `beneficiario_id` bigint unsigned DEFAULT NULL,
  `causacion_id` bigint unsigned DEFAULT NULL,
  `concepto` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto_total` decimal(18,2) NOT NULL,
  `tipo_pago` enum('cheque','transferencia','efectivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'transferencia',
  `numero_referencia` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banco` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuenta_bancaria_num` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `estado` enum('borrador','revisada','aprobada','enviada','pagada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_anulacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_por` bigint unsigned DEFAULT NULL,
  `revisado_por` bigint unsigned DEFAULT NULL,
  `aprobado_por` bigint unsigned DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `fecha_aprobacion` timestamp NULL DEFAULT NULL,
  `fecha_envio` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ordenes_pago_numero_unique` (`numero`),
  KEY `ordenes_pago_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  KEY `ordenes_pago_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  KEY `ordenes_pago_beneficiario_id_foreign` (`beneficiario_id`),
  KEY `ordenes_pago_causacion_id_foreign` (`causacion_id`),
  KEY `ordenes_pago_creado_por_foreign` (`creado_por`),
  KEY `ordenes_pago_revisado_por_foreign` (`revisado_por`),
  KEY `ordenes_pago_aprobado_por_foreign` (`aprobado_por`),
  CONSTRAINT `ordenes_pago_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_pago_beneficiario_id_foreign` FOREIGN KEY (`beneficiario_id`) REFERENCES `beneficiarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_pago_causacion_id_foreign` FOREIGN KEY (`causacion_id`) REFERENCES `causaciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_pago_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_pago_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ordenes_pago_revisado_por_foreign` FOREIGN KEY (`revisado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_pago_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ordenes_pago_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_pago_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `orden_pago_id` bigint unsigned NOT NULL,
  `descripcion` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL,
  `orden` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ordenes_pago_detalle_orden_pago_id_foreign` (`orden_pago_id`),
  CONSTRAINT `ordenes_pago_detalle_orden_pago_id_foreign` FOREIGN KEY (`orden_pago_id`) REFERENCES `ordenes_pago` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `causacion_id` bigint unsigned NOT NULL,
  `orden_pago_id` bigint unsigned DEFAULT NULL,
  `generado_automatico` tinyint(1) NOT NULL DEFAULT '0',
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `unidad_ejecutora_id` bigint unsigned NOT NULL,
  `beneficiario` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rif_beneficiario` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pago` enum('cheque','transferencia','efectivo','otro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'transferencia',
  `numero_referencia` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'N° cheque / referencia bancaria',
  `banco` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuenta_bancaria` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto_pagado` decimal(18,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `concepto` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','procesado','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_anulacion` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pagos_numero_unique` (`numero`),
  KEY `pagos_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  KEY `pagos_created_by_foreign` (`created_by`),
  KEY `pagos_orden_pago_id_foreign` (`orden_pago_id`),
  KEY `pagos_ejercicio_estado_idx` (`ejercicio_fiscal_id`,`estado`),
  KEY `pagos_causacion_estado_idx` (`causacion_id`,`estado`),
  KEY `pagos_estado_idx` (`estado`),
  CONSTRAINT `pagos_causacion_id_foreign` FOREIGN KEY (`causacion_id`) REFERENCES `causaciones` (`id`),
  CONSTRAINT `pagos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `pagos_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`),
  CONSTRAINT `pagos_orden_pago_id_foreign` FOREIGN KEY (`orden_pago_id`) REFERENCES `ordenes_pago` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pagos_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `partidas_presupuestarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `partidas_presupuestarias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `generica` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `especifica` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subespecifica` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('gasto','ingreso','otros') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuenta_bancaria_id` bigint unsigned DEFAULT NULL,
  `saldo_actual` decimal(18,2) NOT NULL DEFAULT '0.00',
  `monto_aprobado` decimal(18,2) NOT NULL DEFAULT '0.00',
  `monto_vigente` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT 'Aprobado +- creditos adicionales y modificaciones formales',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partidas_presupuestarias_codigo_unique` (`codigo`),
  KEY `partidas_presupuestarias_generica_index` (`generica`),
  KEY `partidas_presupuestarias_especifica_index` (`especifica`),
  KEY `partidas_presupuestarias_cuenta_bancaria_id_foreign` (`cuenta_bancaria_id`),
  CONSTRAINT `partidas_presupuestarias_cuenta_bancaria_id_foreign` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `periodos_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodos_contables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `anio` smallint NOT NULL,
  `mes` tinyint NOT NULL,
  `nombre` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('abierto','cerrado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierto',
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `cerrado_por` bigint unsigned DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periodos_contables_ejercicio_fiscal_id_anio_mes_unique` (`ejercicio_fiscal_id`,`anio`,`mes`),
  KEY `periodos_contables_cerrado_por_foreign` (`cerrado_por`),
  KEY `periodos_contables_creado_por_foreign` (`creado_por`),
  CONSTRAINT `periodos_contables_cerrado_por_foreign` FOREIGN KEY (`cerrado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `periodos_contables_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `periodos_contables_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `proyectos_sia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proyectos_sia` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `unidad_ejecutora_id` bigint unsigned NOT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `objetivo` text COLLATE utf8mb4_unicode_ci,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('formulacion','activo','suspendido','terminado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'formulacion',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proyectos_sia_ejercicio_fiscal_id_codigo_unique` (`ejercicio_fiscal_id`,`codigo`),
  KEY `proyectos_sia_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  CONSTRAINT `proyectos_sia_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `proyectos_sia_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `recepciones_bienes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recepciones_bienes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ejercicio_fiscal_id` bigint unsigned DEFAULT NULL,
  `numero` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden_compra_id` bigint unsigned NOT NULL,
  `fecha_recepcion` date NOT NULL,
  `recibido_por` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entregado_por` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_guia` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_factura` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_recibido` decimal(18,2) NOT NULL DEFAULT '0.00',
  `estado` enum('conforme','no_conforme','parcial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'conforme',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recepciones_bienes_numero_unique` (`numero`),
  KEY `recepciones_bienes_orden_compra_id_foreign` (`orden_compra_id`),
  KEY `recepciones_bienes_creado_por_foreign` (`creado_por`),
  KEY `recepciones_bienes_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  CONSTRAINT `recepciones_bienes_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recepciones_bienes_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recepciones_bienes_orden_compra_id_foreign` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `recepciones_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recepciones_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recepcion_id` bigint unsigned NOT NULL,
  `orden_detalle_id` bigint unsigned NOT NULL,
  `articulo_id` bigint unsigned DEFAULT NULL,
  `cantidad_recibida` decimal(12,2) NOT NULL,
  `precio_unitario` decimal(18,2) NOT NULL DEFAULT '0.00',
  `condicion` enum('bueno','malo','incompleto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bueno',
  `observacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recepciones_detalle_recepcion_id_foreign` (`recepcion_id`),
  KEY `recepciones_detalle_orden_detalle_id_foreign` (`orden_detalle_id`),
  KEY `recepciones_detalle_articulo_id_foreign` (`articulo_id`),
  CONSTRAINT `recepciones_detalle_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recepciones_detalle_orden_detalle_id_foreign` FOREIGN KEY (`orden_detalle_id`) REFERENCES `ordenes_compra_detalle` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `recepciones_detalle_recepcion_id_foreign` FOREIGN KEY (`recepcion_id`) REFERENCES `recepciones_bienes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `retenciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retenciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('porcentaje','monto_fijo','porcentaje_iva') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'porcentaje',
  `porcentaje` decimal(7,4) DEFAULT NULL COMMENT 'Usado cuando tipo=porcentaje',
  `alicuota_iva` decimal(5,2) DEFAULT NULL COMMENT 'Alícuota IVA incluida en la factura. Sólo para tipo porcentaje_iva',
  `monto_fijo` decimal(14,2) DEFAULT NULL COMMENT 'Usado cuando tipo=monto_fijo',
  `aplica_a` json DEFAULT NULL COMMENT 'Módulos donde aplica esta retención',
  `base_calculo` enum('monto_bruto','monto_neto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monto_bruto',
  `obligatoria` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Si true, se preselecciona automáticamente en los formularios',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `retenciones_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `retenciones_aplicadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retenciones_aplicadas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `retencionable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `retencionable_id` bigint unsigned NOT NULL,
  `retencion_id` bigint unsigned NOT NULL,
  `monto_base` decimal(14,2) NOT NULL COMMENT 'Monto sobre el que se calculó la retención',
  `monto_retenido` decimal(14,2) NOT NULL COMMENT 'Monto final descontado',
  `porcentaje_aplicado` decimal(7,4) DEFAULT NULL COMMENT 'Porcentaje vigente al momento de aplicar',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_retencion_por_registro` (`retencionable_type`,`retencionable_id`,`retencion_id`),
  KEY `retenciones_aplicadas_retencionable_type_retencionable_id_index` (`retencionable_type`,`retencionable_id`),
  KEY `retenciones_aplicadas_retencion_id_foreign` (`retencion_id`),
  KEY `retenciones_morph_idx` (`retencionable_type`,`retencionable_id`),
  CONSTRAINT `retenciones_aplicadas_retencion_id_foreign` FOREIGN KEY (`retencion_id`) REFERENCES `retenciones` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT 'fa-user-shield',
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#4f8ef7',
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `solicitudes_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_compra` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ejercicio_fiscal_id` bigint unsigned NOT NULL,
  `unidad_ejecutora_id` bigint unsigned DEFAULT NULL,
  `motivo` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('bienes','servicios','mixta') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bienes',
  `prioridad` enum('baja','media','alta','urgente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'media',
  `fecha_requerida` date DEFAULT NULL,
  `estado` enum('borrador','enviada','revisada','aprobada','rechazada','procesada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `motivo_rechazo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `solicitado_por` bigint unsigned DEFAULT NULL,
  `aprobado_por` bigint unsigned DEFAULT NULL,
  `fecha_aprobacion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `solicitudes_compra_numero_unique` (`numero`),
  KEY `solicitudes_compra_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  KEY `solicitudes_compra_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  KEY `solicitudes_compra_solicitado_por_foreign` (`solicitado_por`),
  KEY `solicitudes_compra_aprobado_por_foreign` (`aprobado_por`),
  CONSTRAINT `solicitudes_compra_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `solicitudes_compra_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `solicitudes_compra_solicitado_por_foreign` FOREIGN KEY (`solicitado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `solicitudes_compra_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `solicitudes_compra_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_compra_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `solicitud_compra_id` bigint unsigned NOT NULL,
  `articulo_id` bigint unsigned DEFAULT NULL,
  `descripcion` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_medida` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unidad',
  `cantidad` decimal(12,2) NOT NULL,
  `precio_estimado` decimal(18,2) NOT NULL DEFAULT '0.00',
  `especificaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitudes_compra_detalle_solicitud_compra_id_foreign` (`solicitud_compra_id`),
  KEY `solicitudes_compra_detalle_articulo_id_foreign` (`articulo_id`),
  CONSTRAINT `solicitudes_compra_detalle_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `solicitudes_compra_detalle_solicitud_compra_id_foreign` FOREIGN KEY (`solicitud_compra_id`) REFERENCES `solicitudes_compra` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `solicitudes_despacho`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_despacho` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ejercicio_fiscal_id` bigint unsigned DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_ejecutora_id` bigint unsigned NOT NULL,
  `motivo` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prioridad` enum('baja','media','alta','urgente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'media',
  `fecha_requerida` date DEFAULT NULL,
  `estado` enum('borrador','enviada','aprobada','entregada','rechazada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_rechazo` text COLLATE utf8mb4_unicode_ci,
  `recibido_por` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre de la persona que recibió los artículos en la oficina',
  `fecha_entrega` datetime DEFAULT NULL COMMENT 'Fecha y hora en que se realizó la entrega física',
  `observaciones_entrega` text COLLATE utf8mb4_unicode_ci COMMENT 'Notas del almacenista al momento de la entrega',
  `solicitado_por` bigint unsigned NOT NULL,
  `aprobado_por` bigint unsigned DEFAULT NULL,
  `fecha_aprobacion` timestamp NULL DEFAULT NULL,
  `fecha_despacho` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `solicitudes_despacho_numero_unique` (`numero`),
  KEY `solicitudes_despacho_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  KEY `solicitudes_despacho_solicitado_por_foreign` (`solicitado_por`),
  KEY `solicitudes_despacho_aprobado_por_foreign` (`aprobado_por`),
  KEY `solicitudes_despacho_ejercicio_fiscal_id_foreign` (`ejercicio_fiscal_id`),
  CONSTRAINT `solicitudes_despacho_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`),
  CONSTRAINT `solicitudes_despacho_ejercicio_fiscal_id_foreign` FOREIGN KEY (`ejercicio_fiscal_id`) REFERENCES `ejercicios_fiscales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `solicitudes_despacho_solicitado_por_foreign` FOREIGN KEY (`solicitado_por`) REFERENCES `users` (`id`),
  CONSTRAINT `solicitudes_despacho_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `solicitudes_despacho_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_despacho_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `solicitud_despacho_id` bigint unsigned NOT NULL,
  `articulo_id` bigint unsigned NOT NULL,
  `cantidad_solicitada` decimal(12,2) NOT NULL,
  `cantidad_despachada` decimal(12,2) DEFAULT NULL,
  `observacion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitudes_despacho_detalle_solicitud_despacho_id_foreign` (`solicitud_despacho_id`),
  KEY `solicitudes_despacho_detalle_articulo_id_foreign` (`articulo_id`),
  CONSTRAINT `solicitudes_despacho_detalle_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`),
  CONSTRAINT `solicitudes_despacho_detalle_solicitud_despacho_id_foreign` FOREIGN KEY (`solicitud_despacho_id`) REFERENCES `solicitudes_despacho` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unidades_ejecutoras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidades_ejecutoras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unidades_ejecutoras_codigo_unique` (`codigo`),
  KEY `unidades_ejecutoras_parent_id_foreign` (`parent_id`),
  CONSTRAINT `unidades_ejecutoras_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unidad_ejecutora_id` bigint unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_cedula_unique` (`cedula`),
  KEY `users_unidad_ejecutora_id_foreign` (`unidad_ejecutora_id`),
  CONSTRAINT `users_unidad_ejecutora_id_foreign` FOREIGN KEY (`unidad_ejecutora_id`) REFERENCES `unidades_ejecutoras` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_03_29_140743_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_03_29_140807_create_audits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_03_29_140854_create_unidades_ejecutoras_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_03_29_140920_create_ejercicios_fiscales_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_03_29_140937_create_fuentes_financiamiento_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_03_29_141149_create_partidas_presupuestarias_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_03_29_141157_create_proyectos_sia_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_03_29_141230_create_actividades_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_03_29_141239_create_creditos_presupuestarios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_03_29_141300_modify_users_table_add_sia_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_03_30_003855_create_causaciones_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_03_30_010000_create_compromisos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_03_30_010001_create_pagos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_03_30_010002_create_modificaciones_presupuestarias_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_03_30_100000_create_beneficiarios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_03_30_100001_create_cuentas_bancarias_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_03_30_100002_create_movimientos_bancarios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_03_30_100003_create_conciliaciones_bancarias_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_03_30_150001_create_cuentas_contables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_03_30_150002_create_periodos_contables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_03_30_150003_create_asientos_contables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_03_30_150004_create_asientos_detalle_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_03_30_160001_create_almacenes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_03_30_160002_create_articulos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_03_30_160003_create_solicitudes_compra_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_03_30_160004_create_ordenes_compra_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_03_30_160005_create_recepciones_inventario_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_04_01_000001_create_ordenes_pago_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_04_01_000002_create_bienes_nacionales_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_04_01_000003_create_nomina_personal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_04_01_000004_create_control_ingresos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_04_07_000001_add_cuenta_bancaria_to_partidas_presupuestarias',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_04_07_000002_add_tipo_otros_to_partidas_presupuestarias',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_04_07_000003_create_movimientos_partidas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_04_07_000004_add_saldo_actual_to_partidas_presupuestarias',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_04_07_000005_simplify_partidas_presupuestarias',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_04_07_000006_expand_codigo_in_partidas_presupuestarias',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_04_08_000001_integrate_ordenes_pago_with_presupuesto',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_04_08_194155_make_credito_presupuestario_nullable_in_compromisos_causaciones',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_04_09_035209_expand_tipo_enum_in_movimientos_partidas',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_04_09_040244_add_compromiso_id_to_causaciones_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_04_09_124320_add_beneficiario_id_to_compromisos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_04_09_135616_add_partida_to_ordenes_compra_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_04_09_192511_add_partida_to_nominas_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_04_13_151111_create_solicitudes_despacho_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_04_13_173727_make_unidad_nullable_in_solicitudes_compra',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_04_13_182809_add_ejercicio_fiscal_to_transaccional_tables',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_04_15_000001_create_retenciones_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_04_15_000002_create_retenciones_aplicadas_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_04_16_122529_add_documento_fields_to_compromisos_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_04_16_140357_add_alicuota_iva_to_retenciones_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_04_16_142133_add_iva_fields_to_causaciones_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_04_16_143242_add_iva_fields_to_compromisos_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_04_16_143258_add_iva_to_compromisos',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_04_20_130626_add_entrega_fields_to_solicitudes_despacho',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_04_20_131830_update_estado_enum_in_solicitudes_despacho',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_04_20_132402_remove_anulada_from_solicitudes_despacho_estado',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_04_20_143740_add_icono_color_to_roles',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_04_20_145841_seed_missing_permissions',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_04_21_130657_add_monto_aprobado_to_partidas_presupuestarias',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_04_21_174735_add_monto_vigente_to_partidas_presupuestarias',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_04_21_181203_add_datos_civiles_to_empleados',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_04_21_181204_create_empleado_familiares_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_04_21_181206_create_empleado_historial_cargos_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_04_22_000001_add_salud_to_empleados_create_formaciones_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_04_28_000001_add_retenciones_to_ordenes_compra_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_04_29_000001_add_performance_indexes',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_05_04_173254_update_empleados_table_for_cv_format',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_05_05_134319_add_carnet_militar_foto_to_empleados_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_05_18_124425_create_empleado_bonificacions_table',24);
