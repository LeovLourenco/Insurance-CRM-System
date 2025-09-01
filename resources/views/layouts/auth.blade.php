<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Login - Sistema de Cotações')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- App CSS (includes Bootstrap with INOVA colors) -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--inova-primary) 0%, var(--inova-secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        background: radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 40%),
           radial-gradient(circle at 70% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 40%);
        animation: float 8s ease-in-out infinite;

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-40px) rotate(3deg); }
        }

        .auth-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 
                       0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            animation: slideUp 0.6s ease-out;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-header {
            background: linear-gradient(135deg, 
                       color-mix(in srgb, var(--inova-primary) 90%, #000 10%) 0%, 
                       color-mix(in srgb, var(--inova-primary) 70%, #000 30%) 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.1;
        }

        .auth-header .content {
            position: relative;
            z-index: 1;
        }

        .auth-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .auth-header p {
            margin: 0.75rem 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        .auth-body {
            padding: 2.5rem 2rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--inova-text-dark);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control {
            border: 2px solid var(--inova-border-default);
            border-radius: 0.75rem;
            padding: 0.875rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--inova-bg-lighter);
        }

        .form-control:focus {
            border-color: var(--inova-primary);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--inova-primary) 10%, transparent 90%);
            background: white;
            transform: translateY(-1px);
        }

        .form-control.is-invalid {
            border-color: var(--bs-danger);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--inova-primary) 0%, var(--inova-secondary) 100%);
            border: none;
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px color-mix(in srgb, var(--inova-primary) 40%, transparent 60%);
            background: linear-gradient(135deg, 
                       color-mix(in srgb, var(--inova-primary) 90%, white 10%) 0%, 
                       color-mix(in srgb, var(--inova-secondary) 90%, white 10%) 100%);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        .form-check-input:checked {
            background-color: var(--inova-primary);
            border-color: var(--inova-primary);
        }

        .auth-footer {
            text-align: center;
            padding: 1rem 2rem 2rem;
            color: var(--inova-text-muted);
            font-size: 0.9rem;
            background: var(--inova-bg-lighter);
        }

        .auth-footer a {
            color: var(--inova-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .auth-links {
            margin-top: 1.5rem;
            text-align: center;
        }

        .auth-links a {
            color: var(--inova-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .auth-links a:hover {
            color: var(--inova-secondary);
            text-decoration: underline;
        }

        /* Responsividade */
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .auth-container {
                max-width: 100%;
            }
            
            .auth-header {
                padding: 2rem 1.5rem;
            }
            
            .auth-header h1 {
                font-size: 1.5rem;
            }
            
            .auth-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>

    @yield('styles')
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="content">
                <h1>
                    <i class="bi bi-shield-lock"></i>
                    Logos
                </h1>
                <p>@yield('header-subtitle', 'Sistema de Cotações')</p>
            </div>
        </div>

        <div class="auth-body">
            @yield('content')
        </div>

        <div class="auth-footer">
            @yield('footer', '2025 Logos - Todos os direitos reservados')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>