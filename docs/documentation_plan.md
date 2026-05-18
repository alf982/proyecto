# Plan de Documentación Técnica: Sistema de Información Administrativa (SIA)

El plan ha sido actualizado para seguir **exactamente el mismo orden del menú principal (Dashboard)**. Avanzaremos sección por sección documentando los Modelos, Servicios, Controladores y Vistas de cada módulo.

## 0. Estándares
*   **DocBlocks** (`/** ... */`) en PHP (Modelos, Controladores, Servicios).
*   **Componentes Blade** para limpiar las vistas (como hicimos con la paginación).
*   Evitar N+1 mediante `with()` en las consultas.

---

## Módulo 1: Principal y Administración
El núcleo del sistema y gestión de accesos.

* [x] **Dashboard** *(Optimizado y Documentado).*
* [x] **Usuarios** *(Optimizado y Documentado).*
* [x] **Unidades Ejecutoras** *(Optimizado y Documentado).*
* [x] **Beneficiarios** *(Optimizado y Documentado).*
* [x] **Roles y Permisos** *(Optimizado y Documentado).*
* [x] **Auditoría del Sistema** *(Optimizado y Documentado).*

## Módulo 2: Presupuesto
El flujo financiero base del ente.

* [x] **Ejercicios Fiscales** *(Optimizado y Documentado).*
* [x] **Catálogo de Partidas** *(Optimizado y Documentado).*
* [x] **Movimientos de Partidas** *(Optimizado y Documentado).*
* [x] **Proyectos** *(Optimizado y Documentado).*
* [x] **Compromisos** *(Optimizado y Documentado).*
* [x] **Causaciones** *(Optimizado y Documentado).*
* [x] **Pagos** *(Optimizado y Documentado).*
* [x] **Ejecución Presupuestaria** *(Optimizado y Documentado).*

## Módulo 3: Tesorería
* [x] **Cuentas Bancarias** *(Optimizado y Documentado).*

## Módulo 4: Compras y Almacén
El flujo de abastecimiento e inventario.

* [x] **Almacenes** *(Optimizado y Documentado).*
* [x] **Catálogo de Artículos** *(Optimizado y Documentado).*
* [x] **Solicitudes de Despacho** *(Optimizado y Documentado).*
* [x] **Solicitudes de Compra** *(Optimizado y Documentado).*
* [x] **Órdenes de Compra** *(Optimizado y Documentado).*
* [x] **Recepciones de Bienes** *(Optimizado y Documentado).*

## Módulo 5: Bienes Nacionales
Gestión de activos fijos (equipos, mobiliario, vehículos).

* [x] **Categorías de Bienes** *(Optimizado y Documentado).*
* [x] **Inventario de Bienes** *(Optimizado y Documentado).*

## Módulo 6: Nómina y Personal
Gestión de talento humano y cálculo de salarios.

* [x] **Empleados** *(Optimizado y Documentado).*
* [x] **Cargos** *(Optimizado y Documentado).*
* [x] **Conceptos de Nómina** *(Optimizado y Documentado).*
* [x] **Nóminas** *(Optimizado y Documentado).*

## Módulo 7: Configuración Fiscal
* [x] **Retenciones** *(Optimizado y Documentado).*

---
### Flujo de Trabajo (Para cada ítem)
1.  **Modelo**: Comentar tabla y relaciones (`hasMany`, `belongsTo`).
2.  **Servicio**: Si existe lógica pesada (ej: `CompromisoService`), documentar sus reglas.
3.  **Controlador**: Asegurar carga ambiciosa (`with()`) y comentar funciones.
4.  **Vistas**: Limpiar HTML y separar en sub-componentes.
