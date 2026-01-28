# Manual de Usuario
## Sistema de Análisis Meteorológico - ESPOCH

### 1. Introducción
Bienvenido al Sistema de Análisis Meteorológico. Esta plataforma le permite visualizar, descargar y gestionar la información climatológica recopilada por la red de estaciones automáticas de la ESPOCH en la provincia de Chimborazo.

### 2. Acceso al Sistema
Para acceder a las funciones administrativas y de carga de datos, debe iniciar sesión:
1.  Diríjase a la página de inicio.
2.  Haga clic en el botón **"Acceso Administrativo"** (generalmente ubicado en la parte superior o pie de página).
3.  Ingrese sus credenciales (Correo y Contraseña).
4.  Si los datos son correctos, será redirigido al panel principal.

> **Nota**: El acceso público permite visualizar los dashboards y descargar datos libres sin necesidad de cuenta.

### 3. Uso del Dashboard (Panel de Visualización)
El Dashboard es la herramienta principal para el análisis de datos.

#### 3.1. Selección de Datos
En la barra lateral o superior encontrará los filtros:
- **Estación**: Seleccione la estación meteorológica de interés (ej. "Tunshi", "Sochos").
- **Rango de Fechas**: Defina las fechas de inicio y fin para el análisis.
- **Nivel de Datos**: Elija entre L1 (Validado) o L2 (Consolidado Horario).

#### 3.2. Interpretación de Gráficos
El sistema generará automáticamente los siguientes gráficos:

- **Climograma**: Muestra la relación entre la temperatura (línea) y la precipitación (barras) a lo largo del tiempo.
- **Rosa de los Vientos**: Diagrama polar que indica la dirección predominante y la velocidad del viento. Los "pétalos" más largos indican mayor frecuencia en esa dirección.
- **Mapa de Calor (Heatmap)**:
    - Muestra la intensidad de una variable (ej. Temperatura) en una cuadrícula.
    - **Eje Vertical**: Horas del día (00:00 - 23:00).
    - **Eje Horizontal**: Días del mes/año.
    - **Color**: Colores más cálidos (rojo) indican valores altos; fríos (azul) indican valores bajos. Es útil para identificar patrones diarios, como la radiación solar máxima al mediodía.
- **Radar de Humedad**: Muestra la distribución de frecuencia de diferentes rangos de humedad (Seco, Normal, Húmedo).

### 4. Carga de Datos (Solo Administradores)
Esta funcionalidad permite alimentar el sistema con nuevos registros.

1.  Navegue a la sección **"Importar Datos"** o **"Carga de Archivos"**.
2.  **Seleccione el archivo**: Haga clic en buscar y elija un archivo CSV válido desde su computadora.
3.  **Seleccione el Nivel**: Indique si el archivo corresponde a datos L0, L1 o L2.
4.  **Confirmar Carga**: Presione el botón "Cargar".
5.  **Verificación**: El sistema mostrará una barra de progreso. Al finalizar, le indicará cuántos registros se insertaron correctamente y si hubo errores en alguna línea.

#### Formato del Archivo CSV
Asegúrese de que el archivo CSV cumpla con la plantilla estándar:
- Separador: Punto y coma (;) o coma (,), según configuración.
- Cabecera: Debe incluir los códigos de columna estándar (ej. `fecha`, `hora`, `temp_aire`, `hum_rel`, `vel_viento`).

### 5. Reportes y Descargas
Puede descargar los datos visualizados para su propio análisis.
1.  Configure los filtros deseados en el Dashboard.
2.  Busque el botón **"Exportar Datos"** o **"Descargar CSV"**.
3.  El navegador descargará un archivo compatible con Excel.

### 6. Soporte
Si encuentra errores en la visualización o problemas al cargar archivos, contacte al administrador del sistema o al departamento técnico del GEAA.
