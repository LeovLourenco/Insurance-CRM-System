@props([
    'name' => '',
    'size' => 'md'
])

@php
    // Extrair iniciais dos primeiros nomes
    $words = explode(' ', trim($name));
    $initials = '';
    
    // Pegar a inicial do primeiro nome
    if (count($words) > 0) {
        $initials .= strtoupper(substr($words[0], 0, 1));
    }
    
    // Pegar a inicial do segundo nome (se existir)
    if (count($words) > 1) {
        $initials .= strtoupper(substr($words[1], 0, 1));
    }
    
    // Se não tiver nome, usar '?'
    if (empty($initials)) {
        $initials = '?';
    }
    
    // Definir tamanhos
    $sizes = [
        'sm' => ['width' => '40px', 'height' => '40px', 'font' => '14px'],
        'md' => ['width' => '60px', 'height' => '60px', 'font' => '20px'],
        'lg' => ['width' => '80px', 'height' => '80px', 'font' => '28px'],
        'xl' => ['width' => '120px', 'height' => '120px', 'font' => '42px']
    ];
    
    $currentSize = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="avatar-inova d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
     style="width: {{ $currentSize['width'] }}; 
            height: {{ $currentSize['height'] }}; 
            font-size: {{ $currentSize['font'] }}; 
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.1);
            {{ $attributes->get('style', '') }}">
    {{ $initials }}
</div>