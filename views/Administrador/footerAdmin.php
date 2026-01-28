        </div>
    </main>

<style>
    :root {
        --bg-primary: #0a0e1a;
        --bg-secondary: #12182b;
        --bg-tertiary: #1a2236;
        --neon-cyan: #00ffff;
        --neon-blue: #0099ff;
        --neon-purple: #9d4edd;
        --neon-pink: #ff006e;
        --text-primary: #e8eaf0;
        --text-secondary: #a0a8c0;
        --border-color: rgba(0, 255, 255, 0.1);
    }

    .main-footer {
        background: var(--bg-secondary);
        border-top: 1px solid var(--border-color);
        margin-top: auto;
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 3rem;
        padding: 3rem 0;
    }

    .footer-column h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--neon-cyan);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .footer-column h4 i {
        font-size: 1.1rem;
    }

    .institution-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .contact-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .contact-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.85rem;
    }

    .contact-item i {
        color: var(--neon-cyan);
        width: 18px;
        margin-top: 2px;
    }

    .contact-item a {
        color: var(--text-secondary);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .contact-item a:hover {
        color: var(--neon-cyan);
    }

    .social-links {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .social-links a {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(0, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--neon-cyan);
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .social-links a:hover {
        background: var(--neon-cyan);
        color: var(--bg-secondary);
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 255, 255, 0.3);
    }

    .careers-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .careers-list li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: rgba(0, 255, 255, 0.05);
        border-radius: 6px;
        font-size: 0.8rem;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .careers-list li:hover {
        background: rgba(0, 255, 255, 0.1);
        border-color: var(--neon-cyan);
    }

    .careers-list i {
        color: var(--neon-cyan);
        width: 14px;
    }

    .team-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .team-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: rgba(0, 255, 255, 0.05);
        border-radius: 6px;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .team-item:hover {
        background: rgba(0, 255, 255, 0.1);
        border-color: var(--neon-cyan);
    }

    .team-item i {
        color: var(--neon-cyan);
        font-size: 1rem;
        flex-shrink: 0;
    }

    .team-item-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .team-item-info strong {
        font-size: 0.85rem;
        color: var(--text-primary);
        font-weight: 600;
    }

    .team-item-info span {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .footer-bottom {
        background: var(--bg-tertiary);
        border-top: 1px solid var(--border-color);
        padding: 2rem 0;
    }

    .decorative-lines {
        display: flex;
        height: 3px;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .line {
        flex: 1;
    }

    .line.cyan { background: var(--neon-cyan); }
    .line.blue { background: var(--neon-blue); }
    .line.purple { background: var(--neon-purple); }
    .line.pink { background: var(--neon-pink); }

    .copyright-content {
        text-align: center;
    }

    .copyright-main {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .copyright-sub {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .copyright-dev {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--neon-cyan);
        color: var(--bg-primary);
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        box-shadow: 0 4px 12px rgba(0, 255, 255, 0.4);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .back-to-top.visible {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 16px rgba(0, 255, 255, 0.6);
    }

    @media (max-width: 1024px) {
        .footer-content {
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }
    }

    @media (max-width: 768px) {
        .footer-content {
            padding: 2rem 0;
        }

        .back-to-top {
            width: 45px;
            height: 45px;
            bottom: 20px;
            right: 20px;
        }
    }
</style>

<footer class="main-footer">
    <div class="footer-bottom">
        <div class="container">
            <div class="decorative-lines">
                <div class="line cyan"></div>
                <div class="line blue"></div>
                <div class="line purple"></div>
                <div class="line pink"></div>
            </div>
            
            <div class="copyright-content">
                <p class="copyright-main">&copy; <?php echo date('Y'); ?> ESPOCH - Escuela Superior Politécnica de Chimborazo</p>
                <p class="copyright-sub">Sistema de Análisis Meteorológico para Energías Renovables</p>
                <p class="copyright-dev">Desarrollado por estudiantes de Ingeniería en Software - Facultad de Ciencias</p>
            </div>
        </div>
    </div>
</footer>

<button class="back-to-top" id="backToTop">
    <i class="fas fa-chevron-up"></i>
</button>

<script>
    const backToTop = document.getElementById('backToTop');

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });

    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
</script>
</body>
</html>