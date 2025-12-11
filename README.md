# Tidy - Entorno de Desarrollo Local con Docker

Sistema de productividad personal con gamificación. Este repositorio está configurado para desarrollo local usando Docker Compose.

## 🚀 Stack Tecnológico

- **Frontend**: Vue.js 3 + Quasar Framework
- **Backend**: Laravel 11 (PHP 8.2)
- **Base de Datos**: MySQL 8.0
- **Contenedorización**: Docker + Docker Compose

## 📋 Prerequisitos

- Docker >= 20.10
- Docker Compose >= 2.0
- Git

## 🛠️ Instalación y Configuración

### 1. Clonar el repositorio

```bash
git clone https://github.com/LUISLDCDV/Tidy_tesis_localhost.git
cd Tidy_tesis_localhost
```

### 2. Configurar variables de entorno

#### Backend (.env)

```bash
cp backend/.env.example backend/.env
```

Edita `backend/.env` si necesitas cambiar configuraciones (ya viene con valores por defecto para Docker).

#### Frontend (.env)

```bash
cp frontend/.env.example frontend/.env
```

El frontend ya está configurado para apuntar al backend en `http://localhost:8000`.

### 3. Levantar los servicios con Docker Compose

```bash
docker-compose build
docker-compose up -d
docker compose exec backend php artisan migrate:fresh --seed

```

Este comando levantará:

- **MySQL**: Puerto 3306
- **Backend (Laravel)**: Puerto 8000
- **Frontend (Quasar)**: Puerto 9000
- **phpMyAdmin**: Puerto 8080 (opcional)

### 4. Verificar que los servicios estén corriendo

```bash
docker-compose ps
```

Deberías ver algo como:

```
NAME                IMAGE                  STATUS          PORTS
tidy_backend        tidy_backend           Up 2 minutes    0.0.0.0:8000->8000/tcp
tidy_frontend       tidy_frontend          Up 2 minutes    0.0.0.0:9000->9000/tcp
tidy_mysql          mysql:8.0              Up 2 minutes    0.0.0.0:3306->3306/tcp
tidy_phpmyadmin     phpmyadmin/phpmyadmin  Up 2 minutes    0.0.0.0:8080->80/tcp
```

## 🌐 Acceder a la Aplicación

- **Frontend**: http://localhost:9000
- **Backend API**: http://localhost:8000/api
- **phpMyAdmin**: http://localhost:8080
  - Usuario: `root`
  - Contraseña: `root`

## 📦 Servicios Disponibles

### MySQL

- Host: `localhost`
- Puerto: `3306`
- Base de datos: `tidy_db`
- Usuario: `tidy_user`
- Contraseña: `tidy_password`
- Usuario root: `root` / `root`

### Backend Laravel

- URL: `http://localhost:8000`
- API: `http://localhost:8000/api`
- Las migraciones y seeders se ejecutan automáticamente al iniciar

### Frontend Quasar

- URL: `http://localhost:9000`
- Hot reload habilitado (los cambios se reflejan automáticamente)

## 🔧 Comandos Útiles

### Detener los servicios

```bash
docker-compose down
```

### Detener y eliminar volúmenes (⚠️ elimina la BD)

```bash
docker-compose down -v
```

### Ver logs de todos los servicios

```bash
docker-compose logs -f
```

### Ver logs de un servicio específico

```bash
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f mysql
```

### Ejecutar comandos en el backend

```bash
# Entrar al contenedor
docker-compose exec backend bash

# Ejecutar migraciones
docker-compose exec backend php artisan migrate

# Crear un seeder
docker-compose exec backend php artisan db:seed

# Limpiar cache
docker-compose exec backend php artisan cache:clear

# Comando completo despues de deploy
docker compose exec backend php artisan migrate:fresh --seed

# Comando para realizar pruebas
docker compose exec backend php artisan test


```

### Ejecutar comandos en el frontend

```bash
# Entrar al contenedor
docker-compose exec frontend sh

# Instalar dependencias
docker-compose exec frontend npm install

# Build para producción
docker-compose exec frontend npm run build
```

### Reiniciar un servicio específico

```bash
docker-compose restart backend
docker-compose restart frontend
```

### Reconstruir imágenes (después de cambios en Dockerfile)

```bash
docker-compose build
docker-compose up -d
```

## 🗄️ Gestión de Base de Datos

### Acceder a MySQL desde línea de comandos

```bash
docker-compose exec mysql mysql -u tidy_user -p tidy_db
# Contraseña: tidy_password
```

### Backup de la base de datos

```bash
docker-compose exec mysql mysqldump -u root -proot tidy_db > backup.sql
```

### Restaurar base de datos

```bash
docker-compose exec -T mysql mysql -u root -proot tidy_db < backup.sql
```

## 🐛 Troubleshooting

### El backend no inicia

```bash
# Ver logs
docker-compose logs backend

# Posibles soluciones:
# 1. Limpiar caché de Composer
docker-compose exec backend composer clear-cache
docker-compose restart backend

# 2. Regenerar key de Laravel
docker-compose exec backend php artisan key:generate
```

### El frontend no inicia

```bash
# Ver logs
docker-compose logs frontend

# Posibles soluciones:
# 1. Reinstalar dependencias
docker-compose exec frontend rm -rf node_modules package-lock.json
docker-compose exec frontend npm install
docker-compose restart frontend
```

### MySQL no acepta conexiones

```bash
# Verificar estado
docker-compose logs mysql

# Reiniciar MySQL
docker-compose restart mysql

# Si persiste, eliminar volumen y recrear
docker-compose down -v
docker-compose up -d
```

### Puerto ya en uso

Si algún puerto (3306, 8000, 9000, 8080) ya está en uso:

1. Edita `docker-compose.yml`
2. Cambia el puerto externo (izquierda):
   ```yaml
   ports:
     - "NUEVO_PUERTO:8000"  # Ejemplo: "8001:8000"
   ```

## 📁 Estructura del Proyecto

```
Tidy_tesis_localhost/
├── backend/              # Laravel API
│   ├── app/
│   ├── database/
│   ├── routes/
│   ├── .env              # Variables de entorno del backend
│   └── Dockerfile
├── frontend/             # Vue.js + Quasar
│   ├── src/
│   ├── public/
│   ├── .env              # Variables de entorno del frontend
│   └── Dockerfile
├── docker-compose.yml    # Configuración de Docker Compose
└── README.md
```

## 🔐 Credenciales de Desarrollo

### Aplicación

- **Usuario demo**: `demo@demo.com`
- **Contraseña**: `demo123`

(Estas credenciales se crean automáticamente con los seeders)

### Base de Datos

- **Usuario**: `tidy_user`
- **Contraseña**: `tidy_password`
- **Root**: `root` / `root`

## 🚨 Notas Importantes

- ⚠️ Este entorno está configurado para **desarrollo local únicamente**
- ⚠️ No usar estas credenciales en producción
- ⚠️ Los datos se persisten en volúmenes de Docker
- ⚠️ Al hacer `docker-compose down -v` se eliminan todos los datos

## 📝 Desarrollo

### Hacer cambios en el código

Los cambios se reflejan automáticamente gracias a los volúmenes montados:

- **Frontend**: Hot reload automático en `http://localhost:9000`
- **Backend**: Cambios en controladores/modelos requieren reiniciar el contenedor:
  ```bash
  docker-compose restart backend
  ```

### Agregar dependencias

#### Frontend

```bash
docker-compose exec frontend npm install <paquete>
```

#### Backend

```bash
docker-compose exec backend composer require <paquete>
```

## 📚 Documentación Adicional

- [Laravel Documentation](https://laravel.com/docs/11.x)
- [Vue.js Documentation](https://vuejs.org/)
- [Quasar Framework](https://quasar.dev/)
- [Docker Compose](https://docs.docker.com/compose/)

## 🤝 Contribuir

Este es un proyecto de tesis. Para contribuir:

1. Crea una rama: `git checkout -b feature/nueva-funcionalidad`
2. Haz tus cambios
3. Commit: `git commit -m 'feat: descripción'`
4. Push: `git push origin feature/nueva-funcionalidad`
5. Crea un Pull Request

## 📄 Licencia

Este proyecto es parte de una tesis universitaria.

---

**Desarrollado para desarrollo local con Docker Compose**
