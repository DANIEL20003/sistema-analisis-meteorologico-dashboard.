<?php
// views/Administrador/VADUser.php
$titulo_pagina = 'Admin: Historial de Descargas';
$pagina_activa = 'admin_datos';
$ruta_base = '../../'; // Esto le dice al header que suba 2 niveles

include('headerAdmin.php'); 

// Inclusión del modelo con ruta absoluta para evitar errores
require_once(__DIR__ . '/../../model/MLoadData.php');

// Instancia y datos
$model = new MLoadData();
$logs = $model->get_logs_descargas();
?>

<style>
    :root { --c-green:#00ff88; --bg:#0b101d; --c-border: rgba(0,255,136,0.3); }
    
    .admin-bg { position:fixed; inset:0; z-index:-1; background:url('../../public/img/chimborazo.jpg') center/cover fixed; }
    .admin-bg::before { content:''; position:absolute; inset:0; background:rgba(10,14,26,0.95); }
    
    .container { max-width:1400px; margin:auto; padding:30px 20px; position:relative; z-index:2; }
    .main-layout { display: flex; gap: 30px; align-items: flex-start; }

    /* FILTROS IZQUIERDA */
    .filters-box { 
        width: 300px; flex-shrink: 0; background: var(--bg); border: 1px solid var(--c-green); 
        border-radius: 12px; padding: 25px; position: sticky; top: 25px; 
        box-shadow: 0 0 20px rgba(0, 255, 136, 0.1);
    }
    .filters-title { color: var(--c-green); font-size: 1.2rem; font-weight: 700; margin-bottom: 20px; border-bottom:1px solid #333; padding-bottom:10px; }
    
    .input-group label { display:block; color:white; margin-bottom:5px; font-size:0.9rem; }
    .input-search { 
        width:100%; padding:10px; background:rgba(255,255,255,0.05); 
        border:1px solid #444; color:white; border-radius:5px; margin-bottom:15px; 
    }
    .input-search:focus { border-color: var(--c-green); outline:none; }

    /* TABLA DERECHA */
    .content-area { flex: 1; min-width: 0; }
    .table-container { 
        background: var(--bg); border: 1px solid var(--c-border); 
        border-radius: 12px; padding: 0; overflow: hidden; 
    }
    .logs-table { width:100%; border-collapse:collapse; color:white; font-size:0.85rem; }
    .logs-table th { 
        background: rgba(0,255,136,0.15); color: var(--c-green); 
        padding: 15px; text-align: left; font-weight: bold; 
        border-bottom: 2px solid var(--c-green); white-space: nowrap;
    }
    .logs-table td { padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; }
    .logs-table tr:hover { background: rgba(255,255,255,0.02); }
    
    .tag-level { background: #333; padding: 2px 6px; border-radius: 4px; border: 1px solid #555; font-size: 0.75rem; }
    .no-data { text-align:center; padding: 30px; color: #888; }

    @media (max-width: 768px) { .main-layout { flex-direction: column; } .filters-box { width: 100%; position: static; } }
</style>

<div class="admin-bg"></div>
<div class="container">
    <div class="main-layout">
        
        <div class="filters-box">
            <h2 class="filters-title">Filtros de Búsqueda</h2>
            
            <div class="input-group">
                <label>Buscar Usuario / Institución:</label>
                <input type="text" id="searchUser" class="input-search" placeholder="Escriba para filtrar...">
            </div>
            
            <div class="input-group">
                <label>Filtrar por Fecha:</label>
                <input type="date" id="searchDate" class="input-search">
            </div>

            <div style="margin-top:20px; font-size:0.8rem; color:#aaa;">
                <i class="fas fa-info-circle"></i> Mostrando últimos registros.
            </div>
        </div>

        <div class="content-area">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3 style="color:var(--c-green); margin:0;">Historial de Descargas</h3>
                <span style="background:var(--c-green); color:black; padding:5px 10px; border-radius:5px; font-weight:bold;">
                    <?php echo count($logs); ?> Registros
                </span>
            </div>

            <div class="table-container">
                <table class="logs-table" id="tableLogs">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Solicitante</th>
                            <th>Institución</th>
                            <th>Motivo</th>
                            <th>Archivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($logs)): ?>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td style="white-space:nowrap;"><?= date('Y-m-d H:i', strtotime($log['fecha_descarga'])) ?></td>
                                <td>
                                    <div style="font-weight:bold; color:white;"><?= $log['nombre_solicitante'] ?></div>
                                    <div style="font-size:0.75rem; color:#888;">ID: <?= $log['cedula_pasaporte'] ?></div>
                                </td>
                                <td><?= $log['institucion'] ?></td>
                                <td><?= $log['motivo'] ?></td>
                                <td>
                                    <span class="tag-level"><?= $log['tipo_nivel'] ?></span> 
                                    <?= $log['tipo_archivo'] ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="no-data">No se encontraron registros de descargas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    // Lógica de Filtro en Tiempo Real (Cliente)
    const searchUser = document.getElementById('searchUser');
    const searchDate = document.getElementById('searchDate');
    const table = document.getElementById('tableLogs');
    const rows = table.getElementsByTagName('tr');

    function filterTable() {
        const term = searchUser.value.toLowerCase();
        const date = searchDate.value;
        
        // Empezamos desde 1 para saltar el header
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            // Obtener texto de las columnas relevantes
            const dateText = row.cells[0]?.textContent || "";
            const userText = row.cells[1]?.textContent.toLowerCase() || "";
            const instText = row.cells[2]?.textContent.toLowerCase() || "";
            
            const matchUser = userText.includes(term) || instText.includes(term);
            const matchDate = !date || dateText.includes(date);
            
            if (matchUser && matchDate) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    }

    searchUser.addEventListener('keyup', filterTable);
    searchDate.addEventListener('change', filterTable);
</script>

<?php include('footerAdmin.php'); ?>