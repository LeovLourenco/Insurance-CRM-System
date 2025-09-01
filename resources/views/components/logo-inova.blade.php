@props([
    'type' => 'horizontal', // horizontal | vertical | icon
    'size' => 'md', // sm | md | lg
    'color' => 'default' // default | white | dark
])

@php
    // Definir dimensões baseadas no tamanho
    $sizes = [
        'sm' => ['width' => '120px', 'height' => '30px'],
        'md' => ['width' => '160px', 'height' => '40px'],
        'lg' => ['width' => '200px', 'height' => '50px']
    ];
    
    $currentSize = $sizes[$size] ?? $sizes['md'];
    
    // Definir cores baseadas no tema
    $colorSchemes = [
        'default' => ['bg' => '#070B4A', 'text' => 'white'],
        'white' => ['bg' => 'white', 'text' => '#070B4A'],
        'dark' => ['bg' => '#070B4A', 'text' => 'white']
    ];
    
    $currentColors = $colorSchemes[$color] ?? $colorSchemes['default'];
@endphp

<div class="logo-inova-container d-flex align-items-center" {{ $attributes }}>
    @if($type === 'horizontal' || $type === 'icon')
        <svg xmlns="http://www.w3.org/2000/svg" 
             viewBox="0 0 160 40" 
             style="width: {{ $currentSize['width'] }}; height: {{ $currentSize['height'] }};"
             class="logo-horizontal-inova">
            <defs>
                <style>
                    .logo-bg-{{ $color }} { fill: {{ $currentColors['bg'] }}; }
                    .logo-icon-{{ $color }} { 
                        fill: {{ $currentColors['text'] }}; 
                        font-family: 'Zona Pro Bold', Arial Black, sans-serif; 
                        font-weight: 900; 
                        font-size: 18px; 
                    }
                    .logo-text-{{ $color }} { 
                        fill: {{ $currentColors['text'] }}; 
                        font-family: 'MullerNarrow-ExtraBold', Arial Narrow, sans-serif; 
                        font-weight: 800; 
                        font-size: 16px; 
                        letter-spacing: 1px; 
                    }
                </style>
            </defs>
            
            <!-- Fundo do ícone (quadrado azul) -->
            <rect x="2" y="2" width="36" height="36" rx="4" ry="4" class="logo-bg-{{ $color }}"/>
            
            <!-- Letra "I" dentro do quadrado -->
            <text x="20" y="28" text-anchor="middle" class="logo-icon-{{ $color }}">I</text>
            
            @if($type === 'horizontal')
                <!-- Texto "INOVA" -->
                <text x="50" y="25" class="logo-text-{{ $color }}">INOVA</text>
            @endif
        </svg>
    @endif
    
    @if($type === 'vertical')
        <svg xmlns="http://www.w3.org/2000/svg" 
             viewBox="0 0 80 60" 
             style="width: {{ $currentSize['width'] }}; height: auto;"
             class="logo-vertical-inova">
            <defs>
                <style>
                    .logo-bg-{{ $color }} { fill: {{ $currentColors['bg'] }}; }
                    .logo-icon-{{ $color }} { 
                        fill: {{ $currentColors['text'] }}; 
                        font-family: 'Zona Pro Bold', Arial Black, sans-serif; 
                        font-weight: 900; 
                        font-size: 16px; 
                    }
                    .logo-text-{{ $color }} { 
                        fill: {{ $currentColors['text'] }}; 
                        font-family: 'MullerNarrow-ExtraBold', Arial Narrow, sans-serif; 
                        font-weight: 800; 
                        font-size: 14px; 
                        letter-spacing: 1px; 
                    }
                </style>
            </defs>
            
            <!-- Fundo do ícone (quadrado azul) -->
            <rect x="22" y="2" width="36" height="36" rx="4" ry="4" class="logo-bg-{{ $color }}"/>
            
            <!-- Letra "I" dentro do quadrado -->
            <text x="40" y="26" text-anchor="middle" class="logo-icon-{{ $color }}">I</text>
            
            <!-- Texto "INOVA" -->
            <text x="40" y="52" text-anchor="middle" class="logo-text-{{ $color }}">INOVA</text>
        </svg>
    @endif
</div>