# 🚀 Guía de Deploy - Tidy Frontend

## 📋 Tabla de Contenidos
- [Deploy Rápido (Automatizado)](#deploy-rápido-automatizado)
- [Deploy Manual (Paso a Paso)](#deploy-manual-paso-a-paso)
- [Configuración de Entornos](#configuración-de-entornos)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Deploy Rápido (Automatizado)

### Usando el Script Automatizado

```bash
# Desde el directorio frontend
./deploy-prod.sh
```

**¡Eso es todo!** El script automáticamente:
1. ✅ Respalda tu configuración local (`.env.local`)
2. ✅ Genera el build de producción con configuración correcta
3. ✅ Despliega a Firebase Hosting
4. ✅ Restaura tu configuración local
5. ✅ Te muestra la URL de la app deployada

---

## 🔧 Deploy Manual (Paso a Paso)

Si prefieres hacerlo manualmente o necesitas más control:

### Paso 1: Respaldar Configuración Local
```bash
mv .env.local .env.local.backup
```

### Paso 2: Generar Build de Producción
```bash
npm run build
```
> ⚠️ **Importante:** Sin el `.env.local`, Vite usará el `.env` que tiene la URL de producción

### Paso 3: Deploy a Firebase
```bash
firebase deploy --only hosting
```

### Paso 4: Restaurar Configuración Local
```bash
mv .env.local.backup .env.local
```

---

## ⚙️ Configuración de Entornos

### 📁 Archivos de Configuración

#### `.env` (Producción)
```bash
VITE_API_URL=https://tidyback-production.up.railway.app
VITE_MERCADOPAGO_PUBLIC_KEY=APP_USR-e4ec6d39-96f7-4416-96ec-d5baf56f391f
VITE_MERCADOPAGO_ACCESS_TOKEN=APP_USR-6204038608894489-091819-21add24becf81e7349bf3bd1cb13d4d2-2696010789
```

#### `.env.local` (Desarrollo Local)
```bash
VITE_API_URL=http://localhost:8000
VITE_MERCADOPAGO_PUBLIC_KEY=APP_USR-e4ec6d39-96f7-4416-96ec-d5baf56f391f
VITE_MERCADOPAGO_ACCESS_TOKEN=APP_USR-6204038608894489-091819-21add24becf81e7349bf3bd1cb13d4d2-2696010789
```

### 🔑 Prioridad de Archivos en Vite

Vite carga los archivos `.env` en este orden (de mayor a menor prioridad):

1. **`.env.local`** ← Mayor prioridad (desarrollo local)
2. `.env.production`
3. **`.env`** ← Producción (cuando no hay `.env.local`)

**Por eso es crucial** remover `.env.local` antes del build de producción.

---

## 🌐 URLs del Proyecto

### Producción
- **Frontend**: https://tidy-personal.web.app
- **Backend**: https://tidyback-production.up.railway.app
- **Firebase Console**: https://console.firebase.google.com/project/tidy-1d736/overview

### Desarrollo Local
- **Frontend**: http://localhost:5173 (Vite dev server)
- **Backend**: http://localhost:8000 (Laravel local)

---

## 🐛 Troubleshooting

### Error: "CORS request did not succeed"

**Problema:** La app en producción intenta conectarse a `localhost:8000`

**Causa:** El `.env.local` sobrescribió la configuración de producción durante el build

**Solución:**
```bash
# Eliminar el build anterior
rm -rf dist

# Remover .env.local temporalmente
mv .env.local .env.local.backup

# Rebuild
npm run build

# Verificar que NO use localhost
grep -r "localhost:8000" dist/js/*.js

# Si no hay resultados, el build es correcto
firebase deploy --only hosting

# Restaurar .env.local
mv .env.local.backup .env.local
```

### Error: "Firebase not found"

**Problema:** Firebase CLI no está instalado

**Solución:**
```bash
npm install -g firebase-tools
firebase login
```

### Error: "Permission denied: ./deploy-prod.sh"

**Problema:** El script no tiene permisos de ejecución

**Solución:**
```bash
chmod +x deploy-prod.sh
./deploy-prod.sh
```

### Error: Build falla con errores TypeScript

**Solución:**
```bash
# Limpiar node_modules y reinstalar
rm -rf node_modules package-lock.json
npm install

# Intentar build nuevamente
npm run build
```

---

## 📝 Checklist Pre-Deploy

Antes de hacer deploy, verifica:

- [ ] ✅ Todos los tests pasan: `npm run test` (si aplica)
- [ ] ✅ No hay errores de TypeScript: `npm run type-check` (si aplica)
- [ ] ✅ El build local funciona: `npm run build`
- [ ] ✅ Has probado los cambios localmente
- [ ] ✅ Tienes respaldo de `.env.local`
- [ ] ✅ Estás en la rama correcta (main/master)

---

## 🔄 Flujo de Trabajo Recomendado

### Para Desarrollo Diario
```bash
# 1. Desarrollar con servidor local
npm run dev

# 2. Probar cambios en localhost:5173
# 3. Hacer commit de cambios
git add .
git commit -m "feat: nueva funcionalidad"
git push origin main
```

### Para Deploy a Producción
```bash
# 1. Asegurarse de estar en main/master actualizado
git checkout main
git pull origin main

# 2. Ejecutar el script de deploy
./deploy-prod.sh

# 3. Verificar en https://tidy-personal.web.app
```

---

## 📚 Comandos Útiles

```bash
# Ver logs de Firebase
firebase hosting:channel:list

# Ver versiones deployadas
firebase hosting:releases:list

# Rollback a versión anterior (si es necesario)
firebase hosting:clone <source-site-id>:<channel-id> <target-site-id>:<channel-id>

# Limpiar cache del build
npm run clean  # o rm -rf dist .quasar

# Verificar variables de entorno en build
cat dist/js/index-*.js | grep -o "https://[^\"]*railway[^\"]*"
```

---

## 🎓 Notas Importantes

1. **Nunca subas `.env` o `.env.local` al repositorio** - Están en `.gitignore`
2. **Las credenciales de MercadoPago son de TEST** - Actualiza en producción real
3. **El script `deploy-prod.sh` maneja automáticamente** el backup y restore
4. **Firebase Hosting tiene cache** - Puede tomar unos minutos en propagarse
5. **Siempre verifica la URL del backend** después del deploy

---

## 👨‍💻 Autor

**Luis Duarte**
Proyecto de Tesis - Tidy
Escuela Da Vinci - Analista de Sistemas

---

**Última actualización:** 2025-10-08
