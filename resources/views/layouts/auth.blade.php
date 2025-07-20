<style>
        /* Topbar melhorado */
        .topbar {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 1rem 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-bottom: 1px solid #e2e8f0;
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Breadcrumb (prioridade visual) */
        .breadcrumb {
            margin: 0;
            background: none;
            padding: 0;
        }

        .breadcrumb-item a {
            color: #1e293b;
            transition: color 0.2s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .breadcrumb-item a:hover {
            color: #3b82f6;
        }

        .breadcrumb-item.active {
            color: #3b82f6;
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Nome da empresa (contexto, mais discreto) */
        .company-name h6 {
            color: #64748b;
            font-size: 0.9rem;
            letter-spacing: -0.025em;
        }

        .company-name h6 i {
            color: #94a3b8;
        }

        /* Data/hora (informação passiva) */
        .topbar-datetime {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #94a3b8;
            font-size: 0.875rem;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .topbar {
                padding: 0.75rem 1rem;
            }
            
            .breadcrumb-item a {
                font-size: 0.9rem;
            }
            
            .breadcrumb-item.active {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .topbar {
                padding: 0.5rem 1rem;
            }
            
            .breadcrumb-item a {
                font-size: 0.85rem;
            }
            
            .breadcrumb-item.active {
                font-size: 0.85rem;
            }
        }
    </style>

    <script>
        // Atualizar hora em tempo real
        function updateTime() {
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                const now = new Date();
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                timeElement.textContent = `${hours}:${minutes}`;
            }
        }

        // Atualizar a cada minuto
        document.addEventListener('DOMContentLoaded', function() {
            updateTime(); // Atualiza imediatamente
            setInterval(updateTime, 60000); // Atualiza a cada minuto
        });
    </script>