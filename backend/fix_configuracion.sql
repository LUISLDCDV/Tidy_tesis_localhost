-- Script para corregir problema de elementos tipo 'paso' y columna configuracion
-- Este script debe ejecutarse directamente en MySQL

-- 1. Agregar columna configuracion a la tabla elementos
ALTER TABLE elementos ADD COLUMN configuracion JSON NULL AFTER orden;

-- 2. Eliminar todos los elementos tipo 'paso' que causan problemas
DELETE FROM elementos WHERE tipo = 'paso';

-- 3. Verificar que no queden elementos tipo 'paso'
SELECT COUNT(*) as elementos_paso_restantes FROM elementos WHERE tipo = 'paso';

-- 4. Verificar que la columna configuracion existe
DESCRIBE elementos;