-- ============================================================
--  SIA — Limpieza de datos de prueba
--  Preserva: usuarios, roles, permisos, catálogos base
--  Ejecutar en phpMyAdmin o HeidiSQL sobre la BD "sia"
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── Presupuesto ──────────────────────────────────────────────
TRUNCATE TABLE movimientos_partidas;
TRUNCATE TABLE partidas_presupuestarias;
TRUNCATE TABLE ejercicios_fiscales;
TRUNCATE TABLE proyectos_sia;

-- ── Causaciones / Pagos ──────────────────────────────────────
TRUNCATE TABLE causaciones_detalle;
TRUNCATE TABLE causaciones;
TRUNCATE TABLE ordenes_pago;
TRUNCATE TABLE pagos;

-- ── Nómina ───────────────────────────────────────────────────
TRUNCATE TABLE nominas_detalle;
TRUNCATE TABLE nominas;
TRUNCATE TABLE empleados;
TRUNCATE TABLE cargos;

-- ── Compras y Almacén ────────────────────────────────────────
TRUNCATE TABLE recepciones_detalle;
TRUNCATE TABLE recepciones_bienes;
TRUNCATE TABLE ordenes_compra_detalle;
TRUNCATE TABLE ordenes_compra;
TRUNCATE TABLE solicitudes_compra_detalle;
TRUNCATE TABLE solicitudes_compra;
TRUNCATE TABLE solicitudes_despacho_detalle;
TRUNCATE TABLE solicitudes_despacho;
TRUNCATE TABLE inventario_movimientos;

-- ── Beneficiarios ────────────────────────────────────────────
TRUNCATE TABLE beneficiarios;

-- ── Bienes Nacionales / Ingresos ─────────────────────────────
TRUNCATE TABLE bienes_nacionales;
TRUNCATE TABLE ingresos;

-- ── Sesiones ─────────────────────────────────────────────────
TRUNCATE TABLE sessions;
TRUNCATE TABLE password_reset_tokens;

-- ── Tablas contables (en desuso pero si existen) ─────────────
-- TRUNCATE TABLE asientos_detalle;
-- TRUNCATE TABLE asientos_contables;
-- TRUNCATE TABLE periodos_contables;
-- TRUNCATE TABLE cuentas_contables;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  TABLAS QUE NO SE TOCAN (catálogos y seguridad):
--    users
--    roles
--    permissions
--    role_has_permissions
--    model_has_roles
--    model_has_permissions
--    unidades_ejecutoras
--    almacenes
--    articulos
--    migrations
-- ============================================================
