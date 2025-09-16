<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Cotacao;
use Spatie\Permission\Models\Role;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $diretor;
    protected $comercial1;
    protected $comercial2;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'diretor']);
        Role::create(['name' => 'comercial']);
        
        // Criar usuários de teste
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->diretor = User::factory()->create();
        $this->diretor->assignRole('diretor');
        
        $this->comercial1 = User::factory()->create();
        $this->comercial1->assignRole('comercial');
        
        $this->comercial2 = User::factory()->create();
        $this->comercial2->assignRole('comercial');
    }

    /** @test */
    public function comercial_cannot_access_other_comercial_cotacao()
    {
        // Criar cotação do comercial1
        $cotacao = Cotacao::factory()->create(['user_id' => $this->comercial1->id]);
        
        // Tentar acessar como comercial2
        $this->actingAs($this->comercial2)
             ->get(route('cotacoes.show', $cotacao->id))
             ->assertStatus(403);
    }

    /** @test */
    public function comercial_cannot_edit_other_comercial_cotacao()
    {
        $cotacao = Cotacao::factory()->create(['user_id' => $this->comercial1->id]);
        
        $this->actingAs($this->comercial2)
             ->get(route('cotacoes.edit', $cotacao->id))
             ->assertStatus(403);
    }

    /** @test */
    public function comercial_cannot_manipulate_user_id_in_create()
    {
        $this->actingAs($this->comercial1)
             ->post(route('cotacoes.store'), [
                 'corretora_id' => 1,
                 'produto_id' => 1,
                 'segurado_id' => 1,
                 'seguradoras' => [1],
                 'user_id' => $this->comercial2->id, // ⚠️ TENTATIVA DE ATAQUE
             ]);
             
        // Verificar se a cotação foi criada com o user_id correto
        $cotacao = Cotacao::latest()->first();
        $this->assertEquals($this->comercial1->id, $cotacao->user_id);
    }

    /** @test */
    public function comercial_cannot_update_user_id_in_edit()
    {
        $cotacao = Cotacao::factory()->create(['user_id' => $this->comercial1->id]);
        
        $this->actingAs($this->comercial1)
             ->put(route('cotacoes.update', $cotacao->id), [
                 'observacoes' => 'Teste',
                 'status' => 'em_andamento',
                 'user_id' => $this->comercial2->id, // ⚠️ TENTATIVA DE ATAQUE
             ]);
             
        // Verificar se o user_id não foi alterado
        $cotacao->refresh();
        $this->assertEquals($this->comercial1->id, $cotacao->user_id);
    }

    /** @test */
    public function diretor_can_view_all_cotacoes_but_cannot_edit()
    {
        $cotacao = Cotacao::factory()->create(['user_id' => $this->comercial1->id]);
        
        // Diretor pode visualizar
        $this->actingAs($this->diretor)
             ->get(route('cotacoes.show', $cotacao->id))
             ->assertStatus(200);
             
        // Mas não pode editar
        $this->actingAs($this->diretor)
             ->get(route('cotacoes.edit', $cotacao->id))
             ->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_everything()
    {
        $cotacao = Cotacao::factory()->create(['user_id' => $this->comercial1->id]);
        
        // Admin pode ver
        $this->actingAs($this->admin)
             ->get(route('cotacoes.show', $cotacao->id))
             ->assertStatus(200);
             
        // Admin pode editar
        $this->actingAs($this->admin)
             ->get(route('cotacoes.edit', $cotacao->id))
             ->assertStatus(200);
    }

    /** @test */
    public function unauthorized_user_cannot_access_cotacoes()
    {
        $guest = User::factory()->create(); // Usuário sem role
        $cotacao = Cotacao::factory()->create(['user_id' => $this->comercial1->id]);
        
        $this->actingAs($guest)
             ->get(route('cotacoes.index'))
             ->assertStatus(403);
    }

    /** @test */
    public function privilege_escalation_attempt_is_blocked()
    {
        $this->actingAs($this->comercial1)
             ->post(route('cotacoes.store'), [
                 'corretora_id' => 1,
                 'produto_id' => 1,
                 'segurado_id' => 1,
                 'seguradoras' => [1],
                 'role' => 'admin', // ⚠️ TENTATIVA DE ESCALAÇÃO
                 'permissions' => ['all'], // ⚠️ TENTATIVA DE ESCALAÇÃO
             ]);
             
        // Verificar que o usuário ainda é comercial
        $this->comercial1->refresh();
        $this->assertTrue($this->comercial1->hasRole('comercial'));
        $this->assertFalse($this->comercial1->hasRole('admin'));
    }

    /** @test */
    public function comercial_cannot_delete_other_comercial_cotacao()
    {
        $cotacao = Cotacao::factory()->create(['user_id' => $this->comercial1->id]);
        
        $this->actingAs($this->comercial2)
             ->delete(route('cotacoes.destroy', $cotacao->id))
             ->assertStatus(403);
             
        // Cotação deve ainda existir
        $this->assertDatabaseHas('cotacoes', ['id' => $cotacao->id]);
    }
}