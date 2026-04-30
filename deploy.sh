#!/bin/bash
# ============================================================
#  deploy.sh — Script de despliegue a producción
#  ERP Sistema Integrado de Administración (SIA)
#  Ejecutar desde la raíz del proyecto como: bash deploy.sh
# ============================================================

set -e  # Detener si cualquier comando falla

echo "🚀 Iniciando despliegue..."

# 1. Modo mantenimiento
echo "⏸  Activando modo mantenimiento..."
php artisan down --render="errors.503" --retry=30

# 2. Actualizar código fuente
echo "📦 Actualizando código..."
git pull origin main

# 3. Dependencias PHP
echo "📚 Instalando dependencias PHP (producción)..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Migraciones pendientes
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force

# 5. Limpiar cachés anteriores antes de regenerar
echo "🧹 Limpiando cachés anteriores..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# 6. Regenerar cachés de producción
echo "⚡ Generando cachés de producción..."
php artisan config:cache    # config/*.php en un solo archivo
php artisan route:cache     # Todas las rutas pre-compiladas
php artisan view:cache      # Blade pre-compilado
php artisan event:cache     # Event listeners

# 7. Optimización adicional
echo "🔧 Optimizando autoloader..."
php artisan optimize

# 8. Reiniciar queue worker (requiere supervisor en producción)
echo "🔄 Reiniciando queue workers..."
php artisan queue:restart

# 9. Limpiar caché de catálogos del CatalogoCache (datos freshcos en primer request)
echo "🗑️  Limpiando caché de catálogos SIA..."
php artisan tinker --execute="App\Services\CatalogoCache::olvidarTodo(); echo 'Caché de catálogos limpiado.';"

# 10. Salir de modo mantenimiento
echo "✅ Levantando modo mantenimiento..."
php artisan up

echo ""
echo "✨ Despliegue completado exitosamente."
echo "   Servidor: $(php artisan --version)"
