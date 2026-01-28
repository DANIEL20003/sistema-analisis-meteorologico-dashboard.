<!-- Script agregado al final del header para botón manual global -->
<?php
// Este archivo se carga al final de header.php para tener el manual disponible en todas las páginas
?>

<!-- BOTÓN FLOTANTE MANUAL DE USUARIO -->
<button id="manualBtn" class="manual-floating-btn" title="Manual de Usuario">
    <i class="fas fa-book"></i>
    <span class="manual-btn-text">Ayuda</span>
</button>

<!-- MODAL MANUAL DE USUARIO -->
<div id="manualModal" class="manual-modal">
    <div class="manual-modal-content">
        <div class="manual-modal-header">
            <h2 class="manual-modal-title">
                <i class="fas fa-book"></i>
                Manual de Usuario
            </h2>
            <button class="manual-modal-close" id="closeManualBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="manual-modal-body">
            <?php include(__DIR__ . '/../../manual_usuario.html'); ?>
        </div>
    </div>
</div>

<style>
    /* Botón flotante */
    .manual-floating-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00ffff, #0096ff);
        border: none;
        box-shadow: 0 8px 25px rgba(0, 255, 255, 0.4);
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        transition: all 0.3s ease;
        z-index: 9998;
        color: #0a0e1a;
    }

    .manual-floating-btn i {
        font-size: 1.5rem;
    }

    .manual-btn-text {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .manual-floating-btn:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 12px 35px rgba(0, 255, 255, 0.6);
    }

    /* Modal */
    .manual-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(8px);
        animation: fadeIn 0.3s ease;
    }

    .manual-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .manual-modal-content {
        background: #0a0e1a;
        border: 2px solid #00ffff;
        border-radius: 20px;
        width: 90%;
        max-width: 1200px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0, 255, 255, 0.4);
        animation: slideUp 0.4s ease;
    }

    .manual-modal-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid rgba(0, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, rgba(0, 255, 255, 0.1), rgba(0, 150, 255, 0.1));
        border-radius: 20px 20px 0 0;
    }

    .manual-modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #00ffff, #0096ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin: 0;
    }

    .manual-modal-close {
        background: rgba(255, 7, 58, 0.2);
        border: 2px solid #ff073a;
        color: #ff073a;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }

    .manual-modal-close:hover {
        background: #ff073a;
        color: #fff;
        transform: rotate(90deg);
    }

    .manual-modal-body {
        padding: 2rem;
        overflow-y: auto;
        max-height: calc(90vh - 100px);
    }

    .manual-modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .manual-modal-body::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
    }

    .manual-modal-body::-webkit-scrollbar-thumb {
        background: #00ffff;
        border-radius: 4px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .manual-floating-btn {
            width: 60px;
            height: 60px;
            bottom: 20px;
            right: 20px;
        }

        .manual-modal-content {
            width: 95%;
            max-height: 95vh;
        }

        .manual-modal-header {
            padding: 1rem 1.5rem;
        }

        .manual-modal-title {
            font-size: 1.2rem;
        }

        .manual-modal-body {
            padding: 1.5rem;
        }
    }
</style>

<script>
    // Control del modal manual disponible globalmente
    document.addEventListener('DOMContentLoaded', function () {
        const manualBtn = document.getElementById('manualBtn');
        const manualModal = document.getElementById('manualModal');
        const closeManualBtn = document.getElementById('closeManualBtn');

        // Abrir modal
        if (manualBtn) {
            manualBtn.addEventListener('click', () => {
                manualModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }

        // Cerrar modal
        if (closeManualBtn) {
            closeManualBtn.addEventListener('click', () => {
                manualModal.classList.remove('active');
                document.body.style.overflow = 'auto';
            });
        }

        // Cerrar al hacer clic fuera
        if (manualModal) {
            manualModal.addEventListener('click', (e) => {
                if (e.target === manualModal) {
                    manualModal.classList.remove('active');
                    document.body.style.overflow = 'auto';
                }
            });
        }

        // Cerrar con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && manualModal.classList.contains('active')) {
                manualModal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    });
</script>