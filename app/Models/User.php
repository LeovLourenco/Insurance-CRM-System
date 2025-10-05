<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telefone',
        'endereco',
        'cep',
        'password_changed',
        'password_changed_at',
        // ⚠️ SEGURANÇA: Roles/permissions não estão no fillable
        // Devem ser atribuídos via métodos específicos do Spatie
    ];
    
    // ⚠️ CAMPOS SUPER PROTEGIDOS: Nunca podem ser alterados via mass assignment
    protected $guarded = [
        'id', 'email_verified_at', 'remember_token', 'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'password_changed' => 'boolean',
    ];

    // Relacionamentos
    
    /**
     * Corretoras atribuídas a este usuario
     */
    public function corretoras()
    {
        return $this->hasMany(Corretora::class, 'usuario_id');
    }

    /**
     * Configuração do ActivityLog
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'telefone', 'endereco', 'cep',
                'password_changed', 'password_changed_at'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Usuário foi {$eventName}");
    }
}
