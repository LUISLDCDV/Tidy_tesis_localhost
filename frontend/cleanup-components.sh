#!/bin/bash

# Script de limpieza de componentes sin uso en Tidy Frontend
# Creado: 2025-10-11
# Uso: bash cleanup-components.sh

# Removido set -e para permitir que continúe si un archivo ya fue eliminado

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Contadores
DELETED_COUNT=0
SKIPPED_COUNT=0
ERROR_COUNT=0

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  LIMPIEZA DE COMPONENTES SIN USO${NC}"
echo -e "${BLUE}  Tidy Frontend - Octubre 2025${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Función para eliminar archivo con verificación
delete_file() {
    local file="$1"
    local description="$2"

    if [ -f "$file" ]; then
        echo -e "${YELLOW}Verificando:${NC} $description"
        echo -e "  Archivo: $file"

        # Buscar referencias en código
        echo -e "  Buscando referencias..."
        local filename=$(basename "$file" .vue)
        local refs=$(grep -r "$filename" src --include="*.vue" --include="*.js" --include="*.ts" 2>/dev/null | grep -v "$file" | wc -l)

        if [ "$refs" -gt 0 ]; then
            echo -e "  ${RED}⚠️  ADVERTENCIA: Se encontraron $refs posibles referencias${NC}"
            echo -e "  ${YELLOW}Saltando este archivo por seguridad${NC}\n"
            ((SKIPPED_COUNT++))
            return 1
        else
            echo -e "  ${GREEN}✓ No se encontraron referencias${NC}"
            rm "$file"
            echo -e "  ${GREEN}✓ Eliminado exitosamente${NC}\n"
            ((DELETED_COUNT++))
            return 0
        fi
    else
        echo -e "${YELLOW}⊘ Archivo no encontrado (ya eliminado?):${NC} $file\n"
        ((SKIPPED_COUNT++))
        return 1
    fi
}

# Función para eliminar directorio con verificación
delete_directory() {
    local dir="$1"
    local description="$2"

    if [ -d "$dir" ]; then
        echo -e "${YELLOW}Verificando directorio:${NC} $description"
        echo -e "  Directorio: $dir"

        local file_count=$(find "$dir" -type f | wc -l)
        echo -e "  Archivos en el directorio: $file_count"

        # Buscar referencias a cualquier archivo del directorio
        local dir_name=$(basename "$dir")
        local refs=$(grep -r "$dir_name" src --include="*.vue" --include="*.js" --include="*.ts" 2>/dev/null | grep -v "$dir" | wc -l)

        if [ "$refs" -gt 0 ]; then
            echo -e "  ${RED}⚠️  ADVERTENCIA: Se encontraron $refs posibles referencias${NC}"
            echo -e "  ${YELLOW}Saltando este directorio por seguridad${NC}\n"
            ((SKIPPED_COUNT++))
            return 1
        else
            echo -e "  ${GREEN}✓ No se encontraron referencias${NC}"
            rm -rf "$dir"
            echo -e "  ${GREEN}✓ Directorio eliminado exitosamente${NC}\n"
            ((DELETED_COUNT++))
            return 0
        fi
    else
        echo -e "${YELLOW}⊘ Directorio no encontrado:${NC} $dir\n"
        ((SKIPPED_COUNT++))
        return 1
    fi
}

# Crear backup antes de empezar
echo -e "${BLUE}Creando backup de seguridad...${NC}"
BACKUP_DIR="./backup_components_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Copiar solo los archivos que vamos a eliminar
echo -e "Copiando archivos a eliminar a: $BACKUP_DIR\n"

# ====================================
# PASO 1: DUPLICADOS CONFIRMADOS (100% SEGURO)
# ====================================
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}PASO 1: Eliminando duplicados confirmados${NC}"
echo -e "${GREEN}========================================${NC}\n"

# GeoClima copy.vue
FILE="src/components/Elements/Calendary/GeoClima copy.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Archivo duplicado GeoClima (con ' copy' en nombre)"

# ElementsContainer-Quasar.vue
FILE="src/components/Elements/ElementsContainer-Quasar.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Versión antigua de ElementsContainer"

# Carpeta animations/ (minúscula) - duplicada
DIR="src/components/animations"
if [ -d "$DIR" ]; then
    cp -r "$DIR" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_directory "$DIR" "Carpeta animations/ duplicada (existe Animations/)"

# ====================================
# PASO 2: COMPONENTES SIN REFERENCIAS
# ====================================
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}PASO 2: Eliminando componentes sin referencias${NC}"
echo -e "${GREEN}========================================${NC}\n"

# NotaAnotadorPuntaje.vue
FILE="src/components/Elements/Notes/NotaAnotadorPuntaje.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente de nota sin uso"

# NoteFormatoFigma.vue
FILE="src/components/Elements/Notes/NoteFormatoFigma.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente de nota formato Figma sin uso"

# UserButton.vue
FILE="src/components/User/UserButton.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente UserButton sin referencias"

# barra_de_progreso.vue
FILE="src/components/User/barra_de_progreso.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente barra de progreso sin referencias"

# AdvancedGPSAlarm.vue
FILE="src/components/alarms/AdvancedGPSAlarm.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente AdvancedGPSAlarm (funcionalidad integrada en AlarmsList)"

# ====================================
# PASO 3: DIRECTORIO ADMIN SIN USO
# ====================================
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}PASO 3: Eliminando directorio admin sin uso${NC}"
echo -e "${GREEN}========================================${NC}\n"

DIR="src/components/admin"
if [ -d "$DIR" ]; then
    cp -r "$DIR" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_directory "$DIR" "Directorio admin/ sin rutas ni referencias"

# ====================================
# PASO 4: COMPONENTES COMMON SIN USO
# ====================================
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}PASO 4: Eliminando componentes common sin uso${NC}"
echo -e "${GREEN}========================================${NC}\n"

# ChatComponent.vue
FILE="src/components/common/ChatComponent.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente ChatComponent sin referencias"

# ElementCounters.vue
FILE="src/components/common/ElementCounters.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente ElementCounters sin referencias"

# OfflineSyncStatus.vue
FILE="src/components/common/OfflineSyncStatus.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente OfflineSyncStatus sin referencias"

# ====================================
# PASO 5: COMPONENTES RAÍZ OBSOLETOS
# ====================================
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}PASO 5: Eliminando componentes raíz obsoletos${NC}"
echo -e "${GREEN}========================================${NC}\n"

# CloudSync.vue
FILE="src/components/CloudSync.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente CloudSync obsoleto (reemplazado por SyncStatusIndicator)"

# CreateEditElement.vue
FILE="src/components/CreateEditElement.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente CreateEditElement obsoleto (reemplazado por DinamicNote)"

# NotificationCenter.vue
FILE="src/components/NotificationCenter.vue"
if [ -f "$FILE" ]; then
    cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
fi
delete_file "$FILE" "Componente NotificationCenter obsoleto (reemplazado por NotificationManager)"

# ====================================
# PASO 6 (OPCIONAL): COMPONENTES DE TESTING
# ====================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}PASO 6 (OPCIONAL): Componentes de testing${NC}"
echo -e "${YELLOW}========================================${NC}\n"

echo -e "${YELLOW}Los siguientes componentes son para testing/debug:${NC}"
echo -e "  - src/components/APPCounter.vue"
echo -e "  - src/components/SimpleHome.vue"
echo -e "  - src/components/__tests__/SimpleHome.test.js"
echo -e "\n${YELLOW}¿Deseas eliminarlos? (s/N):${NC} "
read -r RESPONSE

if [[ "$RESPONSE" =~ ^[Ss]$ ]]; then
    echo -e "\n${BLUE}Eliminando componentes de testing...${NC}\n"

    # APPCounter.vue
    FILE="src/components/APPCounter.vue"
    if [ -f "$FILE" ]; then
        cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
    fi
    delete_file "$FILE" "Componente de prueba APPCounter"

    # SimpleHome.vue
    FILE="src/components/SimpleHome.vue"
    if [ -f "$FILE" ]; then
        cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
    fi
    delete_file "$FILE" "Componente de prueba SimpleHome"

    # SimpleHome.test.js
    FILE="src/components/__tests__/SimpleHome.test.js"
    if [ -f "$FILE" ]; then
        cp "$FILE" "$BACKUP_DIR/" 2>/dev/null || true
    fi
    delete_file "$FILE" "Test de SimpleHome"
else
    echo -e "\n${YELLOW}Saltando eliminación de componentes de testing${NC}\n"
    SKIPPED_COUNT=$((SKIPPED_COUNT + 3))
fi

# ====================================
# RESUMEN FINAL
# ====================================
echo -e "\n${BLUE}========================================${NC}"
echo -e "${BLUE}       RESUMEN DE LIMPIEZA${NC}"
echo -e "${BLUE}========================================${NC}\n"

echo -e "${GREEN}✓ Archivos eliminados:${NC} $DELETED_COUNT"
echo -e "${YELLOW}⊘ Archivos saltados:${NC} $SKIPPED_COUNT"
echo -e "${RED}✗ Errores:${NC} $ERROR_COUNT"

if [ "$DELETED_COUNT" -gt 0 ]; then
    echo -e "\n${GREEN}Backup creado en:${NC} $BACKUP_DIR"
    echo -e "${YELLOW}Si algo sale mal, puedes restaurar desde el backup${NC}"
fi

echo -e "\n${BLUE}========================================${NC}"
echo -e "${BLUE}Limpieza completada!${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Verificar si hay directorios vacíos resultantes
echo -e "${BLUE}Verificando directorios vacíos...${NC}"
EMPTY_DIRS=$(find src/components -type d -empty 2>/dev/null)

if [ -n "$EMPTY_DIRS" ]; then
    echo -e "${YELLOW}Directorios vacíos encontrados:${NC}"
    echo "$EMPTY_DIRS"
    echo -e "\n${YELLOW}¿Deseas eliminar directorios vacíos? (s/N):${NC} "
    read -r RESPONSE

    if [[ "$RESPONSE" =~ ^[Ss]$ ]]; then
        find src/components -type d -empty -delete 2>/dev/null
        echo -e "${GREEN}✓ Directorios vacíos eliminados${NC}\n"
    else
        echo -e "${YELLOW}Directorios vacíos no eliminados${NC}\n"
    fi
else
    echo -e "${GREEN}✓ No se encontraron directorios vacíos${NC}\n"
fi

# Sugerencias finales
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  PRÓXIMOS PASOS SUGERIDOS${NC}"
echo -e "${BLUE}========================================${NC}\n"

echo -e "1. ${YELLOW}Ejecutar tests:${NC}"
echo -e "   npm run test\n"

echo -e "2. ${YELLOW}Probar el build:${NC}"
echo -e "   npm run build\n"

echo -e "3. ${YELLOW}Verificar la app en desarrollo:${NC}"
echo -e "   npm run dev\n"

echo -e "4. ${YELLOW}Si todo funciona correctamente:${NC}"
echo -e "   rm -rf $BACKUP_DIR\n"

echo -e "5. ${YELLOW}Si algo falla, restaurar:${NC}"
echo -e "   cp -r $BACKUP_DIR/* src/components/\n"

echo -e "${GREEN}¡Listo! Tu proyecto está más limpio ahora 🎉${NC}\n"
