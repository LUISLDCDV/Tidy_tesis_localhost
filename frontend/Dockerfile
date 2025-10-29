FROM node:20-alpine

# Instalar bash y dependencias mínimas
RUN apk add --no-cache \
    bash \
    git

# Establecer directorio de trabajo
WORKDIR /app

# Crear .npmrc para ignorar scripts problemáticos y configurar npm
RUN echo "ignore-scripts=true" > /app/.npmrc && \
    echo "prefer-offline=true" >> /app/.npmrc && \
    echo "audit=false" >> /app/.npmrc && \
    echo "fund=false" >> /app/.npmrc

# Copiar package.json y package-lock.json
COPY package*.json ./

# Instalar dependencias ignorando TODOS los scripts de postinstall
# Esto evita que pngquant-bin, gifsicle, etc intenten compilar
RUN npm install --legacy-peer-deps --loglevel=error

# Habilitar scripts solo para dependencias críticas que necesitamos
RUN npm rebuild esbuild --ignore-scripts=false 2>/dev/null || true

# Copiar el resto de archivos
COPY . .

# Exponer puerto
EXPOSE 9000

# Comando por defecto (se sobrescribe en docker-compose)
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "9000"]
