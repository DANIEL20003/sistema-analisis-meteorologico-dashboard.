# Guía de Uso - Sistema de Carga L2

## 📋 Resumen

El sistema de carga L2 está **completamenteimplementado** y genera automáticamente:

1. **Archivo Crudo** → Copia del archivo original sin modificar
2. **CSV Limpio para BD** → Solo columnas necesarias para la base de datos
3. **CSV Completo Limpio** → Todas las columnas originales, pero con datos limpios

## 🔍 Testing del Sistema

### Test 1: Verificación General del Sistema

```bash
# Ejecutar en navegador
http://localhost/PracticasM/test_carga_completa_l2.php
```

**Qué verifica:**
- ✅ Archivos del sistema existen (DataCleanerL2.php, CLoadDataL2.php, MLoadDataL2.php)
- ✅ Carpetas de guardado están configuradas correctamente
- ✅ Estaciones en base de datos
- ✅ Datos L2 existentes (si hay)
- ✅ Vistas materializadas funcionando
- ✅ Estructura de tabla `datos_l2` correcta

### Test 2: Diagnóstico de Vistas

```bash
# Ejecutar en navegador  
http://localhost/PracticasM/test_endpoints_l2.php
```

**Qué verifica:**
- ✅ Conexión a PostgreSQL
- ✅ Datos L2 en registros y datos_l2
- ✅ Estado de vistas materializadas L2
- ✅ Muestra de datos recientes
- ✅ Endpoints en CDashboard.php

## 📁 Estructura de Carpetas Generadas

```
uploads/
├── [NOMBRE_ESTACION]/
│   └── Crudos/
│       └── L2/
│           └── [ESTACION]_L2_[FECHA-INICIO]_[FECHA-FIN]_Crudos.csv
│
└── [NOMBRE_ESTACION]/
    └── Limpios/
        └── L2/
            ├── [ESTACION]_L2_[FECHA-INICIO]_[FECHA-FIN]_Limpios_BD.csv  ← Para insertar en BD
            └── [ESTACION]_L2_[FECHA-INICIO]_[FECHA-FIN]_Limpios.csv     ← Todas las columnas limpias
```

## 🔄 Proceso de Carga L2

### 1. Preparación

**Formato de archivo esperado:**
- Extensión: `.csv` o `.txt`
- Separador: `,` o `;` (se detecta automáticamente)
- Debe contener columnas de fecha/hora
- Variables esperadas (nombres estándar):
  - `Stat_TA_1h` → Temperatura promedio hora (°C)
  - `Stat_RH_1h` → Humedad relativa promedio hora (%)
  - `Stat_PA_1h` → Presión atmosférica promedio hora (hPa)
  - `Sum_SR_Glob_1h` → Radiación global total hora (W/m²)
  - `GenWind_1h` → Velocidad viento promedio hora (m/s)
  - `GenWind_Dir_1h` → Dirección viento promedio hora (grados)

### 2. Carga del Archivo

**Opción A: Interfaz Web**
1. Abrir `http://localhost/PracticasM/`
2. Ir a "Cargar Datos L2"
3. Seleccionar estación
4. Subir archivo CSV
5. Esperar confirmación

**Opción B: API (Programático)**
```bash
curl -X POST http://localhost/PracticasM/controller/CLoadDataL2.php \
  -F "archivo=@ruta/al/archivo.csv" \
  -F "id_estacion=1"
```

### 3. Procesamiento Automático

El sistema ejecuta automáticamente:

1. **Validación** del archivo
2. **Guardado del crudo** en `Crudos/L2/`
3. **Limpieza de datos** con DataCleanerL2:
   - Detección automática de columnas
   - Parsing de fecha/hora
   - Validación de umbrales
   - Limpieza de valores numéricos
4. **Generación de 2 CSV limpios**:
   - CSV para BD (solo columnas necesarias)
   - CSV completo limpio (todas las columnas)
5. **Inserción en base de datos**:
   - Tabla `registros` (metadatos)
   - Tabla `datos_l2` (datos meteorológicos)
6. **Actualización de vistas** (opcional)

### 4. Verificación

**Verificar archivos generados:**
```bash
# En terminal o explorador de archivos
cd uploads/[NOMBRE_ESTACION]/Crudos/L2/
# Debe haber archivo: [ESTACION]_L2_[FECHA]_[FECHA]_Crudos.csv

cd uploads/[NOMBRE_ESTACION]/Limpios/L2/
# Debe haber 2 archivos:
# - [ESTACION]_L2_[FECHA-INICIO]_[FECHA-FIN]_Limpios_BD.csv
# - [ESTACION]_L2_[FECHA-INICIO]_[FECHA-FIN]_Limpios.csv
```

**Verificar datos en BD:**
```sql
-- En PostgreSQL
SELECT COUNT(*) FROM registros WHERE tipo_nivel = 'L2';
SELECT COUNT(*) FROM datos_l2;

-- Ver datos recientes
SELECT r.fecha_hora, dl2.temp_promedio_hora, dl2.humedad_promedio_hora
FROM registros r
INNER JOIN datos_l2 dl2 ON r.id_registro = dl2.id_registro
WHERE r.tipo_nivel = 'L2'
ORDER BY r.fecha_hora DESC
LIMIT 10;
```

**Refrescar vistas materializadas (si necesario):**
```sql
SELECT refrescar_vistas_materializadas();
```

## ⚙️ Configuración

### Umbrales de Validación

Ubicación: `controller/DataCleanerL2.php` líneas 12-19

```php
private $umbrales = [
    'temperatura' => ['min' => -80.0, 'max' => 60.0],
    'humedad' => ['min' => 0.0, 'max' => 100.0],
    'presion' => ['min' => 500.0, 'max' => 1080.0],
    'radiacion' => ['min' => 0.0, 'max' => 1373.0],
    'viento_velocidad' => ['min' => 0.0, 'max' => 75.0],
    'viento_direccion' => ['min' => 0.0, 'max' => 360.0]
];
```

### Mapeo de Columnas

Ubicación: `controller/DataCleanerL2.php` líneas 22-29

```php
private $mappingColumnas = [
    'Stat_TA_1h' => 'temp_promedio_hora',
    'Stat_RH_1h' => 'humedad_promedio_hora',
    'Stat_PA_1h' => 'presion_promedio_hora',
    'Sum_SR_Glob_1h' => 'radiacion_total_hora',
    'GenWind_1h' => 'viento_promedio_hora',
    'GenWind_Dir_1h' => 'direccion_viento_hora'
];
```

**Para ajustar:** Editar estas líneas si tus archivos CSV tienen nombres de columnas diferentes.

## 🐛 Troubleshooting

### Problema: "No se pudieron detectar las columnas"

**Solución:**
- Verificar que el archivo tenga encabezados con nombres de columnas
- Revisar el mapping en `DataCleanerL2.php`
- Verificar que haya al menos 3 líneas con datos reales

### Problema: "Vistas materializadas vacías"

**Solución:**
```sql
REFRESH MATERIALIZED VIEW mv_l2_consolidado;
REFRESH MATERIALIZED VIEW mv_l2_heatmap_temperatura;
REFRESH MATERIALIZED VIEW mv_l2_heatmap_radiacion;
REFRESH MATERIALIZED VIEW mv_l2_rosa_vientos;
REFRESH MATERIALIZED VIEW mv_l2_humedad_rangos;
```

O ejecutar:
```sql
SELECT refrescar_vistas_materializadas();
```

### Problema: "Archivos no se generan"

**Solución:**
- Verificar permisos de escritura en carpeta `uploads/`
- Revisar logs en `uploads/logs/carga_l2.log`
- Ejecutar test de verificación: `test_carga_completa_l2.php`

## 📊 Visualización de Datos

Después de cargar datos L2, puedes visualizarlos en:

1. **Dashboard Principal** → Gráficas L2
2. **Vistas Materializadas** → Datos agregados optimizados
3. **CSV Generados** → Archivos limpios para análisis externo

---

**Última actualización:** 2025-12-19
