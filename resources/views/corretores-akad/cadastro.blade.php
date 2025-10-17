<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cadastro de Corretor AKAD - Inova</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #070B4A 0%, #393C6E 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-container {
            max-width: 750px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .header {
            background: white;
            color: #070B4A;
            padding: 40px 50px;
            text-align: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            filter: brightness(0) saturate(100%) invert(12%) sepia(91%) saturate(1834%) hue-rotate(217deg) brightness(91%) contrast(106%);
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin: 0;
        }

        .header p {
            margin-top: 5px;
            opacity: 0.9;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .form-container {
            padding: 10px 50px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e1e5e9;
        }

        .section-icon {
            font-size: 1.5rem;
            margin-right: 15px;
            color: #070B4A;
        }

        .section-title {
            color: #070B4A;
            font-size: 1.4rem;
            font-weight: 600;
            margin: 0;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 1rem;
        }

        .required {
            color: #e74c3c;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #393C6E;
            background: white;
            box-shadow: 0 0 0 3px rgba(57, 60, 110, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #070B4A 0%, #393C6E 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(7, 11, 74, 0.3);
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .status-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            display: none;
        }

        .status-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success-container {
            display: none;
            text-align: center;
            padding: 50px;
        }

        .success-icon {
            font-size: 4rem;
            color: #27ae60;
            margin-bottom: 25px;
        }

        .success-container h2 {
            color: #070B4A;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .success-container p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .contact-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-top: 30px;
            text-align: center;
        }

        .contact-info h4 {
            color: #070B4A;
            margin-bottom: 10px;
        }

        .contact-info p {
            color: #666;
            font-size: 0.95rem;
            margin: 6px 0;
        }

        .loading {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .main-container {
                border-radius: 15px;
            }

            .header,
            .form-container,
            .success-container {
                padding: 30px 25px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 1.8rem;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Formulário Principal -->
        <div id="form-section">
            <div class="header">
                <div class="logo-container">
                    <img src="{{ asset('assets/svg/logo-login.svg') }}" alt="Inova" class="logo-icon">
                    <h1>Cadastro de Corretor na AKAD Seguros</h1>
                </div>
                <p>Preencha os dados da corretora para assinatura da declaração e seja atendido pela Inova Representação</p>
            </div>

            <div class="form-container">
                <div class="form-section">
                    <div class="section-header">
                        <span class="section-icon">📋</span>
                        <h3 class="section-title">Dados da Corretora</h3>
                    </div>
                    
                    <div class="form-group">
                        <label for="razao_social">Razão Social <span class="required">*</span></label>
                        <input type="text" id="razao_social" name="razao_social" required placeholder="Nome completo da corretora">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cnpj">CNPJ<span class="required">*</span></label>
                            <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00">
                        </div>
                        <div class="form-group">
                            <label for="codigo_susep">Código SUSEP<span class="required">*</span></label>
                            <input type="text" id="codigo_susep" name="codigo_susep" placeholder="Código de registro SUSEP">
                        </div>
                    </div>
                        <div class="form-group">
                            <label for="email">Email Corporativo <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="email@corretora.com.br">
                        </div>                    
                </div>

                <div class="form-section">
                    <div class="section-header">
                        <span class="section-icon">👤</span>
                        <h3 class="section-title">Responsável Legal</h3>
                    </div>
                    
                    <div class="form-group">
                        <label for="nome">Nome do Responsável <span class="required">*</span></label>
                        <input type="text" id="nome" name="nome" required placeholder="Nome completo do responsável legal">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefone">Telefone <span class="required">*</span></label>
                            <input type="tel" id="telefone" name="telefone" required placeholder="(11) 99999-9999">
                        </div>
                    </div>
                </div>

                <button type="button" class="submit-btn" onclick="enviarCadastro()">
                    Solicitar atendimento
                </button>

                <div id="statusMessage" class="status-message"></div>
            </div>
        </div>

        <!-- Tela de Sucesso -->
        <div id="success-section" class="success-container">
            <img src="{{ asset('assets/svg/Logo-horizontal-Inova-azul.svg') }}" alt="Inova" style="max-width: 200px; margin-bottom: 30px;">
            
            <div class="success-icon">✓</div>
            <h2>Cadastro Realizado com Sucesso!</h2>
            <p>A declaração de atendimento foi enviado para o email informado.</p>
            <p>Verifique sua caixa de entrada e siga as instruções para assinar digitalmente o documento.</p>

            <div class="contact-info">
                <h4>📞 Suporte</h4>
                <p>Em caso de dúvidas, entre em contato com nossa equipe</p>
                <p><strong>Email:</strong> atendimento@inovarepresentacao.com.br</p>
            </div>
        </div>
    </div>

    <script>
        // Formatação automática de campos
        document.getElementById('cnpj').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 14) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
                e.target.value = value;
            }
        });

        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length === 11) {
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length === 10) {
                    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                }
                e.target.value = value;
            }
        });

        // Validações
        function validarEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }

        function validarCNPJ(cnpj) {
            cnpj = cnpj.replace(/\D/g, '');
            
            if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) {
                return false;
            }
            
            // Validação do primeiro dígito verificador
            let sum = 0;
            let weight = 2;
            for (let i = 11; i >= 0; i--) {
                sum += parseInt(cnpj.charAt(i)) * weight;
                weight++;
                if (weight === 10) weight = 2;
            }
            let remainder = sum % 11;
            if (remainder < 2) remainder = 0;
            else remainder = 11 - remainder;
            if (remainder !== parseInt(cnpj.charAt(12))) return false;
            
            // Validação do segundo dígito verificador
            sum = 0;
            weight = 2;
            for (let i = 12; i >= 0; i--) {
                sum += parseInt(cnpj.charAt(i)) * weight;
                weight++;
                if (weight === 10) weight = 2;
            }
            remainder = sum % 11;
            if (remainder < 2) remainder = 0;
            else remainder = 11 - remainder;
            if (remainder !== parseInt(cnpj.charAt(13))) return false;
            
            return true;
        }

        // Função para enviar cadastro
        async function enviarCadastro() {
            const btn = document.querySelector('.submit-btn');
            const statusDiv = document.getElementById('statusMessage');
            
            // Coleta dos dados
            const dados = {
                razao_social: document.getElementById('razao_social').value.trim(),
                cnpj: document.getElementById('cnpj').value.trim(),
                codigo_susep: document.getElementById('codigo_susep').value.trim(),
                email: document.getElementById('email').value.trim(),
                nome: document.getElementById('nome').value.trim(),
                telefone: document.getElementById('telefone').value.trim()
            };

            // Validações
            if (!dados.razao_social || !dados.email || !dados.nome || !dados.telefone) {
                mostrarStatus('Preencha todos os campos obrigatórios.', 'error');
                return;
            }

            if (!validarEmail(dados.email)) {
                mostrarStatus('Digite um e-mail válido.', 'error');
                return;
            }

            if (dados.cnpj && !validarCNPJ(dados.cnpj)) {
                mostrarStatus('Digite um CNPJ válido.', 'error');
                return;
            }

            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<div class="loading"></div>Enviando dados...';
            statusDiv.style.display = 'none';

            try {
                const response = await fetch('/api/corretores-akad/cadastrar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(dados)
                });
                
                // Debug: verificar se resposta é OK
                if (!response.ok) {
                    // Para erro 422, ainda precisamos ler o JSON para ver os erros
                    if (response.status === 422) {
                        const errorResult = await response.json();
                        console.log('Erro 422 - Dados de validação:', errorResult);
                        if (errorResult.errors) {
                            const primeiroErro = Object.values(errorResult.errors)[0][0];
                            throw new Error(primeiroErro);
                        } else {
                            throw new Error(errorResult.message || 'Dados inválidos');
                        }
                    } else {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                }
                
                const result = await response.json();
                
                // Debug: logar resposta
                console.log('Resposta da API:', result);
                
                if (result.success) {
                    // Sucesso: mostra tela de confirmação
                    btn.innerHTML = '✓ Cadastro realizado com sucesso!';
                    
                    setTimeout(() => {
                        mostrarTelaFinal();
                    }, 1500);
                    
                } else {
                    // Mostrar erros de validação se existirem
                    if (result.errors) {
                        const primeiroErro = Object.values(result.errors)[0][0];
                        throw new Error(primeiroErro);
                    } else {
                        throw new Error(result.message || 'Erro no cadastro');
                    }
                }
                
            } catch (error) {
                console.error('Erro:', error);
                mostrarStatus(error.message || 'Erro ao enviar dados. Tente novamente.', 'error');
                
                btn.disabled = false;
                btn.innerHTML = 'Cadastrar e Enviar Contrato';
            }
        }

        function mostrarStatus(mensagem, tipo) {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.textContent = mensagem;
            statusDiv.className = `status-message ${tipo}`;
            statusDiv.style.display = 'block';
            
            if (tipo === 'success') {
                setTimeout(() => {
                    statusDiv.style.display = 'none';
                }, 5000);
            }
        }

        function mostrarTelaFinal() {
            document.getElementById('form-section').style.display = 'none';
            document.getElementById('success-section').style.display = 'block';
        }
    </script>
</body>
</html>