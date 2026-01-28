<?php
$titulo_pagina = 'Tiempo Real - Sistema de Análisis Meteorológico - ESPOCH';
$pagina_activa = 'tiempo-real';

include('../views/Administrador/headerAdmin.php');
?>

<!-- Plotly.js - Optimizado para Big Data -->
<script src="https://cdn.plot.ly/plotly-2.27.0.min.js" charset="utf-8"></script>

<style>
    /* ===== VARIABLES DE COLORES ===== */
    :root {
        --primary-cyan: #00ffff;
        --primary-blue: #0096ff;
        --accent-green: #00ff88;
        --accent-purple: #9d4edd;
        --accent-orange: #ff9500;
        --accent-red: #ff073a;
        --accent-yellow: #ffd60a;
        --text-primary: #ffffff;
        --text-secondary: rgba(255, 255, 255, 0.85);
        --text-muted: rgba(255, 255, 255, 0.6);
        --bg-primary: rgba(10, 14, 26, 0.96);
        --bg-secondary: rgba(18, 24, 43, 0.94);
        --bg-card: rgba(255, 255, 255, 0.05);
        --border-primary: rgba(0, 255, 255, 0.25);
        --border-secondary: rgba(255, 255, 255, 0.08);
        --shadow-glow: 0 8px 32px rgba(0, 255, 255, 0.12);
    }

    /* ===== RESET Y BASE ===== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* ===== FONDO FIJO ===== */
    .dashboard-page-wrapper {
        position: relative;
        min-height: 100vh;
        padding-bottom: 1rem;
    }

    .dashboard-fixed-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('../public/img/chimborazo.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        z-index: -1;
    }

    .dashboard-fixed-background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(10, 14, 26, 0.94) 0%, rgba(18, 24, 43, 0.92) 100%);
        z-index: 1;
    }

    /* ===== CONTENEDOR PRINCIPAL ===== */
    .realtime-container {
        position: relative;
        z-index: 3;
        max-width: 100%;
        margin: 0 auto;
        padding: 0.8rem 1rem;
    }

    /* ===== HEADER COMPACTO Y PROFESIONAL ===== */
    .realtime-header {
        background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
        border: 1px solid var(--border-primary);
        border-radius: 10px;
        padding: 1rem 1.5rem;
        backdrop-filter: blur(25px);
        box-shadow: var(--shadow-glow), 0 4px 20px rgba(0, 0, 0, 0.4);
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: fadeInDown 0.5s ease;
    }

    .header-info h1 {
        font-size: 1.1rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--accent-green), var(--primary-cyan));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0 0 0.2rem 0;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .header-info p {
        font-size: 0.7rem;
        color: var(--text-secondary);
        margin: 0;
        letter-spacing: 0.3px;
    }

    .header-status {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.3rem;
    }

    .status-badge {
        padding: 0.35rem 0.9rem;
        border-radius: 15px;
        font-size: 0.65rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        box-shadow: 0 3px 12px rgba(0, 255, 136, 0.3);
    }

    .pulse-dot {
        width: 7px;
        height: 7px;
        background: #ffffff;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    .last-update-info {
        font-size: 0.65rem;
        color: var(--text-muted);
        text-align: right;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .last-update-time {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--accent-green);
        margin-top: 0.15rem;
        font-family: 'Courier New', monospace;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(1.4); }
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ===== LOADING COMPACTO ===== */
    .loading-container {
        text-align: center;
        padding: 3rem 1.5rem;
        display: none;
    }

    .loading-container.active {
        display: block;
    }

    .spinner {
        width: 45px;
        height: 45px;
        border: 4px solid var(--border-secondary);
        border-top-color: var(--primary-cyan);
        border-radius: 50%;
        animation: spin 0.9s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loading-text {
        font-size: 0.85rem;
        color: var(--text-secondary);
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    /* ===== GRID PROFESIONAL 2 COLUMNAS ===== */
    .charts-dynamic-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        animation: fadeInUp 0.6s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ===== TARJETA DE VARIABLE PROFESIONAL ===== */
    .variable-card {
        background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
        border: 1px solid var(--border-primary);
        border-radius: 12px;
        padding: 1rem;
        backdrop-filter: blur(25px);
        box-shadow: var(--shadow-glow), 0 4px 20px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        opacity: 0;
        animation: cardAppear 0.5s ease forwards;
        position: relative;
        overflow: hidden;
    }

    .variable-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--accent-green), var(--primary-cyan), var(--accent-purple));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .variable-card:hover {
        border-color: var(--accent-green);
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(0, 255, 136, 0.25), var(--shadow-glow);
    }

    .variable-card:hover::before {
        opacity: 1;
    }

    @keyframes cardAppear {
        to {
            opacity: 1;
        }
    }

    /* ===== HEADER DE VARIABLE COMPACTO ===== */
    .variable-header {
        margin-bottom: 0.8rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .variable-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.4rem;
    }

    .variable-name {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 1px;
        line-height: 1.2;
    }

    .variable-unit {
        padding: 0.25rem 0.6rem;
        border-radius: 5px;
        font-size: 0.65rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        color: #0a0e1a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .variable-description {
        font-size: 0.68rem;
        color: var(--text-secondary);
        line-height: 1.3;
        margin: 0.3rem 0 0 0;
    }

    .variable-code {
        font-size: 0.62rem;
        color: var(--text-muted);
        font-family: 'Courier New', monospace;
        background: rgba(0, 255, 255, 0.08);
        padding: 0.15rem 0.4rem;
        border-radius: 3px;
        display: inline-block;
        margin-top: 0.3rem;
        letter-spacing: 0.3px;
    }

    /* ===== CONTENEDOR DE GRÁFICA OPTIMIZADO ===== */
    .chart-wrapper {
        background: rgba(0, 0, 0, 0.35);
        border-radius: 8px;
        padding: 0.8rem;
        min-height: 380px;
        position: relative;
        border: 1px solid var(--border-secondary);
    }

    .chart-container {
        height: 350px;
        width: 100%;
    }

    /* ===== ESTACIONES BADGES COMPACTOS ===== */
    .stations-info {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: 0.8rem;
        padding-top: 0.8rem;
        border-top: 1px solid var(--border-secondary);
    }

    .station-badge {
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        font-size: 0.65rem;
        font-weight: 600;
        background: var(--bg-card);
        color: var(--text-secondary);
        border: 1px solid var(--border-secondary);
        display: flex;
        align-items: center;
        gap: 0.3rem;
        transition: all 0.25s ease;
        letter-spacing: 0.3px;
    }

    .station-badge:hover {
        background: rgba(0, 255, 136, 0.15);
        border-color: var(--accent-green);
        color: var(--accent-green);
        transform: translateY(-1px);
    }

    .station-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--accent-green);
    }

    /* ===== MENSAJE SIN DATOS ===== */
    .no-data-message {
        text-align: center;
        padding: 2.5rem 1.5rem;
        color: var(--text-muted);
    }

    .no-data-message i {
        font-size: 2.5rem;
        color: var(--text-muted);
        margin-bottom: 0.8rem;
        opacity: 0.5;
    }

    .no-data-message p {
        font-size: 0.9rem;
        margin: 0;
    }

    /* ===== ESTADO VACÍO ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
        border: 2px dashed var(--border-primary);
        border-radius: 12px;
        margin: 1.5rem 0;
        backdrop-filter: blur(25px);
    }

    .empty-state i {
        font-size: 3rem;
        color: var(--primary-cyan);
        margin-bottom: 1rem;
        opacity: 0.6;
    }

    .empty-state h3 {
        font-size: 1.2rem;
        color: var(--text-primary);
        margin: 0 0 0.8rem 0;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .empty-state p {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin: 0;
    }

    /* ===== SCROLLBAR PROFESIONAL ===== */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--bg-secondary);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));
        border-radius: 4px;
        transition: background 0.3s ease;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
    }

    /* ===== RESPONSIVO ===== */
    @media (max-width: 1600px) {
        .charts-dynamic-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.9rem;
        }
    }

    @media (max-width: 1200px) {
        .charts-dynamic-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .realtime-container {
            padding: 0.6rem 0.8rem;
        }

        .realtime-header {
            flex-direction: column;
            text-align: center;
            padding: 1rem;
            gap: 0.8rem;
        }

        .header-info h1 {
            font-size: 1rem;
        }

        .header-status {
            align-items: center;
        }

        .variable-title-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.4rem;
        }

        .chart-wrapper {
            min-height: 300px;
        }

        .chart-container {
            height: 280px;
        }
    }

    /* ===== ANIMACIÓN ESCALONADA DE TARJETAS ===== */
    .variable-card:nth-child(1) { animation-delay: 0.05s; }
    .variable-card:nth-child(2) { animation-delay: 0.10s; }
    .variable-card:nth-child(3) { animation-delay: 0.15s; }
    .variable-card:nth-child(4) { animation-delay: 0.20s; }
    .variable-card:nth-child(5) { animation-delay: 0.25s; }
    .variable-card:nth-child(6) { animation-delay: 0.30s; }
    .variable-card:nth-child(7) { animation-delay: 0.35s; }
    .variable-card:nth-child(8) { animation-delay: 0.40s; }
    .variable-card:nth-child(9) { animation-delay: 0.45s; }
    .variable-card:nth-child(10) { animation-delay: 0.50s; }
</style>

<!-- Fondo fijo -->
<div class="dashboard-fixed-background"></div>

<div class="dashboard-page-wrapper">
    <div class="realtime-container">
        
        <!-- ===== HEADER PROFESIONAL COMPACTO ===== -->
        <header class="realtime-header">
            <div class="header-info">
                <h1>🔴 MONITOREO TIEMPO REAL L2</h1>
                <p>Visualización dinámica de variables meteorológicas activas (24 horas)</p>
            </div>
            <div class="header-status">
                <div class="status-badge">
                    <span class="pulse-dot"></span>
                    <span>TIEMPO REAL ACTIVO</span>
                </div>
                <div class="last-update-info">
                    ÚLTIMA ACTUALIZACIÓN
                </div>
                <div class="last-update-time" id="lastUpdateTime">--:--:--</div>
            </div>
        </header>

        <!-- ===== LOADING SPINNER ===== -->
        <div class="loading-container active" id="loadingContainer">
            <div class="spinner"></div>
            <div class="loading-text">Cargando variables y datos en tiempo real...</div>
        </div>

        <!-- ===== ESTADO VACÍO ===== -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <i class="fas fa-database"></i>
            <h3>No hay datos disponibles</h3>
            <p>No se encontraron registros de tiempo real en las últimas 24 horas</p>
        </div>

        <!-- ===== GRID DINÁMICO DE VARIABLES ===== -->
        <div class="charts-dynamic-grid" id="chartsGrid" style="display: none;">
            <!-- Las tarjetas se generan dinámicamente aquí -->
        </div>

    </div>
</div>

<script>
    // ===== VARIABLES GLOBALES =====
    let refreshInterval = null;
    let variablesData = {};
    let metadatos = [];
    
    // Colores profesionales para las gráficas
    const chartColors = [
        '#ff073a', '#00ff88', '#0096ff', '#ffd60a', 
        '#9d4edd', '#00ffff', '#ff9500', '#ff006e',
        '#00e5ff', '#76ff03', '#ff1744', '#ffea00'
    ];

    // ===== CONFIGURACIÓN PROFESIONAL DE PLOTLY =====
    const plotlyConfig = {
        responsive: true,
        displayModeBar: false,
        displaylogo: false,
        scrollZoom: false
    };

    const getPlotlyLayout = (titulo, unidad) => ({
        paper_bgcolor: 'rgba(0,0,0,0)',
        plot_bgcolor: 'rgba(0,0,0,0)',
        font: { 
            color: '#ffffff', 
            size: 10,
            family: 'system-ui, -apple-system, sans-serif'
        },
        margin: { l: 55, r: 25, t: 15, b: 45 },
        xaxis: {
            title: {
                text: 'Tiempo (24H)',
                font: { size: 10, color: 'rgba(255,255,255,0.7)' }
            },
            gridcolor: 'rgba(255,255,255,0.08)',
            showgrid: true,
            zeroline: false,
            color: 'rgba(255,255,255,0.8)',
            tickfont: { size: 9 },
            tickformat: '%H:%M'
        },
        yaxis: {
            title: {
                text: unidad ? `${unidad}` : titulo,
                font: { size: 10, color: 'rgba(255,255,255,0.7)' }
            },
            gridcolor: 'rgba(255,255,255,0.08)',
            showgrid: true,
            zeroline: false,
            color: 'rgba(255,255,255,0.8)',
            tickfont: { size: 9 }
        },
        hovermode: 'x unified',
        showlegend: true,
        legend: {
            orientation: 'h',
            y: -0.18,
            x: 0.5,
            xanchor: 'center',
            font: { size: 9 },
            bgcolor: 'rgba(0,0,0,0.3)',
            bordercolor: 'rgba(255,255,255,0.1)',
            borderwidth: 1
        },
        hoverlabel: {
            bgcolor: 'rgba(10,14,26,0.95)',
            bordercolor: 'rgba(0,255,255,0.5)',
            font: { size: 10, color: '#ffffff' }
        }
    });

    // ===== INICIALIZACIÓN =====
    document.addEventListener('DOMContentLoaded', async function() {
        console.log('🚀 Inicializando Dashboard Tiempo Real Profesional...');
        
        await cargarDatosIniciales();
        
        // Auto-actualización cada 60 segundos
        refreshInterval = setInterval(actualizarDatos, 60000);
        
        console.log('✅ Dashboard inicializado');
    });

    // ===== CARGAR DATOS INICIALES =====
    async function cargarDatosIniciales() {
        try {
            showLoading(true);

            const response = await fetch('../controller/CTiempoReal.php?action=obtenerDatosDinamicos');
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const data = await response.json();
            
            console.log('📊 Datos recibidos:', data);

            if (!data.exito) {
                throw new Error(data.mensaje || 'Error al obtener datos');
            }

            metadatos = data.metadatos || [];
            variablesData = data.variables || {};

            if (data.ultima_actualizacion) {
                document.getElementById('lastUpdateTime').textContent = 
                    formatearFechaHora(data.ultima_actualizacion);
            }

            if (metadatos.length === 0 || Object.keys(variablesData).length === 0) {
                mostrarEstadoVacio();
                return;
            }

            generarTarjetasVariables();
            
            showLoading(false);
            document.getElementById('chartsGrid').style.display = 'grid';

        } catch (error) {
            console.error('❌ Error:', error);
            showLoading(false);
            mostrarEstadoVacio();
            mostrarNotificacion('Error al cargar datos: ' + error.message, 'error');
        }
    }

    // ===== GENERAR TARJETAS DINÁMICAMENTE =====
    function generarTarjetasVariables() {
        const grid = document.getElementById('chartsGrid');
        grid.innerHTML = '';

        let colorIndex = 0;

        metadatos.forEach((metadato, index) => {
            const codigoColumna = metadato.codigo_columna;
            const datosVariable = variablesData[codigoColumna];

            if (!datosVariable || datosVariable.length === 0) {
                console.log(`⚠️ Sin datos para: ${codigoColumna}`);
                return;
            }

            const color = chartColors[colorIndex % chartColors.length];
            colorIndex++;

            const estaciones = [...new Set(datosVariable.map(d => d.estacion))];

            const card = document.createElement('div');
            card.className = 'variable-card';
            card.innerHTML = `
                <div class="variable-header">
                    <div class="variable-title-row">
                        <h3 class="variable-name">${metadato.nombre_corto}</h3>
                        ${metadato.unidad ? `<span class="variable-unit">${metadato.unidad}</span>` : ''}
                    </div>
                    ${metadato.descripcion ? `<p class="variable-description">${metadato.descripcion}</p>` : ''}
                    <div class="variable-code">Código: ${codigoColumna}</div>
                </div>
                <div class="chart-wrapper">
                    <div class="chart-container" id="chart_${codigoColumna.replace(/[^a-zA-Z0-9]/g, '_')}"></div>
                </div>
                <div class="stations-info">
                    ${estaciones.map(est => `
                        <div class="station-badge">
                            <span class="station-dot"></span>
                            <span>${est}</span>
                        </div>
                    `).join('')}
                </div>
            `;

            grid.appendChild(card);

            setTimeout(() => {
                renderizarGrafica(
                    `chart_${codigoColumna.replace(/[^a-zA-Z0-9]/g, '_')}`,
                    datosVariable,
                    metadato,
                    color
                );
            }, 30 * index);
        });
    }

    // ===== RENDERIZAR GRÁFICA CON PLOTLY =====
    function renderizarGrafica(elementId, datos, metadato, color) {
        const elemento = document.getElementById(elementId);
        if (!elemento) {
            console.error(`❌ Elemento no encontrado: ${elementId}`);
            return;
        }

        const datosPorEstacion = {};
        datos.forEach(d => {
            if (!datosPorEstacion[d.estacion]) {
                datosPorEstacion[d.estacion] = [];
            }
            datosPorEstacion[d.estacion].push(d);
        });

        const traces = [];
        let colorIndex = 0;

        for (const [estacion, datosEst] of Object.entries(datosPorEstacion)) {
            datosEst.sort((a, b) => new Date(a.fecha_hora) - new Date(b.fecha_hora));

            traces.push({
                x: datosEst.map(d => d.fecha_hora),
                y: datosEst.map(d => parseFloat(d.valor)),
                type: 'scatter',
                mode: 'lines',
                name: estacion,
                line: {
                    color: chartColors[colorIndex % chartColors.length],
                    width: 2,
                    shape: 'spline'
                },
                fill: 'tozeroy',
                fillcolor: `${chartColors[colorIndex % chartColors.length]}15`,
                hovertemplate: `<b>${estacion}</b><br>` +
                              `%{y:.2f} ${metadato.unidad || ''}<br>` +
                              `%{x|%H:%M}<extra></extra>`
            });

            colorIndex++;
        }

        const layout = getPlotlyLayout(metadato.nombre_corto, metadato.unidad);

        Plotly.newPlot(elementId, traces, layout, plotlyConfig);
    }

    // ===== ACTUALIZAR DATOS =====
    async function actualizarDatos() {
        console.log('🔄 Actualizando datos...');
        try {
            const response = await fetch('../controller/CTiempoReal.php?action=obtenerDatosDinamicos');
            const data = await response.json();

            if (data.exito) {
                variablesData = data.variables || {};
                metadatos = data.metadatos || [];

                if (data.ultima_actualizacion) {
                    document.getElementById('lastUpdateTime').textContent = 
                        formatearFechaHora(data.ultima_actualizacion);
                }

                if (metadatos.length > 0 && Object.keys(variablesData).length > 0) {
                    generarTarjetasVariables();
                    console.log('✅ Datos actualizados');
                }
            }
        } catch (error) {
            console.error('❌ Error al actualizar:', error);
        }
    }

    // ===== FUNCIONES AUXILIARES =====
    function showLoading(mostrar) {
        document.getElementById('loadingContainer').classList.toggle('active', mostrar);
    }

    function mostrarEstadoVacio() {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('chartsGrid').style.display = 'none';
        showLoading(false);
    }

    function formatearFechaHora(fechaHora) {
        const fecha = new Date(fechaHora);
        return fecha.toLocaleString('es-EC', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
    }

    function mostrarNotificacion(mensaje, tipo = 'info') {
        const colores = {
            success: 'linear-gradient(135deg, #00ff88, #00ffff)',
            error: 'linear-gradient(135deg, #ff073a, #ff9500)',
            info: 'linear-gradient(135deg, #0096ff, #9d4edd)'
        };

        const notif = document.createElement('div');
        notif.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            background: ${colores[tipo]};
            color: ${tipo === 'info' ? '#ffffff' : '#0a0e1a'};
            padding: 0.8rem 1.3rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            letter-spacing: 0.3px;
        `;
        notif.textContent = mensaje;
        document.body.appendChild(notif);

        setTimeout(() => notif.style.transform = 'translateX(0)', 100);
        setTimeout(() => {
            notif.style.transform = 'translateX(400px)';
            setTimeout(() => document.body.removeChild(notif), 300);
        }, 4000);
    }

    // Limpiar interval al salir
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) clearInterval(refreshInterval);
    });
</script>

<?php include('../views/Administrador/footerAdmin.php'); ?>