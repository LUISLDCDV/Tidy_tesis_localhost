#!/bin/bash

# Script de Deploy Automatizado para Tidy - Producción
# Autor: Luis Duarte
# Uso: ./deploy-prod.sh

echo "🚀 ============================================="
echo "   TIDY - Deploy a Producción (Firebase)"
echo "============================================="
echo ""

# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Función para manejar errores
handle_error() {
    echo -e "${RED}❌ Error: $1${NC}"

    # Restaurar .env.local si existe el backup
    if [ -f .env.local.backup ]; then
        echo -e "${YELLOW}⚠️  Restaurando .env.local...${NC}"
        mv .env.local.backup .env.local
    fi

    exit 1
}

# Verificar que estamos en el directorio correcto
if [ ! -f "package.json" ]; then
    handle_error "No se encontró package.json. Asegúrate de estar en el directorio frontend"
fi

# Verificar que Firebase está configurado
if [ ! -f "firebase.json" ]; then
    handle_error "No se encontró firebase.json. Firebase no está configurado"
fi

# Paso 1: Respaldar .env.local
echo -e "${BLUE}📦 Paso 1/5: Respaldando configuración local...${NC}"
if [ -f .env.local ]; then
    mv .env.local .env.local.backup
    echo -e "${GREEN}✓ .env.local respaldado${NC}"
else
    echo -e "${YELLOW}⚠️  No se encontró .env.local (esto es normal si es el primer deploy)${NC}"
fi
echo ""

# Paso 2: Verificar .env de producción
echo -e "${BLUE}🔍 Paso 2/5: Verificando configuración de producción...${NC}"
if [ ! -f .env ]; then
    handle_error "No se encontró archivo .env de producción"
fi

# Mostrar la URL del backend que se usará
BACKEND_URL=$(grep "VITE_API_URL" .env | cut -d '=' -f2)
echo -e "${GREEN}✓ Backend URL: ${BACKEND_URL}${NC}"
echo ""

# Paso 3: Build de producción
echo -e "${BLUE}🔨 Paso 3/5: Generando build de producción...${NC}"
npm run build || handle_error "Falló el build de producción"
echo -e "${GREEN}✓ Build completado${NC}"
echo ""

# Paso 4: Deploy a Firebase
echo -e "${BLUE}🚀 Paso 4/5: Desplegando a Firebase Hosting...${NC}"
firebase deploy --only hosting || handle_error "Falló el deploy a Firebase"
echo -e "${GREEN}✓ Deploy completado${NC}"
echo ""

# Paso 5: Restaurar .env.local
echo -e "${BLUE}🔄 Paso 5/5: Restaurando configuración local...${NC}"
if [ -f .env.local.backup ]; then
    mv .env.local.backup .env.local
    echo -e "${GREEN}✓ .env.local restaurado${NC}"
fi
echo ""

# Resumen final
echo -e "${GREEN}=============================================${NC}"
echo -e "${GREEN}✨ Deploy completado exitosamente!${NC}"
echo -e "${GREEN}=============================================${NC}"
echo ""
echo -e "${BLUE}📱 Tu aplicación está disponible en:${NC}"
echo -e "${YELLOW}   https://tidy-personal.web.app${NC}"
echo ""
echo -e "${BLUE}🔧 Backend conectado a:${NC}"
echo -e "${YELLOW}   ${BACKEND_URL}${NC}"
echo ""
echo -e "${GREEN}🎉 ¡Listo para usar!${NC}"
echo ""
