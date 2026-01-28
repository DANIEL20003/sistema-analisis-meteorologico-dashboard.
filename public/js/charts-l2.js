/**
 * ================================================================
 * GRÁFICAS L2 - MÓDULO SEPARADO
 * Sistema de visualización de datos climatológicos nivel 2
 * ================================================================
 */

(function () {
    'use strict';

    // ================================================================
    // CONFIGURACIÓN
    // ================================================================
    const CONFIG = {
        baseUrl: '../controller/CDashboard.php',
        plotlyConfig: {
            responsive: true,
            displayModeBar: true,
            displaylogo: false
        },
        plotlyLayout: {
            paper_bgcolor: 'rgba(10,14,26,0.95)',
            plot_bgcolor: 'rgba(10,14,26,0.8)',
            font: { color: '#ffffff' },
            margin: { l: 60, r: 60, t: 80, b: 80 }
        }
    };

    // ================================================================
    // FUNCIÓN PRINCIPAL: CAMBIAR NIVEL
    // ================================================================
    window.switchLevel = function (level) {
        console.log(`🔄 Cambiando a nivel ${level}`);

        // Ocultar todas las secciones
        document.querySelectorAll('.charts-section').forEach(section => {
            section.style.display = 'none';
        });

        // Mostrar sección seleccionada
        const targetSection = document.getElementById(`charts${level}`);
        if (targetSection) {
            targetSection.style.display = 'block';
        }

        // Actualizar botones
        document.querySelectorAll('.level-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        const activeButton = document.querySelector(`.level-btn[data-level="${level}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }

        // Cargar gráficas de L2
        if (level === 'L2') {
            const estacionSelect = document.getElementById('filterEstacion');
            const fechaInicio = document.getElementById('filterFechaInicio');

            if (!estacionSelect || !fechaInicio) {
                console.warn('⚠️ Selectores de filtros no encontrados');
                return;
            }

            const anio = new Date(fechaInicio.value).getFullYear() || 2024;
            const mes = new Date(fechaInicio.value).getMonth() + 1 || 1;

            if (!fechaInicio.value) {
                console.warn('⚠️ Debes seleccionar fecha primero');
                return;
            }

            console.log('📊 Cargando gráficas L2...', { anio, mes });
            loadChartsL2(1, anio, mes); // ID estación 1 hardcodeado
        }
    };

    // ================================================================
    // ORQUESTADOR: CARGAR TODAS LAS GRÁFICAS L2
    // ================================================================
    async function loadChartsL2(estacionId, anio, mes) {
        console.log(`🔄 Cargando gráficas L2 para estación ${estacionId}, año ${anio}, mes ${mes}`);

        try {
            await Promise.all([
                loadChartTendenciaL2(estacionId, anio, mes),
                loadChartClimogramaL2(estacionId, anio)
            ]);
            console.log('✅ Todas las gráficas L2 cargadas');
        } catch (error) {
            console.error('❌ Error al cargar gráficas L2:', error);
        }
    }

    // ================================================================
    // CARGAR TENDENCIA ANUAL
    // ================================================================
    async function loadChartTendenciaL2(estacionId, anio, mes) {
        const url = `${CONFIG.baseUrl}?action=get_chart_l2_tendencia_anual&id_estacion=${estacionId}&anio=${anio}&mes=${mes}`;

        try {
            console.log('📊 Cargando Tendencia Anual L2...');
            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Tendencia Anual - Respuesta:', result);

            if (result.success && result.data && result.data.labels && result.data.labels.length > 0) {
                console.log('✅ Datos válidos, creando gráfica...');
                createChartTendenciaL2(result.data);
            } else {
                console.warn('⚠️ Sin datos para Tendencia Anual L2');
                showChartMessage('chartTendenciaL2', 'Sin datos disponibles para este período');
            }
        } catch (error) {
            console.error('❌ Error Tendencia Anual:', error);
            showChartMessage('chartTendenciaL2', 'Error al cargar datos');
        }
    }

    // ================================================================
    // CARGAR CLIMOGRAMA
    // ================================================================
    async function loadChartClimogramaL2(estacionId, anio) {
        const url = `${CONFIG.baseUrl}?action=get_chart_l2_climograma&id_estacion=${estacionId}&anio=${anio}`;

        try {
            console.log('📊 Cargando Climograma L2...');
            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Climograma - Respuesta:', result);

            if (result.success && result.data && result.data.labels && result.data.labels.length > 0) {
                console.log('✅ Datos válidos, creando gráfica...');
                createChartClimogramaL2(result.data);
            } else {
                console.warn('⚠️ Sin datos para Climograma L2');
                showChartMessage('chartClimogramaL2', 'Sin datos disponibles para este período');
            }
        } catch (error) {
            console.error('❌ Error Climograma:', error);
            showChartMessage('chartClimogramaL2', 'Error al cargar datos');
        }
    }

    // ================================================================
    // RENDERIZAR TENDENCIA ANUAL
    // ================================================================
    function createChartTendenciaL2(datos) {
        console.log('🔥 Renderizando Tendencia Anual...', datos);

        const chartDiv = document.getElementById('chartTendenciaL2');
        if (!chartDiv) {
            console.error('❌ chartTendenciaL2 NO EXISTE');
            return;
        }

        // Limpiar y forzar estilos
        chartDiv.innerHTML = '';
        chartDiv.setAttribute('style', 'width:100% !important; height:400px !important; display:block !important;');

        // Preparar datos
        const traces = [{
            x: datos.labels,
            y: datos.temperatura_promedio,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'Temperatura',
            line: { color: '#ff6b6b', width: 3 },
            marker: { size: 6 }
        }];

        if (datos.viento_promedio && datos.viento_promedio.length > 0) {
            traces.push({
                x: datos.labels,
                y: datos.viento_promedio,
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Viento',
                yaxis: 'y2',
                line: { color: '#4ecdc4', width: 3 },
                marker: { size: 6 }
            });
        }

        // Layout
        const layout = {
            ...CONFIG.plotlyLayout,
            title: { text: 'Tendencia Anual L2', font: { color: '#ffffff', size: 16 } },
            xaxis: { title: 'Período', color: '#ffffff', gridcolor: 'rgba(255,255,255,0.1)' },
            yaxis: { title: 'Temperatura (°C)', color: '#ffffff', gridcolor: 'rgba(255,255,255,0.1)' },
            yaxis2: { title: 'Viento (m/s)', overlaying: 'y', side: 'right', color: '#ffffff' }
        };

        // Renderizar
        Plotly.newPlot(chartDiv, traces, layout, CONFIG.plotlyConfig).then(() => {
            console.log('✅ Tendencia Anual renderizada');
            setTimeout(() => Plotly.Plots.resize(chartDiv), 200);
        }).catch(err => console.error('❌ Error Plotly Tendencia:', err));
    }

    // ================================================================
    // RENDERIZAR CLIMOGRAMA
    // ================================================================
    function createChartClimogramaL2(datos) {
        console.log('🔥 Renderizando Climograma...', datos);

        const chartDiv = document.getElementById('chartClimogramaL2');
        if (!chartDiv) {
            console.error('❌ chartClimogramaL2 NO EXISTE');
            return;
        }

        // Limpiar y forzar estilos
        chartDiv.innerHTML = '';
        chartDiv.setAttribute('style', 'width:100% !important; height:400px !important; display:block !important;');

        // Preparar datos
        const traces = [{
            x: datos.labels,
            y: datos.temperatura_promedio,
            type: 'bar',
            name: 'Temperatura',
            marker: { color: '#ff6b6b' }
        }];

        if (datos.viento_promedio && datos.viento_promedio.length > 0) {
            traces.push({
                x: datos.labels,
                y: datos.viento_promedio,
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Viento',
                yaxis: 'y2',
                line: { color: '#4ecdc4', width: 3 },
                marker: { size: 6 }
            });
        }

        // Layout
        const layout = {
            ...CONFIG.plotlyLayout,
            title: { text: 'Climograma L2', font: { color: '#ffffff', size: 16 } },
            xaxis: { title: 'Período', color: '#ffffff', gridcolor: 'rgba(255,255,255,0.1)' },
            yaxis: { title: 'Temperatura (°C)', color: '#ffffff', gridcolor: 'rgba(255,255,255,0.1)' },
            yaxis2: { title: 'Viento (m/s)', overlaying: 'y', side: 'right', color: '#ffffff' }
        };

        // Renderizar
        Plotly.newPlot(chartDiv, traces, layout, CONFIG.plotlyConfig).then(() => {
            console.log('✅ Climograma renderizado');
            setTimeout(() => Plotly.Plots.resize(chartDiv), 200);
        }).catch(err => console.error('❌ Error Plotly Climograma:', err));
    }

    // ================================================================
    // ACTUALIZAR FILTROS PARA L2
    // ================================================================
    async function updateFiltersForL2() {
        try {
            console.log('📅 Obteniendo rangos disponibles para L2...');

            const response = await fetch(`${CONFIG.baseUrl}?action=get_l2_available_dates`);
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                console.log('✅ Rangos L2 obtenidos:', result.data);

                // Encontrar el select de año (puede tener diferentes IDs)
                const yearSelect = document.querySelector('select[name="anio"]') ||
                    document.getElementById('filterYear') ||
                    document.querySelector('.filters-grid select:first-of-type');

                if (yearSelect) {
                    // Limpiar opciones actuales
                    yearSelect.innerHTML = '<option value="">Seleccione año...</option>';

                    // Agregar años disponibles de L2
                    result.data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.anio;
                        option.textContent = item.anio;
                        yearSelect.appendChild(option);
                    });

                    // Seleccionar el año más reciente por defecto
                    if (result.data.length > 0) {
                        yearSelect.value = result.data[0].anio;
                    }

                    console.log('✅ Selector de año actualizado con datos L2');
                } else {
                    console.warn('⚠️ No se encontró selector de año');
                }
            } else {
                console.warn('⚠️ No hay datos L2 disponibles');
            }
        } catch (error) {
            console.error('❌ Error al actualizar filtros L2:', error);
        }
    }

    // ================================================================
    // MOSTRAR MENSAJE EN GRÁFICA
    // ================================================================
    function showChartMessage(chartId, message) {
        const chartDiv = document.getElementById(chartId);
        if (chartDiv) {
            chartDiv.innerHTML = `
                <div style="display:flex; align-items:center; justify-content:center; height:100%; color:#fff; font-size:14px;">
                    <i class="fas fa-info-circle" style="margin-right:10px;"></i>
                    ${message}
                </div>
            `;
        }
    }

    // ================================================================
    // EXPONER FUNCIONES NECESARIAS
    // ================================================================
    window.loadChartsL2 = loadChartsL2;

    console.log('✅ Módulo L2 cargado correctamente');
})();
