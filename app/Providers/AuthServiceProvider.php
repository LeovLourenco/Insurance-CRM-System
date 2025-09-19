<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Cotacao' => 'App\Policies\CotacaoPolicy',
        'App\Models\CotacaoSeguradora' => 'App\Policies\CotacaoSeguradoraPolicy',
        'App\Models\Produto' => 'App\Policies\ProdutoPolicy',
        'App\Models\Seguradora' => 'App\Policies\SeguradoraPolicy',
        'App\Models\Corretora' => 'App\Policies\CorretoraPolicy',
        'App\Models\Segurado' => 'App\Policies\SeguradoPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
