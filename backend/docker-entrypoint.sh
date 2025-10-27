#!/bin/bash

# Debug: Verificar archivos de build
echo "=== Build Debug Info ==="
echo "Build directory exists: $(ls -la public/build 2>/dev/null || echo 'No')"
echo "Manifest exists: $(ls -la public/build/manifest.json 2>/dev/null || echo 'No')"
echo "Build files: $(ls public/build 2>/dev/null || echo 'None')"
echo "========================"

# Ejecutar migraciones
echo "Running Laravel migrations..."
php artisan migrate --force

# Verificar si las migraciones fueron exitosas
if [ $? -eq 0 ]; then
    echo "Migrations completed successfully"
else
    echo "Migration failed, but continuing..."
fi

# Iniciar Apache
echo "Starting Apache..."
exec apache2-foreground