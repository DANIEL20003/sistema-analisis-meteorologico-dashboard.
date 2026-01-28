<?php
// Configurar ruta base para navegación correcta
$ruta_base = "../../";

// Título de la página
$titulo_pagina = "Listado de Estaciones";
$pagina_activa = "estaciones";
// Configurar breadcrumb
$breadcrumb = '<a href="../../index.php"><i class="fas fa-home"></i> Inicio</a>
               <span class="breadcrumb-separator">/</span>
               <span>Estaciones</span>';

// Incluir el header
include('../includes/header.php');
?>

<!-- Contenido específico de esta página -->
<div class="container">
    <section class="funcionalidades">
        <h2 class="titulo-seccion">
            <i class="fas fa-map-marker-alt"></i> Estaciones Meteorológicas
        </h2>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <span>Aquí se mostrarán todas las estaciones meteorológicas registradas en el sistema.</span>
        </div>

        <!-- Botón para agregar nueva estación -->
        <div class="mb-30">
            <a href="agregar.php" class="btn-card">
                <i class="fas fa-plus"></i> Agregar Nueva Estación
            </a>
        </div>

        <!-- Tabla de estaciones -->
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--color-verde-espoch); color: white;">
                    <tr>
                        <th style="padding: 12px; text-align: left;">ID</th>
                        <th style="padding: 12px; text-align: left;">Nombre</th>
                        <th style="padding: 12px; text-align: center;">Latitud</th>
                        <th style="padding: 12px; text-align: center;">Longitud</th>
                        <th style="padding: 12px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = pg_query($conn, "SELECT * FROM estaciones ORDER BY id");
                    while($estacion = pg_fetch_assoc($query)):
                    ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 12px;"><?php echo $estacion['id']; ?></td>
                        <td style="padding: 12px;"><?php echo $estacion['nombre']; ?></td>
                        <td style="padding: 12px; text-align: center;"><?php echo $estacion['latitud']; ?></td>
                        <td style="padding: 12px; text-align: center;"><?php echo $estacion['longitud']; ?></td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="editar.php?id=<?php echo $estacion['id']; ?>" 
                               style="color: var(--color-verde-espoch); margin-right: 10px;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="eliminar.php?id=<?php echo $estacion['id']; ?>" 
                               style="color: var(--color-rojo-espoch);"
                               onclick="return confirm('¿Está seguro de eliminar esta estación?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php
// Incluir el footer
include('../includes/footer.php');
?>