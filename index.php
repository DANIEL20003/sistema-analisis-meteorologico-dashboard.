<?php
$titulo_pagina = 'Inicio - Sistema de Análisis Meteorológico - ESPOCH';
$pagina_activa = 'inicio';
$ruta_base = './';

include('views/includes/header.php');
?>

<style>
    .index-page-wrapper {
        position: relative;
        padding-top: 2rem;
    }

    .index-fixed-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('public/img/chimborazo 1.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        z-index: -1;
    }

    .index-fixed-background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(10, 14, 26, 0.80) 0%, rgba(18, 24, 43, 0.75) 100%);
        z-index: 1;
    }

    /* Hero Section Compacto */
    .hero-container {
        position: relative;
        z-index: 3;
        text-align: center;
        max-width: 900px;
        margin: 0 auto 3rem;
        padding: 0 1rem;
        animation: fadeInUp 0.8s ease;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 1rem;
        background: rgba(0, 255, 255, 0.15);
        border: 1px solid rgba(0, 255, 255, 0.4);
        border-radius: 50px;
        color: var(--neon-cyan);
        font-size: 0.7rem;
        font-weight: 600;
        margin-bottom: 1rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        backdrop-filter: blur(10px);
    }

    .hero-title {
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 0.8rem;
        background: linear-gradient(135deg, #00ffff 0%, #0096ff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-description {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.5;
        margin-bottom: 0;
    }

    /* Main Content */
    .main-content-section {
        position: relative;
        z-index: 3;
        padding: 0 1rem 3rem;
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Project Header */
    .project-header-card {
        background: rgba(10, 14, 26, 0.85);
        border: 1px solid rgba(0, 255, 255, 0.3);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        backdrop-filter: blur(20px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    }

    .project-main-title {
        font-size: 1.3rem;
        font-weight: 700;
        background: linear-gradient(135deg, #00ffff 0%, #0096ff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1.5rem;
        line-height: 1.4;
        text-align: center;
    }

    .project-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .meta-item {
        text-align: center;
        padding: 0.8rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(0, 255, 255, 0.2);
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .meta-item:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: #00ffff;
        transform: translateY(-2px);
    }

    .meta-item i {
        font-size: 1.2rem;
        color: #00ffff;
        margin-bottom: 0.4rem;
    }

    .meta-label {
        font-size: 0.65rem;
        color: rgba(255, 255, 255, 0.6);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
    }

    .meta-value {
        font-size: 0.85rem;
        color: #ffffff;
        font-weight: 600;
    }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .content-card {
        background: rgba(10, 14, 26, 0.85);
        border: 1px solid rgba(0, 255, 255, 0.3);
        border-radius: 16px;
        padding: 1.8rem;
        backdrop-filter: blur(20px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #00ffff;
        margin-bottom: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding-bottom: 0.8rem;
        border-bottom: 2px solid rgba(0, 255, 255, 0.3);
    }

    .section-title i {
        font-size: 1rem;
    }

    .objective-text {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.6;
        text-align: justify;
    }

    .objectives-list {
        list-style: none;
        padding: 0;
    }

    .objectives-list li {
        background: rgba(255, 255, 255, 0.05);
        border-left: 3px solid #00ffff;
        padding: 0.9rem;
        margin-bottom: 0.8rem;
        border-radius: 0 8px 8px 0;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.8rem;
        line-height: 1.5;
        transition: all 0.3s ease;
    }

    .objectives-list li:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateX(8px);
    }

    .institutions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.8rem;
    }

    .institution-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(0, 255, 255, 0.2);
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .institution-card:hover {
        border-color: #00ffff;
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0, 255, 255, 0.3);
    }

    .institution-icon {
        font-size: 1.5rem;
        color: #00ffff;
        margin-bottom: 0.5rem;
    }

    .institution-name {
        font-size: 0.75rem;
        color: #ffffff;
        font-weight: 600;
        line-height: 1.3;
    }

    .info-text {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.6;
        text-align: justify;
        margin-bottom: 0.8rem;
    }

    .info-text:last-child {
        margin-bottom: 0;
    }

    .highlight-text {
        color: #00ffff;
        font-weight: 600;
    }

    /* Carousel Container - ALTURA REDUCIDA */
    .carousel-container {
        background: rgba(10, 14, 26, 0.9);
        border: 2px solid rgba(0, 255, 255, 0.4);
        border-radius: 16px;
        padding: 1.5rem;
        backdrop-filter: blur(20px);
        box-shadow: 0 8px 30px rgba(0, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
    }

    .carousel-header {
        text-align: center;
        margin-bottom: 1.2rem;
    }

    .carousel-title {
        font-size: 1rem;
        font-weight: 700;
        background: linear-gradient(135deg, #00ffff 0%, #0096ff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }

    .carousel-subtitle {
        font-size: 0.65rem;
        color: rgba(255, 255, 255, 0.6);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* ALTURA REDUCIDA A 320PX */
    .carousel-content {
        position: relative;
        height: 320px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    }

    .carousel-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 0.6s ease-in-out;
        /* REMOVIDO: transform: scale(1.05) para eliminar zoom */
    }

    .carousel-slide.active {
        opacity: 1;
        /* REMOVIDO: transform: scale(1) para eliminar zoom */
    }

    /* Overlay más sutil */
    .carousel-slide::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            to bottom,
            rgba(0, 0, 0, 0.03) 0%,
            rgba(0, 0, 0, 0.15) 100%
        );
        z-index: 1;
        pointer-events: none;
    }

    /* Imágenes sin zoom, con filtros mejorados */
    .carousel-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        border-radius: 12px;
        background: rgba(10, 14, 26, 0.95);
        /* Filtros mejorados para mejor calidad visual */
        filter: brightness(1.05) contrast(1.15) saturate(1.2) sharpen(1.1);
    }

    /* Ajuste específico para fotografías de estaciones */
    .content-grid:nth-child(1) .carousel-image {
        object-fit: contain;
        filter: brightness(1.08) contrast(1.18) saturate(1.25) sharpen(1.15);
    }

    /* Ajuste específico para mapas */
    .content-grid:nth-child(2) .carousel-image {
        object-fit: contain;
        filter: brightness(1.1) contrast(1.2) saturate(1.15) hue-rotate(-2deg);
    }

    .carousel-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(10, 14, 26, 0.95));
        padding: 1.2rem 1rem 0.8rem;
        border-radius: 0 0 12px 12px;
        z-index: 2;
    }

    .carousel-info {
        text-align: center;
    }

    .carousel-station-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: #00ffff;
        margin-bottom: 0.3rem;
        text-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
    }

    .carousel-station-location {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.8);
    }

    .carousel-indicators {
        display: flex;
        justify-content: center;
        gap: 0.4rem;
        margin-top: 1rem;
    }

    .carousel-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .carousel-indicator.active {
        background: #00ffff;
        border-color: #00ffff;
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.6);
        transform: scale(1.3);
    }

    .carousel-indicator:hover {
        background: rgba(0, 255, 255, 0.6);
        transform: scale(1.2);
    }

    .carousel-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 255, 255, 0.2);
        border: 2px solid rgba(0, 255, 255, 0.4);
        color: #00ffff;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        z-index: 3;
    }

    .carousel-nav:hover {
        background: rgba(0, 255, 255, 0.4);
        border-color: #00ffff;
        transform: translateY(-50%) scale(1.15);
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.6);
    }

    .carousel-nav.prev {
        left: 1rem;
    }

    .carousel-nav.next {
        right: 1rem;
    }

    /* Estados vacíos */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        text-align: center;
        color: rgba(255, 255, 255, 0.7);
        background: rgba(10, 14, 26, 0.95);
        border-radius: 12px;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: rgba(0, 255, 255, 0.5);
    }

    .empty-state-text {
        font-size: 1rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.8);
    }

    /* Full Width Card */
    .full-width-card {
        background: rgba(10, 14, 26, 0.85);
        border: 1px solid rgba(0, 255, 255, 0.3);
        border-radius: 16px;
        padding: 2rem;
        backdrop-filter: blur(20px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
        margin-bottom: 2rem;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 1.5rem;
        }

        .project-main-title {
            font-size: 1.1rem;
        }

        .section-title {
            font-size: 1rem;
        }

        .carousel-content {
            height: 280px;
        }

        .institutions-grid {
            grid-template-columns: 1fr;
        }

        .project-meta-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .hero-title {
            font-size: 1.3rem;
        }

        .project-header-card,
        .content-card,
        .carousel-container,
        .full-width-card {
            padding: 1.2rem;
        }

        .carousel-content {
            height: 240px;
        }

        .carousel-overlay {
            padding: 1rem 1rem 0.8rem;
        }
    }
</style>

<div class="index-page-wrapper">
    <div class="index-fixed-background"></div>

    <!-- Hero Compacto -->
    <div class="hero-container">
        <div class="hero-badge">
            <i class="fas fa-leaf"></i> IDIPI-298 | 2023-2025
        </div>
        
        <h1 class="hero-title">
            Caracterización de las condiciones físicas y meteorológicas para la implementación de dispositivos de generación de energías eólica y solar en la provincia de Chimborazo
        </h1>
        
        <p class="hero-description">
            Sistema avanzado de monitoreo y análisis meteorológico para el desarrollo de energías renovables en la provincia de Chimborazo.
        </p>
    </div>

    <!-- Main Content -->
    <div class="main-content-section">

        <!-- Primera Fila: Objetivo General - Fotografías de Estaciones -->
        <div class="content-grid">
            <!-- Objetivo General -->
            <div class="content-card">
                <h3 class="section-title">
                    <i class="fas fa-bullseye"></i>
                    Objetivo General
                </h3>
                <p class="objective-text">
                    Caracterizar las condiciones físicas y meteorológicas de la provincia de Chimborazo para determinar zonas con alto potencial de aprovechamiento de energía eólica y solar, e impulsar el desarrollo de tecnologías limpias y sostenibles.
                </p>
            </div>

            <!-- Fotografías de Estaciones -->
            <div class="carousel-container">
                <div class="carousel-header">
                    <h3 class="carousel-title">
                        <i class="fas fa-camera"></i>
                        Fotografías de Estaciones
                    </h3>
                    <p class="carousel-subtitle">Red GEAA - Infraestructura Meteorológica</p>
                </div>
                
                <div class="carousel-content" id="fotografiasContainer">
                    <span>Cargando fotografías...</span>
                </div>
                
                <button class="carousel-nav prev" onclick="changePhotoSlide(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <button class="carousel-nav next" onclick="changePhotoSlide(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <div class="carousel-indicators" id="fotografiasIndicators"></div>
            </div>
        </div>

        <!-- Segunda Fila: Mapas de Ubicación - Objetivos Específicos -->
        <div class="content-grid">
            <!-- Mapas de Ubicación -->
            <div class="carousel-container">
                <div class="carousel-header">
                    <h3 class="carousel-title">
                        <i class="fas fa-map"></i>
                        Mapas de Ubicación
                    </h3>
                    <p class="carousel-subtitle">Red GEAA - Geolocalización de Estaciones</p>
                </div>
                
                <div class="carousel-content" id="mapasContainer">
                    <span>Cargando mapas...</span>
                </div>
                
                <button class="carousel-nav prev" onclick="changeMapSlide(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <button class="carousel-nav next" onclick="changeMapSlide(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <div class="carousel-indicators" id="mapasIndicators"></div>
            </div>

            <!-- Objetivos Específicos -->
            <div class="content-card">
                <h3 class="section-title">
                    <i class="fas fa-list-check"></i>
                    Objetivos Específicos
                </h3>
                <ul class="objectives-list">
                    <li>Monitorear y analizar los parámetros meteorológicos mediante la red de estaciones del GEAA.</li>
                    <li>Modelar la dinámica atmosférica para identificar flujos de energía, perfiles de viento y temperatura.</li>
                    <li>Diseñar y construir prototipos de generación eólica y solar adaptados a las condiciones locales.</li>
                </ul>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="content-grid">
            <div class="content-card">
                <h3 class="section-title">
                    <i class="fas fa-handshake"></i>
                    Instituciones Participantes
                </h3>
                <div class="institutions-grid">
                    <div class="institution-card">
                        <div class="institution-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="institution-name">EERSA</div>
                    </div>
                    <div class="institution-card">
                        <div class="institution-icon">
                            <i class="fas fa-cloud-sun"></i>
                        </div>
                        <div class="institution-name">INAMHI</div>
                    </div>
                    <div class="institution-card">
                        <div class="institution-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="institution-name">GIZ Alemania</div>
                    </div>
                    <div class="institution-card">
                        <div class="institution-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <div class="institution-name">PUC de Chile</div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Antecedentes
                </h3>
                <p class="info-text">
                    La <span class="highlight-text">ESPOCH</span>, a través del grupo <span class="highlight-text">GEAA</span>, cuenta con una red de <span class="highlight-text">14 estaciones meteorológicas</span> que registran información atmosférica desde <span class="highlight-text">2013</span>.
                </p>
                <p class="info-text">
                    Estos datos han servido de base para diversos estudios sobre energías renovables en la región andina. El nuevo proyecto amplía esta investigación hacia la caracterización y modelado del potencial eólico y solar de Chimborazo.
                </p>
            </div>
        </div>

        <!-- Executive Summary -->
        <div class="full-width-card">
            <h3 class="section-title">
                <i class="fas fa-file-alt"></i>
                Resumen Ejecutivo
            </h3>
            <p class="info-text">
                El acelerado crecimiento poblacional y la creciente contaminación ambiental causada por el uso de combustibles fósiles han impulsado la búsqueda de fuentes de energía limpias y sostenibles, como la energía solar y eólica.
            </p>
            <p class="info-text">
                Gracias a su ubicación geográfica y a su variada orografía, la provincia de Chimborazo posee un alto potencial para el aprovechamiento de estas energías renovables. Sin embargo, este potencial aún no ha sido plenamente utilizado, en gran parte por la falta de información técnica local y el limitado interés institucional en el desarrollo de proyectos energéticos sostenibles.
            </p>
            <p class="info-text">
                La <span class="highlight-text">ESPOCH</span>, a través del Grupo de investigación Energías Alternativas y Ambiente (<span class="highlight-text">GEAA</span>), cuenta con una red de estaciones meteorológicas que opera desde <span class="highlight-text">2013</span>. Esta red recopila datos meteorológicos, permitiendo generar investigaciones, desarrollar modelos climáticos y establecer convenios con instituciones públicas y privadas.
            </p>
        </div>
    </div>
</div>

<script>
    // Variables globales para carruseles
    let currentPhotoSlideIndex = 0;
    let currentMapSlideIndex = 0;
    let fotoSlides = [];
    let mapSlides = [];
    let photoAutoplay = null;
    let mapAutoplay = null;

    // Función para cargar fotografías desde la base de datos - VERSIÓN OPTIMIZADA
    async function cargarFotografias() {
        try {
            console.log('🔄 Cargando fotografías desde la base de datos...');
            
            const response = await fetch('controller/Cindex.php?action=get_fotografias');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            const container = document.getElementById('fotografiasContainer');
            const indicators = document.getElementById('fotografiasIndicators');
            const carouselContainer = container.parentElement;
            const navButtons = carouselContainer.querySelectorAll('.carousel-nav');
            
            console.log('📊 Respuesta de fotografías:', data);
            
            if (data.success && data.data) {
                const fotografias = data.data.fotografias || [];
                const total = data.data.total || 0;
                
                console.log(`✅ Fotografías válidas encontradas: ${total}`);
                
                if (total > 0) {
                    // HAY FOTOGRAFÍAS VÁLIDAS - Mostrar carrusel dinámico
                    console.log('🖼️ Generando carrusel de fotografías...');
                    
                    const slidesHTML = generateSlidesHTML(fotografias, 'foto');
                    container.innerHTML = slidesHTML.html;
                    indicators.innerHTML = slidesHTML.indicators;
                    
                    // Mostrar controles de navegación SOLO si hay múltiples imágenes
                    if (total > 1) {
                        navButtons.forEach(btn => btn.style.display = 'flex');
                        indicators.style.display = 'flex';
                    } else {
                        // Solo una imagen - ocultar controles pero mantener visible la imagen
                        navButtons.forEach(btn => btn.style.display = 'none');
                        indicators.style.display = 'none';
                    }
                    
                    // Actualizar referencias de slides
                    updatePhotoSlidesReferences();
                    
                } else {
                    // NO HAY FOTOGRAFÍAS VÁLIDAS - Mostrar imagen por defecto
                    console.log('⚠️ No hay fotografías válidas - mostrando imagen por defecto');
                    
                    container.innerHTML = `
                        <div class="empty-state">
                            <img src="public/img/chimborazo 1.jpg" alt="Sin fotografías" class="carousel-image" style="object-fit: cover;">
                            <div class="empty-state-text">No hay fotografías de estaciones registradas</div>
                        </div>
                    `;
                    indicators.innerHTML = '';
                    
                    // Ocultar controles completamente
                    navButtons.forEach(btn => btn.style.display = 'none');
                    indicators.style.display = 'none';
                }
            } else {
                throw new Error(data.message || 'Error al cargar fotografías');
            }
            
        } catch (error) {
            console.error('❌ Error al cargar fotografías:', error);
            
            const container = document.getElementById('fotografiasContainer');
            const indicators = document.getElementById('fotografiasIndicators');
            const carouselContainer = container.parentElement;
            const navButtons = carouselContainer.querySelectorAll('.carousel-nav');
            
            // Mostrar imagen por defecto en caso de error
            container.innerHTML = `
                <div class="empty-state">
                    <img src="public/img/chimborazo 1.jpg" alt="Error de conexión" class="carousel-image" style="object-fit: cover;">
                    <div class="empty-state-text">Error de conexión - Mostrando imagen por defecto</div>
                </div>
            `;
            indicators.innerHTML = '';
            
            // Ocultar controles
            navButtons.forEach(btn => btn.style.display = 'none');
            indicators.style.display = 'none';
        }
    }

    // Función para cargar mapas desde la base de datos - VERSIÓN OPTIMIZADA
    async function cargarMapas() {
        try {
            console.log('🗺️ Cargando mapas desde la base de datos...');
            
            const response = await fetch('controller/Cindex.php?action=get_mapas');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            const container = document.getElementById('mapasContainer');
            const indicators = document.getElementById('mapasIndicators');
            const carouselContainer = container.parentElement;
            const navButtons = carouselContainer.querySelectorAll('.carousel-nav');
            
            console.log('📊 Respuesta de mapas:', data);
            
            if (data.success && data.data) {
                const mapas = data.data.mapas || [];
                const total = data.data.total || 0;
                
                console.log(`✅ Mapas válidos encontrados: ${total}`);
                
                if (total > 0) {
                    // HAY MAPAS VÁLIDOS - Mostrar carrusel dinámico
                    console.log('🗺️ Generando carrusel de mapas...');
                    
                    const slidesHTML = generateSlidesHTML(mapas, 'mapa');
                    container.innerHTML = slidesHTML.html;
                    indicators.innerHTML = slidesHTML.indicators;
                    
                    // Mostrar controles de navegación SOLO si hay múltiples mapas
                    if (total > 1) {
                        navButtons.forEach(btn => btn.style.display = 'flex');
                        indicators.style.display = 'flex';
                    } else {
                        // Solo un mapa - ocultar controles pero mantener visible el mapa
                        navButtons.forEach(btn => btn.style.display = 'none');
                        indicators.style.display = 'none';
                    }
                    
                    // Actualizar referencias de slides
                    updateMapSlidesReferences();
                    
                } else {
                    // NO HAY MAPAS VÁLIDOS - Mostrar imagen por defecto
                    console.log('⚠️ No hay mapas válidos - mostrando imagen por defecto');
                    
                    container.innerHTML = `
                        <div class="empty-state">
                            <img src="public/img/chimborazo 1.jpg" alt="Sin mapas" class="carousel-image" style="object-fit: cover;">
                            <div class="empty-state-text">No hay mapas de estaciones registrados</div>
                        </div>
                    `;
                    indicators.innerHTML = '';
                    
                    // Ocultar controles completamente
                    navButtons.forEach(btn => btn.style.display = 'none');
                    indicators.style.display = 'none';
                }
            } else {
                throw new Error(data.message || 'Error al cargar mapas');
            }
            
        } catch (error) {
            console.error('❌ Error al cargar mapas:', error);
            
            const container = document.getElementById('mapasContainer');
            const indicators = document.getElementById('mapasIndicators');
            const carouselContainer = container.parentElement;
            const navButtons = carouselContainer.querySelectorAll('.carousel-nav');
            
            // Mostrar imagen por defecto en caso de error
            container.innerHTML = `
                <div class="empty-state">
                    <img src="public/img/chimborazo 1.jpg" alt="Error de conexión" class="carousel-image" style="object-fit: cover;">
                    <div class="empty-state-text">Error de conexión - Mostrando imagen por defecto</div>
                </div>
            `;
            indicators.innerHTML = '';
            
            // Ocultar controles
            navButtons.forEach(btn => btn.style.display = 'none');
            indicators.style.display = 'none';
        }
    }

    // Generar HTML para slides - VERSIÓN MEJORADA
    function generateSlidesHTML(imagenes, tipo) {
        console.log(`🎨 Generando slides para ${tipo}:`, imagenes.length, 'imágenes');
        
        let html = '';
        let indicators = '';
        
        imagenes.forEach((imagen, index) => {
            const isActive = index === 0 ? 'active' : '';
            const altText = tipo === 'foto' ? `Fotografía Estación ${imagen.estacion}` : `Mapa Estación ${imagen.estacion}`;
            const stationName = tipo === 'foto' ? imagen.estacion : `Mapa: ${imagen.estacion}`;
            const location = imagen.ubicacion || imagen.canton || imagen.provincia || 'Sin ubicación específica';
            const codigo = imagen.codigo ? ` (${imagen.codigo})` : '';
            
            html += `
                <div class="carousel-slide ${isActive}" data-tipo="${tipo}" data-index="${index}">
                    <img src="${imagen.imagen}" alt="${altText}" class="carousel-image" 
                         onerror="this.parentElement.innerHTML='<div class=empty-state><div class=empty-state-text>Error al cargar ${tipo}</div></div>'; this.remove()">
                    <div class="carousel-overlay">
                        <div class="carousel-info">
                            <h4 class="carousel-station-name">${stationName}${codigo}</h4>
                            <p class="carousel-station-location">${location}</p>
                        </div>
                    </div>
                </div>
            `;
            
            indicators += `
                <div class="carousel-indicator ${isActive}" 
                     onclick="current${tipo === 'foto' ? 'Photo' : 'Map'}Slide(${index + 1})" 
                     title="Ver ${tipo} ${index + 1} de ${imagenes.length}"></div>
            `;
        });
        
        console.log(`✅ Slides generados para ${tipo}: ${imagenes.length} slides, ${imagenes.length} indicadores`);
        
        return { html, indicators };
    }

    // Actualizar referencias de slides de fotografías
    function updatePhotoSlidesReferences() {
        setTimeout(() => {
            fotoSlides = document.querySelectorAll('#fotografiasContainer .carousel-slide');
            console.log('📸 Referencias de slides de fotografías actualizadas:', fotoSlides.length);
            
            if (fotoSlides.length > 0) {
                currentPhotoSlideIndex = 0;
                showPhotoSlide(0);
                
                // Iniciar auto-play para fotografías (5 segundos) solo si hay múltiples imágenes
                if (fotoSlides.length > 1) {
                    startPhotoAutoplay();
                }
            }
        }, 200);
    }

    // Actualizar referencias de slides de mapas
    function updateMapSlidesReferences() {
        setTimeout(() => {
            mapSlides = document.querySelectorAll('#mapasContainer .carousel-slide');
            console.log('🗺️ Referencias de slides de mapas actualizadas:', mapSlides.length);
            
            if (mapSlides.length > 0) {
                currentMapSlideIndex = 0;
                showMapSlide(0);
                
                // Iniciar auto-play para mapas (5.5 segundos) solo si hay múltiples imágenes
                if (mapSlides.length > 1) {
                    startMapAutoplay();
                }
            }
        }, 200);
    }

    // Iniciar auto-play para fotografías
    function startPhotoAutoplay() {
        // Limpiar cualquier auto-play existente
        if (photoAutoplay) {
            clearInterval(photoAutoplay);
            photoAutoplay = null;
        }
        
        console.log('▶️ Iniciando auto-play para fotografías');
        
        photoAutoplay = setInterval(() => {
            if (fotoSlides.length > 0) {
                console.log(`🔄 Auto-play: cambiando slide de fotografías (${currentPhotoSlideIndex + 1}/${fotoSlides.length})`);
                changePhotoSlide(1);
            }
        }, 5000);
        
        // Pausar auto-play al hacer hover
        const carouselContainer = document.querySelector('.content-grid:nth-child(1) .carousel-container');
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', () => {
                console.log('⏸️ Pausando auto-play de fotografías por hover');
                clearInterval(photoAutoplay);
            });
            
            carouselContainer.addEventListener('mouseleave', () => {
                if (fotoSlides.length > 1) {
                    console.log('▶️ Reanudando auto-play de fotografías');
                    startPhotoAutoplay();
                }
            });
        }
    }

    // Iniciar auto-play para mapas
    function startMapAutoplay() {
        // Limpiar cualquier auto-play existente
        if (mapAutoplay) {
            clearInterval(mapAutoplay);
            mapAutoplay = null;
        }
        
        console.log('▶️ Iniciando auto-play para mapas');
        
        mapAutoplay = setInterval(() => {
            if (mapSlides.length > 0) {
                console.log(`🔄 Auto-play: cambiando slide de mapas (${currentMapSlideIndex + 1}/${mapSlides.length})`);
                changeMapSlide(1);
            }
        }, 5500);
        
        // Pausar auto-play al hacer hover
        const carouselContainer = document.querySelector('.content-grid:nth-child(2) .carousel-container');
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', () => {
                console.log('⏸️ Pausando auto-play de mapas por hover');
                clearInterval(mapAutoplay);
            });
            
            carouselContainer.addEventListener('mouseleave', () => {
                if (mapSlides.length > 1) {
                    console.log('▶️ Reanudando auto-play de mapas');
                    startMapAutoplay();
                }
            });
        }
    }

    // Mostrar slide específico de fotografías con navegación cíclica
    function showPhotoSlide(n) {
        if (fotoSlides.length === 0) return;
        
        // Navegación cíclica: si llega al final, vuelve al principio
        if (n >= fotoSlides.length) {
            currentPhotoSlideIndex = 0;
        } else if (n < 0) {
            // Si va hacia atrás desde el primero, va al último
            currentPhotoSlideIndex = fotoSlides.length - 1;
        } else {
            currentPhotoSlideIndex = n;
        }
        
        // Actualizar clases active en slides
        fotoSlides.forEach((slide, index) => {
            if (index === currentPhotoSlideIndex) {
                slide.classList.add('active');
            } else {
                slide.classList.remove('active');
            }
        });
        
        // Actualizar clases active en indicadores
        const photoIndicators = document.querySelectorAll('#fotografiasIndicators .carousel-indicator');
        photoIndicators.forEach((indicator, index) => {
            if (index === currentPhotoSlideIndex) {
                indicator.classList.add('active');
            } else {
                indicator.classList.remove('active');
            }
        });
        
        console.log(`📸 Mostrando slide ${currentPhotoSlideIndex + 1} de ${fotoSlides.length}`);
    }

    // Mostrar slide específico de mapas con navegación cíclica
    function showMapSlide(n) {
        if (mapSlides.length === 0) return;
        
        // Navegación cíclica: si llega al final, vuelve al principio
        if (n >= mapSlides.length) {
            currentMapSlideIndex = 0;
        } else if (n < 0) {
            // Si va hacia atrás desde el primero, va al último
            currentMapSlideIndex = mapSlides.length - 1;
        } else {
            currentMapSlideIndex = n;
        }
        
        // Actualizar clases active en slides
        mapSlides.forEach((slide, index) => {
            if (index === currentMapSlideIndex) {
                slide.classList.add('active');
            } else {
                slide.classList.remove('active');
            }
        });
        
        // Actualizar clases active en indicadores
        const mapIndicators = document.querySelectorAll('#mapasIndicators .carousel-indicator');
        mapIndicators.forEach((indicator, index) => {
            if (index === currentMapSlideIndex) {
                indicator.classList.add('active');
            } else {
                indicator.classList.remove('active');
            }
        });
        
        console.log(`🗺️ Mostrando slide ${currentMapSlideIndex + 1} de ${mapSlides.length}`);
    }

    // Cambiar slide de fotografías
    function changePhotoSlide(n) {
        showPhotoSlide(currentPhotoSlideIndex + n);
    }

    // Cambiar slide de mapas
    function changeMapSlide(n) {
        showMapSlide(currentMapSlideIndex + n);
    }

    // Slide actual de fotografías
    function currentPhotoSlide(n) {
        showPhotoSlide(n - 1);
    }

    // Slide actual de mapas
    function currentMapSlide(n) {
        showMapSlide(n - 1);
    }

    // Función para detener auto-play (por si es necesaria)
    function stopAllAutoplay() {
        if (photoAutoplay) {
            clearInterval(photoAutoplay);
            photoAutoplay = null;
        }
        if (mapAutoplay) {
            clearInterval(mapAutoplay);
            mapAutoplay = null;
        }
        console.log('⏹️ Auto-play detenido para todos los carruseles');
    }

    // Inicializar cuando el DOM esté listo - VERSIÓN MEJORADA CON AUTO-PLAY
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Inicializando sistema de carruseles dinámicos con auto-play...');
        
        // Verificar que los contenedores existen
        const fotoContainer = document.getElementById('fotografiasContainer');
        const mapContainer = document.getElementById('mapasContainer');
        
        if (!fotoContainer || !mapContainer) {
            console.error('❌ No se encontraron los contenedores necesarios');
            return;
        }
        
        console.log('✅ Contenedores encontrados, cargando datos desde la base de datos...');
        
        // Cargar imágenes desde la base de datos
        cargarFotografias();
        cargarMapas();
        
        // Función para debugging - verificar estadísticas
        window.debugEstadisticas = async function() {
            try {
                console.log('🔍 Obteniendo estadísticas de debugging...');
                const response = await fetch('controller/Cindex.php?action=debug_estadisticas');
                const data = await response.json();
                console.log('📊 Estadísticas:', data);
                return data;
            } catch (error) {
                console.error('❌ Error obteniendo estadísticas:', error);
            }
        };
        
        // Función para detener/reanudar auto-play manualmente
        window.toggleAutoplay = function() {
            if (photoAutoplay || mapAutoplay) {
                stopAllAutoplay();
                console.log('⏹️ Auto-play detenido manualmente');
            } else {
                if (fotoSlides.length > 1) startPhotoAutoplay();
                if (mapSlides.length > 1) startMapAutoplay();
                console.log('▶️ Auto-play reanudado manualmente');
            }
        };
        
        // Log para confirmar que el sistema está cargado
        console.log('✨ Sistema de carruseles inicializado correctamente');
        console.log('💡 Usa debugEstadisticas() para ver información de debugging');
        console.log('💡 Usa toggleAutoplay() para detener/reanudar auto-play manualmente');
    });
</script>

<?php
include('views/includes/footer.php');
?>