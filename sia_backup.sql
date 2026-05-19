-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: sia
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `actividades`
--

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

--
-- Dumping data for table `actividades`
--

LOCK TABLES `actividades` WRITE;
/*!40000 ALTER TABLE `actividades` DISABLE KEYS */;
/*!40000 ALTER TABLE `actividades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `almacenes`
--

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

--
-- Dumping data for table `almacenes`
--

LOCK TABLES `almacenes` WRITE;
/*!40000 ALTER TABLE `almacenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `almacenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `arqueos_caja`
--

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

--
-- Dumping data for table `arqueos_caja`
--

LOCK TABLES `arqueos_caja` WRITE;
/*!40000 ALTER TABLE `arqueos_caja` DISABLE KEYS */;
/*!40000 ALTER TABLE `arqueos_caja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articulos`
--

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

--
-- Dumping data for table `articulos`
--

LOCK TABLES `articulos` WRITE;
/*!40000 ALTER TABLE `articulos` DISABLE KEYS */;
/*!40000 ALTER TABLE `articulos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asientos_contables`
--

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

--
-- Dumping data for table `asientos_contables`
--

LOCK TABLES `asientos_contables` WRITE;
/*!40000 ALTER TABLE `asientos_contables` DISABLE KEYS */;
/*!40000 ALTER TABLE `asientos_contables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asientos_detalle`
--

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

--
-- Dumping data for table `asientos_detalle`
--

LOCK TABLES `asientos_detalle` WRITE;
/*!40000 ALTER TABLE `asientos_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `asientos_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audits`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audits`
--

LOCK TABLES `audits` WRITE;
/*!40000 ALTER TABLE `audits` DISABLE KEYS */;
INSERT INTO `audits` VALUES (1,'App\\Models\\User',1,'deleted','App\\Models\\CuentaBancaria',1,'{\"id\":1,\"codigo\":\"CTA-0001\",\"nombre\":\"banco pricipal1\",\"banco\":\"Banco de Venezuela\",\"numero_cuenta\":\"0102-0000-00-000000001\",\"tipo\":\"corriente\",\"moneda\":\"VES\",\"saldo_inicial\":\"0.00\",\"saldo_actual\":\"999601580.89\",\"fecha_apertura\":\"2020-01-01\",\"estado\":\"activa\",\"firmante_1\":\"firmate1\",\"firmante_2\":null,\"ejercicio_fiscal_id\":1,\"observaciones\":null,\"creado_por\":1}','[]','http://localhost/tesoreria/cuentas/1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0',NULL,'2026-05-18 23:33:55','2026-05-18 23:33:55');
/*!40000 ALTER TABLE `audits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beneficiarios`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beneficiarios`
--

LOCK TABLES `beneficiarios` WRITE;
/*!40000 ALTER TABLE `beneficiarios` DISABLE KEYS */;
INSERT INTO `beneficiarios` VALUES (1,'J-00052030-5','razon social','nombre comercial','proveedor','0414-0000000','correo@123.com',NULL,'Banco de Venezuela','0102-0005-00-000501003','corriente',1,NULL,'2026-05-18 22:47:13','2026-05-18 22:47:13',NULL);
/*!40000 ALTER TABLE `beneficiarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bienes`
--

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

--
-- Dumping data for table `bienes`
--

LOCK TABLES `bienes` WRITE;
/*!40000 ALTER TABLE `bienes` DISABLE KEYS */;
/*!40000 ALTER TABLE `bienes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

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

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

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

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cajas`
--

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

--
-- Dumping data for table `cajas`
--

LOCK TABLES `cajas` WRITE;
/*!40000 ALTER TABLE `cajas` DISABLE KEYS */;
/*!40000 ALTER TABLE `cajas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargos`
--

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

--
-- Dumping data for table `cargos`
--

LOCK TABLES `cargos` WRITE;
/*!40000 ALTER TABLE `cargos` DISABLE KEYS */;
/*!40000 ALTER TABLE `cargos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias_bien`
--

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

--
-- Dumping data for table `categorias_bien`
--

LOCK TABLES `categorias_bien` WRITE;
/*!40000 ALTER TABLE `categorias_bien` DISABLE KEYS */;
/*!40000 ALTER TABLE `categorias_bien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `causaciones`
--

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

--
-- Dumping data for table `causaciones`
--

LOCK TABLES `causaciones` WRITE;
/*!40000 ALTER TABLE `causaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `causaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compromisos`
--

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

--
-- Dumping data for table `compromisos`
--

LOCK TABLES `compromisos` WRITE;
/*!40000 ALTER TABLE `compromisos` DISABLE KEYS */;
/*!40000 ALTER TABLE `compromisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conceptos_ingreso`
--

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

--
-- Dumping data for table `conceptos_ingreso`
--

LOCK TABLES `conceptos_ingreso` WRITE;
/*!40000 ALTER TABLE `conceptos_ingreso` DISABLE KEYS */;
/*!40000 ALTER TABLE `conceptos_ingreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conceptos_nomina`
--

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

--
-- Dumping data for table `conceptos_nomina`
--

LOCK TABLES `conceptos_nomina` WRITE;
/*!40000 ALTER TABLE `conceptos_nomina` DISABLE KEYS */;
/*!40000 ALTER TABLE `conceptos_nomina` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conciliacion_detalles`
--

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

--
-- Dumping data for table `conciliacion_detalles`
--

LOCK TABLES `conciliacion_detalles` WRITE;
/*!40000 ALTER TABLE `conciliacion_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `conciliacion_detalles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conciliaciones_bancarias`
--

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

--
-- Dumping data for table `conciliaciones_bancarias`
--

LOCK TABLES `conciliaciones_bancarias` WRITE;
/*!40000 ALTER TABLE `conciliaciones_bancarias` DISABLE KEYS */;
/*!40000 ALTER TABLE `conciliaciones_bancarias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `creditos_presupuestarios`
--

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

--
-- Dumping data for table `creditos_presupuestarios`
--

LOCK TABLES `creditos_presupuestarios` WRITE;
/*!40000 ALTER TABLE `creditos_presupuestarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `creditos_presupuestarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas_bancarias`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_bancarias`
--

LOCK TABLES `cuentas_bancarias` WRITE;
/*!40000 ALTER TABLE `cuentas_bancarias` DISABLE KEYS */;
INSERT INTO `cuentas_bancarias` VALUES (1,'CTA-0001','banco pricipal1','Banco de Venezuela','0102-0000-00-000000001','corriente','VES',0.00,999601580.89,'2020-01-01','activa','firmate1',NULL,1,NULL,1,'2026-05-18 22:37:34','2026-05-18 23:33:55','2026-05-18 23:33:55');
/*!40000 ALTER TABLE `cuentas_bancarias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas_contables`
--

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

--
-- Dumping data for table `cuentas_contables`
--

LOCK TABLES `cuentas_contables` WRITE;
/*!40000 ALTER TABLE `cuentas_contables` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuentas_contables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ejercicios_fiscales`
--

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

--
-- Dumping data for table `ejercicios_fiscales`
--

LOCK TABLES `ejercicios_fiscales` WRITE;
/*!40000 ALTER TABLE `ejercicios_fiscales` DISABLE KEYS */;
/*!40000 ALTER TABLE `ejercicios_fiscales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleado_bonificaciones`
--

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

--
-- Dumping data for table `empleado_bonificaciones`
--

LOCK TABLES `empleado_bonificaciones` WRITE;
/*!40000 ALTER TABLE `empleado_bonificaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `empleado_bonificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleado_familiares`
--

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

--
-- Dumping data for table `empleado_familiares`
--

LOCK TABLES `empleado_familiares` WRITE;
/*!40000 ALTER TABLE `empleado_familiares` DISABLE KEYS */;
/*!40000 ALTER TABLE `empleado_familiares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleado_formaciones`
--

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

--
-- Dumping data for table `empleado_formaciones`
--

LOCK TABLES `empleado_formaciones` WRITE;
/*!40000 ALTER TABLE `empleado_formaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `empleado_formaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleado_historial_cargos`
--

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

--
-- Dumping data for table `empleado_historial_cargos`
--

LOCK TABLES `empleado_historial_cargos` WRITE;
/*!40000 ALTER TABLE `empleado_historial_cargos` DISABLE KEYS */;
/*!40000 ALTER TABLE `empleado_historial_cargos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleados`
--

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

--
-- Dumping data for table `empleados`
--

LOCK TABLES `empleados` WRITE;
/*!40000 ALTER TABLE `empleados` DISABLE KEYS */;
/*!40000 ALTER TABLE `empleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

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

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fuentes_financiamiento`
--

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

--
-- Dumping data for table `fuentes_financiamiento`
--

LOCK TABLES `fuentes_financiamiento` WRITE;
/*!40000 ALTER TABLE `fuentes_financiamiento` DISABLE KEYS */;
/*!40000 ALTER TABLE `fuentes_financiamiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingresos`
--

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

--
-- Dumping data for table `ingresos`
--

LOCK TABLES `ingresos` WRITE;
/*!40000 ALTER TABLE `ingresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `ingresos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario_movimientos`
--

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

--
-- Dumping data for table `inventario_movimientos`
--

LOCK TABLES `inventario_movimientos` WRITE;
/*!40000 ALTER TABLE `inventario_movimientos` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventario_movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

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

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

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

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_03_29_140743_create_permission_tables',1),(5,'2026_03_29_140807_create_audits_table',1),(6,'2026_03_29_140854_create_unidades_ejecutoras_table',1),(7,'2026_03_29_140920_create_ejercicios_fiscales_table',1),(8,'2026_03_29_140937_create_fuentes_financiamiento_table',1),(9,'2026_03_29_141149_create_partidas_presupuestarias_table',1),(10,'2026_03_29_141157_create_proyectos_sia_table',1),(11,'2026_03_29_141230_create_actividades_table',1),(12,'2026_03_29_141239_create_creditos_presupuestarios_table',1),(13,'2026_03_29_141300_modify_users_table_add_sia_fields',1),(14,'2026_03_30_003855_create_causaciones_table',1),(15,'2026_03_30_010000_create_compromisos_table',1),(16,'2026_03_30_010001_create_pagos_table',1),(17,'2026_03_30_010002_create_modificaciones_presupuestarias_table',1),(18,'2026_03_30_100000_create_beneficiarios_table',1),(19,'2026_03_30_100001_create_cuentas_bancarias_table',1),(20,'2026_03_30_100002_create_movimientos_bancarios_table',1),(21,'2026_03_30_100003_create_conciliaciones_bancarias_table',1),(22,'2026_03_30_150001_create_cuentas_contables_table',1),(23,'2026_03_30_150002_create_periodos_contables_table',1),(24,'2026_03_30_150003_create_asientos_contables_table',1),(25,'2026_03_30_150004_create_asientos_detalle_table',1),(26,'2026_03_30_160001_create_almacenes_table',1),(27,'2026_03_30_160002_create_articulos_table',1),(28,'2026_03_30_160003_create_solicitudes_compra_table',1),(29,'2026_03_30_160004_create_ordenes_compra_table',1),(30,'2026_03_30_160005_create_recepciones_inventario_table',1),(31,'2026_04_01_000001_create_ordenes_pago_table',1),(32,'2026_04_01_000002_create_bienes_nacionales_table',1),(33,'2026_04_01_000003_create_nomina_personal_table',1),(34,'2026_04_01_000004_create_control_ingresos_table',1),(35,'2026_04_07_000001_add_cuenta_bancaria_to_partidas_presupuestarias',1),(36,'2026_04_07_000002_add_tipo_otros_to_partidas_presupuestarias',1),(37,'2026_04_07_000003_create_movimientos_partidas_table',1),(38,'2026_04_07_000004_add_saldo_actual_to_partidas_presupuestarias',1),(39,'2026_04_07_000005_simplify_partidas_presupuestarias',1),(40,'2026_04_07_000006_expand_codigo_in_partidas_presupuestarias',1),(41,'2026_04_08_000001_integrate_ordenes_pago_with_presupuesto',1),(42,'2026_04_08_194155_make_credito_presupuestario_nullable_in_compromisos_causaciones',1),(43,'2026_04_09_035209_expand_tipo_enum_in_movimientos_partidas',1),(44,'2026_04_09_040244_add_compromiso_id_to_causaciones_table',1),(45,'2026_04_09_124320_add_beneficiario_id_to_compromisos_table',1),(46,'2026_04_09_135616_add_partida_to_ordenes_compra_table',2),(47,'2026_04_09_192511_add_partida_to_nominas_table',3),(48,'2026_04_13_151111_create_solicitudes_despacho_tables',4),(49,'2026_04_13_173727_make_unidad_nullable_in_solicitudes_compra',5),(50,'2026_04_13_182809_add_ejercicio_fiscal_to_transaccional_tables',6),(51,'2026_04_15_000001_create_retenciones_table',7),(52,'2026_04_15_000002_create_retenciones_aplicadas_table',7),(53,'2026_04_16_122529_add_documento_fields_to_compromisos_table',8),(54,'2026_04_16_140357_add_alicuota_iva_to_retenciones_table',9),(55,'2026_04_16_142133_add_iva_fields_to_causaciones_table',10),(56,'2026_04_16_143242_add_iva_fields_to_compromisos_table',11),(57,'2026_04_16_143258_add_iva_to_compromisos',11),(59,'2026_04_20_130626_add_entrega_fields_to_solicitudes_despacho',12),(60,'2026_04_20_131830_update_estado_enum_in_solicitudes_despacho',12),(61,'2026_04_20_132402_remove_anulada_from_solicitudes_despacho_estado',13),(62,'2026_04_20_143740_add_icono_color_to_roles',14),(63,'2026_04_20_145841_seed_missing_permissions',15),(64,'2026_04_21_130657_add_monto_aprobado_to_partidas_presupuestarias',16),(65,'2026_04_21_174735_add_monto_vigente_to_partidas_presupuestarias',17),(66,'2026_04_21_181203_add_datos_civiles_to_empleados',18),(67,'2026_04_21_181204_create_empleado_familiares_table',18),(68,'2026_04_21_181206_create_empleado_historial_cargos_table',18),(69,'2026_04_22_000001_add_salud_to_empleados_create_formaciones_table',19),(70,'2026_04_28_000001_add_retenciones_to_ordenes_compra_table',20),(71,'2026_04_29_000001_add_performance_indexes',21),(72,'2026_05_04_173254_update_empleados_table_for_cv_format',22),(73,'2026_05_05_134319_add_carnet_militar_foto_to_empleados_table',23),(74,'2026_05_18_124425_create_empleado_bonificacions_table',24),(75,'2026_05_18_143708_migracion-01',25);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

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

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
INSERT INTO `model_has_permissions` VALUES (1,'App\\Models\\User',3),(2,'App\\Models\\User',4),(3,'App\\Models\\User',5),(4,'App\\Models\\User',6),(5,'App\\Models\\User',7),(6,'App\\Models\\User',8),(7,'App\\Models\\User',9),(8,'App\\Models\\User',10),(9,'App\\Models\\User',11),(10,'App\\Models\\User',12),(11,'App\\Models\\User',13),(12,'App\\Models\\User',14),(13,'App\\Models\\User',15),(14,'App\\Models\\User',16),(15,'App\\Models\\User',17),(16,'App\\Models\\User',18),(17,'App\\Models\\User',19),(18,'App\\Models\\User',20),(19,'App\\Models\\User',21),(20,'App\\Models\\User',22),(21,'App\\Models\\User',23),(22,'App\\Models\\User',24),(23,'App\\Models\\User',25),(24,'App\\Models\\User',26),(25,'App\\Models\\User',27),(26,'App\\Models\\User',28),(27,'App\\Models\\User',29),(28,'App\\Models\\User',30),(29,'App\\Models\\User',31),(30,'App\\Models\\User',32),(31,'App\\Models\\User',33),(32,'App\\Models\\User',34),(33,'App\\Models\\User',35),(34,'App\\Models\\User',36),(35,'App\\Models\\User',37),(36,'App\\Models\\User',38),(37,'App\\Models\\User',39),(38,'App\\Models\\User',40),(39,'App\\Models\\User',41),(40,'App\\Models\\User',42),(41,'App\\Models\\User',43),(42,'App\\Models\\User',44),(43,'App\\Models\\User',45),(44,'App\\Models\\User',46),(45,'App\\Models\\User',47),(46,'App\\Models\\User',48),(47,'App\\Models\\User',49),(48,'App\\Models\\User',50),(49,'App\\Models\\User',51),(50,'App\\Models\\User',52),(51,'App\\Models\\User',53),(52,'App\\Models\\User',54),(53,'App\\Models\\User',55),(54,'App\\Models\\User',56),(55,'App\\Models\\User',57),(56,'App\\Models\\User',58),(57,'App\\Models\\User',59),(58,'App\\Models\\User',60),(59,'App\\Models\\User',61),(60,'App\\Models\\User',62),(61,'App\\Models\\User',63),(62,'App\\Models\\User',64),(63,'App\\Models\\User',65),(64,'App\\Models\\User',66),(65,'App\\Models\\User',67),(66,'App\\Models\\User',68),(67,'App\\Models\\User',69),(68,'App\\Models\\User',70),(69,'App\\Models\\User',71),(70,'App\\Models\\User',72),(71,'App\\Models\\User',73),(72,'App\\Models\\User',74),(73,'App\\Models\\User',75),(74,'App\\Models\\User',76),(75,'App\\Models\\User',77),(76,'App\\Models\\User',78),(77,'App\\Models\\User',79),(78,'App\\Models\\User',80),(79,'App\\Models\\User',81),(80,'App\\Models\\User',82),(81,'App\\Models\\User',83),(82,'App\\Models\\User',84),(83,'App\\Models\\User',85),(84,'App\\Models\\User',86),(85,'App\\Models\\User',87),(86,'App\\Models\\User',88),(87,'App\\Models\\User',89),(88,'App\\Models\\User',90),(89,'App\\Models\\User',91),(90,'App\\Models\\User',92),(91,'App\\Models\\User',93),(92,'App\\Models\\User',94),(93,'App\\Models\\User',95),(94,'App\\Models\\User',96),(95,'App\\Models\\User',97),(96,'App\\Models\\User',98),(97,'App\\Models\\User',99),(98,'App\\Models\\User',100),(99,'App\\Models\\User',101),(100,'App\\Models\\User',102),(101,'App\\Models\\User',103),(102,'App\\Models\\User',104),(103,'App\\Models\\User',105),(104,'App\\Models\\User',106),(105,'App\\Models\\User',107),(106,'App\\Models\\User',108),(107,'App\\Models\\User',109),(108,'App\\Models\\User',110),(109,'App\\Models\\User',111),(110,'App\\Models\\User',112),(111,'App\\Models\\User',113),(112,'App\\Models\\User',114),(113,'App\\Models\\User',115),(114,'App\\Models\\User',116),(115,'App\\Models\\User',117),(116,'App\\Models\\User',118),(117,'App\\Models\\User',119);
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

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

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(1,'App\\Models\\User',2),(1,'App\\Models\\User',120),(2,'App\\Models\\User',121),(3,'App\\Models\\User',122),(4,'App\\Models\\User',123),(5,'App\\Models\\User',124),(6,'App\\Models\\User',125),(7,'App\\Models\\User',126),(8,'App\\Models\\User',127),(9,'App\\Models\\User',128);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modificaciones_presupuestarias`
--

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

--
-- Dumping data for table `modificaciones_presupuestarias`
--

LOCK TABLES `modificaciones_presupuestarias` WRITE;
/*!40000 ALTER TABLE `modificaciones_presupuestarias` DISABLE KEYS */;
/*!40000 ALTER TABLE `modificaciones_presupuestarias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_bancarios`
--

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

--
-- Dumping data for table `movimientos_bancarios`
--

LOCK TABLES `movimientos_bancarios` WRITE;
/*!40000 ALTER TABLE `movimientos_bancarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_bancarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_bien`
--

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

--
-- Dumping data for table `movimientos_bien`
--

LOCK TABLES `movimientos_bien` WRITE;
/*!40000 ALTER TABLE `movimientos_bien` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_bien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_partidas`
--

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

--
-- Dumping data for table `movimientos_partidas`
--

LOCK TABLES `movimientos_partidas` WRITE;
/*!40000 ALTER TABLE `movimientos_partidas` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_partidas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nominas`
--

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

--
-- Dumping data for table `nominas`
--

LOCK TABLES `nominas` WRITE;
/*!40000 ALTER TABLE `nominas` DISABLE KEYS */;
/*!40000 ALTER TABLE `nominas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nominas_detalle`
--

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

--
-- Dumping data for table `nominas_detalle`
--

LOCK TABLES `nominas_detalle` WRITE;
/*!40000 ALTER TABLE `nominas_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `nominas_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_compra`
--

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

--
-- Dumping data for table `ordenes_compra`
--

LOCK TABLES `ordenes_compra` WRITE;
/*!40000 ALTER TABLE `ordenes_compra` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordenes_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_compra_detalle`
--

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

--
-- Dumping data for table `ordenes_compra_detalle`
--

LOCK TABLES `ordenes_compra_detalle` WRITE;
/*!40000 ALTER TABLE `ordenes_compra_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordenes_compra_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_pago`
--

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

--
-- Dumping data for table `ordenes_pago`
--

LOCK TABLES `ordenes_pago` WRITE;
/*!40000 ALTER TABLE `ordenes_pago` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordenes_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_pago_detalle`
--

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

--
-- Dumping data for table `ordenes_pago_detalle`
--

LOCK TABLES `ordenes_pago_detalle` WRITE;
/*!40000 ALTER TABLE `ordenes_pago_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordenes_pago_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

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

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partidas_presupuestarias`
--

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

--
-- Dumping data for table `partidas_presupuestarias`
--

LOCK TABLES `partidas_presupuestarias` WRITE;
/*!40000 ALTER TABLE `partidas_presupuestarias` DISABLE KEYS */;
/*!40000 ALTER TABLE `partidas_presupuestarias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

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

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodos_contables`
--

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

--
-- Dumping data for table `periodos_contables`
--

LOCK TABLES `periodos_contables` WRITE;
/*!40000 ALTER TABLE `periodos_contables` DISABLE KEYS */;
/*!40000 ALTER TABLE `periodos_contables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(2,'usuarios.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(3,'usuarios.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(4,'usuarios.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(5,'usuarios.eliminar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(6,'roles.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(7,'roles.gestionar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(8,'unidades.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(9,'unidades.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(10,'unidades.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(11,'unidades.eliminar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(12,'beneficiarios.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(13,'beneficiarios.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(14,'beneficiarios.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(15,'ejercicios.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(16,'ejercicios.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(17,'ejercicios.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(18,'ejercicios.cerrar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(19,'partidas.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(20,'partidas.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(21,'partidas.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(22,'partidas.eliminar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(23,'proyectos.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(24,'proyectos.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(25,'proyectos.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(26,'proyectos.eliminar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(27,'creditos.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(28,'creditos.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(29,'creditos.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(30,'compromisos.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(31,'compromisos.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(32,'compromisos.aprobar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(33,'compromisos.anular','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(34,'causaciones.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(35,'causaciones.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(36,'causaciones.aprobar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(37,'causaciones.anular','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(38,'pagos.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(39,'pagos.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(40,'pagos.procesar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(41,'pagos.anular','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(42,'modificaciones.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(43,'modificaciones.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(44,'modificaciones.aprobar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(45,'modificaciones.anular','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(46,'presupuesto.reportes','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(47,'tesoreria.cuentas.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(48,'tesoreria.cuentas.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(49,'tesoreria.cuentas.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(50,'tesoreria.ordenes.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(51,'tesoreria.ordenes.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(52,'tesoreria.ordenes.aprobar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(53,'tesoreria.ordenes.pagar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(54,'contabilidad.cuentas.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(55,'contabilidad.cuentas.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(56,'contabilidad.cuentas.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(57,'contabilidad.periodos.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(58,'contabilidad.periodos.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(59,'contabilidad.periodos.cerrar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(60,'contabilidad.asientos.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(61,'contabilidad.asientos.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(62,'contabilidad.asientos.aprobar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(63,'contabilidad.asientos.anular','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(64,'contabilidad.reportes','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(65,'compras.almacenes.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(66,'compras.almacenes.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(67,'compras.almacenes.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(68,'compras.articulos.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(69,'compras.articulos.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(70,'compras.articulos.editar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(71,'compras.solicitudes.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(72,'compras.solicitudes.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(73,'compras.solicitudes.aprobar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(74,'compras.ordenes.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(75,'compras.ordenes.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(76,'compras.ordenes.aprobar','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(77,'compras.recepciones.ver','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(78,'compras.recepciones.crear','web','2026-04-09 17:12:15','2026-04-09 17:12:15'),(79,'bienes.ver','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(80,'bienes.crear','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(81,'bienes.editar','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(82,'nomina.ver','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(83,'nomina.crear','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(84,'nomina.aprobar','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(85,'nomina.pagar','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(86,'nomina.anular','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(87,'nomina.empleados.ver','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(88,'nomina.empleados.crear','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(89,'nomina.empleados.editar','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(90,'nomina.conceptos.ver','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(91,'nomina.conceptos.crear','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(92,'nomina.conceptos.editar','web','2026-04-09 17:12:16','2026-04-09 17:12:16'),(93,'retenciones.ver','web','2026-04-15 17:48:05','2026-04-15 17:48:05'),(94,'retenciones.crear','web','2026-04-15 17:48:05','2026-04-15 17:48:05'),(95,'retenciones.editar','web','2026-04-15 17:48:05','2026-04-15 17:48:05'),(96,'almacen.solicitudes.ver','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(97,'almacen.solicitudes.crear','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(98,'almacen.solicitudes.aprobar','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(99,'nomina.nominas.ver','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(100,'nomina.nominas.crear','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(101,'nomina.nominas.aprobar','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(102,'nomina.nominas.pagar','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(103,'nomina.cargos.ver','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(104,'nomina.cargos.crear','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(105,'bienes.categorias.ver','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(106,'bienes.categorias.crear','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(107,'bienes.baja','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(108,'bienes.trasladar','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(109,'ingresos.ver','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(110,'ingresos.arqueos.ver','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(111,'ingresos.arqueos.aprobar','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(112,'ingresos.caja.ver','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(113,'ingresos.caja.crear','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(114,'ingresos.caja.editar','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(115,'ingresos.conceptos.ver','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(116,'ingresos.conceptos.crear','web','2026-04-20 21:57:50','2026-04-20 21:57:50'),(117,'creditos.eliminar','web','2026-04-20 21:57:50','2026-04-20 21:57:50');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proyectos_sia`
--

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

--
-- Dumping data for table `proyectos_sia`
--

LOCK TABLES `proyectos_sia` WRITE;
/*!40000 ALTER TABLE `proyectos_sia` DISABLE KEYS */;
/*!40000 ALTER TABLE `proyectos_sia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recepciones_bienes`
--

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

--
-- Dumping data for table `recepciones_bienes`
--

LOCK TABLES `recepciones_bienes` WRITE;
/*!40000 ALTER TABLE `recepciones_bienes` DISABLE KEYS */;
/*!40000 ALTER TABLE `recepciones_bienes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recepciones_detalle`
--

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

--
-- Dumping data for table `recepciones_detalle`
--

LOCK TABLES `recepciones_detalle` WRITE;
/*!40000 ALTER TABLE `recepciones_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `recepciones_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `retenciones`
--

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

--
-- Dumping data for table `retenciones`
--

LOCK TABLES `retenciones` WRITE;
/*!40000 ALTER TABLE `retenciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `retenciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `retenciones_aplicadas`
--

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

--
-- Dumping data for table `retenciones_aplicadas`
--

LOCK TABLES `retenciones_aplicadas` WRITE;
/*!40000 ALTER TABLE `retenciones_aplicadas` DISABLE KEYS */;
/*!40000 ALTER TABLE `retenciones_aplicadas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

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

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,2),(2,2),(3,2),(4,2),(6,2),(7,2),(8,2),(9,2),(10,2),(12,2),(13,2),(14,2),(15,2),(16,2),(17,2),(19,2),(20,2),(21,2),(23,2),(24,2),(25,2),(27,2),(28,2),(29,2),(30,2),(31,2),(32,2),(34,2),(35,2),(36,2),(38,2),(39,2),(40,2),(42,2),(43,2),(44,2),(46,2),(47,2),(50,2),(54,2),(60,2),(64,2),(65,2),(68,2),(71,2),(74,2),(77,2),(79,2),(96,2),(97,2),(98,2),(99,2),(100,2),(101,2),(102,2),(103,2),(104,2),(105,2),(106,2),(107,2),(108,2),(109,2),(110,2),(111,2),(112,2),(113,2),(114,2),(115,2),(116,2),(117,2),(1,3),(8,3),(12,3),(15,3),(16,3),(17,3),(19,3),(20,3),(21,3),(23,3),(24,3),(25,3),(27,3),(28,3),(29,3),(30,3),(31,3),(32,3),(33,3),(34,3),(35,3),(36,3),(37,3),(38,3),(39,3),(40,3),(41,3),(42,3),(43,3),(44,3),(45,3),(46,3),(1,4),(8,4),(12,4),(15,4),(34,4),(38,4),(47,4),(48,4),(49,4),(50,4),(51,4),(52,4),(53,4),(1,5),(8,5),(15,5),(54,5),(55,5),(56,5),(57,5),(58,5),(59,5),(60,5),(61,5),(62,5),(63,5),(64,5),(1,6),(8,6),(12,6),(65,6),(66,6),(67,6),(68,6),(69,6),(70,6),(71,6),(72,6),(73,6),(74,6),(75,6),(76,6),(77,6),(78,6),(96,6),(97,6),(98,6),(1,7),(8,7),(79,7),(80,7),(81,7),(105,7),(106,7),(107,7),(108,7),(1,8),(8,8),(82,8),(83,8),(84,8),(85,8),(86,8),(87,8),(88,8),(89,8),(90,8),(91,8),(92,8),(99,8),(100,8),(101,8),(102,8),(103,8),(104,8),(1,9),(8,9),(12,9),(15,9),(19,9),(23,9),(27,9),(30,9),(34,9),(38,9),(42,9),(46,9),(47,9),(50,9),(54,9),(60,9),(64,9),(65,9),(68,9),(71,9),(74,9),(77,9),(79,9),(82,9),(87,9);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super-admin','web','fa-crown','#f7b94f','Acceso ilimitado al sistema completo','2026-04-09 17:12:16','2026-04-09 17:12:16'),(2,'administrador','web','fa-user-tie','#7c5cfc','Gestión general del sistema','2026-04-09 17:12:16','2026-04-09 17:12:16'),(3,'analista-presupuesto','web','fa-chart-bar','#4f8ef7','Módulo de presupuesto completo','2026-04-09 17:12:16','2026-04-09 17:12:16'),(4,'tesorero','web','fa-piggy-bank','#22d3a6','Tesorería y órdenes de pago','2026-04-09 17:12:16','2026-04-09 17:12:16'),(5,'analista-contable','web','fa-calculator','#f7b94f','Contabilidad y reportes financieros','2026-04-09 17:12:16','2026-04-09 17:12:16'),(6,'jefe-compras','web','fa-cart-shopping','#f97316','Compras, almacén e inventario','2026-04-09 17:12:16','2026-04-09 17:12:16'),(7,'jefe-bienes','web','fa-box-archive','#a78bfa','Control de bienes nacionales','2026-04-09 17:12:16','2026-04-09 17:12:16'),(8,'jefe-nomina','web','fa-id-badge','#34d399','Nómina y personal','2026-04-09 17:12:16','2026-04-09 17:12:16'),(9,'consultor','web','fa-eye','#8a91a8','Solo lectura en todos los módulos','2026-04-09 17:12:16','2026-04-09 17:12:16');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

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

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('wwqRziuiAU9sPSuUcLnb1MWzCDKadcWpMmeDmlUQ',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','eyJfdG9rZW4iOiJyWnE1c0pqZTZYTlhZSm1OZlg4SndBRTBYclcxd2o3RkcyUWFLWWs4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvcHJveWVjdG9cL3B1YmxpY1wvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779113776),('xNOWkvxZGv1XVTTzEayJHrtZIpDZTHJUmvIXkJJS',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0','eyJfdG9rZW4iOiJVREx2TjB6MmdwV1pweHpXMTRTc2RpUjM2ckxBNGM1Q1l1V2QyTlpGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvcHJveWVjdG9cL3B1YmxpY1wvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779112846);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_compra`
--

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

--
-- Dumping data for table `solicitudes_compra`
--

LOCK TABLES `solicitudes_compra` WRITE;
/*!40000 ALTER TABLE `solicitudes_compra` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitudes_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_compra_detalle`
--

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

--
-- Dumping data for table `solicitudes_compra_detalle`
--

LOCK TABLES `solicitudes_compra_detalle` WRITE;
/*!40000 ALTER TABLE `solicitudes_compra_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitudes_compra_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_despacho`
--

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

--
-- Dumping data for table `solicitudes_despacho`
--

LOCK TABLES `solicitudes_despacho` WRITE;
/*!40000 ALTER TABLE `solicitudes_despacho` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitudes_despacho` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_despacho_detalle`
--

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

--
-- Dumping data for table `solicitudes_despacho_detalle`
--

LOCK TABLES `solicitudes_despacho_detalle` WRITE;
/*!40000 ALTER TABLE `solicitudes_despacho_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitudes_despacho_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidades_ejecutoras`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidades_ejecutoras`
--

LOCK TABLES `unidades_ejecutoras` WRITE;
/*!40000 ALTER TABLE `unidades_ejecutoras` DISABLE KEYS */;
INSERT INTO `unidades_ejecutoras` VALUES (1,'UEJ-0101','Unidad 1',NULL,NULL,1,'2026-05-18 22:45:16','2026-05-18 22:45:16',NULL);
/*!40000 ALTER TABLE `unidades_ejecutoras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrador del Sistema',NULL,NULL,NULL,1,NULL,'admin@cep.gob.ve','2026-05-18 17:55:31','$2y$12$tkvGY.MwwfsFtMoNSpJwfeO4Do.cvCE/aiio8jHdiYj6FsQu5rM02','HLg8PNAiDTCF7I1C2T71GHPUFfn2YLkwb6HtYDEyumlnRxcAPVjlF1cO5inq','2026-05-18 17:55:31','2026-05-18 18:14:46');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-19 13:29:19
