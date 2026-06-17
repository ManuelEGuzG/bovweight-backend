<?php

namespace Tests\Feature;

use App\Models\Finca;
use App\Models\Rol;
use Tests\TestCase;

class PersonaTest extends TestCase
{
    // Verifica que el administrador puede listar todas las personas del sistema
    public function test_admin_puede_listar_personas(): void
    {
        $this->actingAsPersona('Administrador');
        $this->crearPersona('Ganadero');
        $this->crearPersona('Asistente');

        $this->getJson('/api/v1/personas')
            ->assertOk()
            ->assertJsonCount(3);
    }

    // Verifica que el ganadero también tiene acceso al listado de personas
    public function test_ganadero_puede_listar_personas(): void
    {
        $this->actingAsPersona('Ganadero');

        $this->getJson('/api/v1/personas')->assertOk();
    }

    // Verifica que el asistente no tiene permiso para listar personas y recibe 403
    public function test_asistente_no_puede_listar_personas(): void
    {
        $this->actingAsPersona('Asistente');

        $this->getJson('/api/v1/personas')->assertForbidden();
    }

    // Verifica que el veterinario no tiene permiso para listar personas y recibe 403
    public function test_veterinario_no_puede_listar_personas(): void
    {
        $this->actingAsPersona('Veterinario');

        $this->getJson('/api/v1/personas')->assertForbidden();
    }

    // Verifica que el administrador puede crear una persona con todos los datos requeridos
    public function test_admin_crea_persona_con_datos_validos(): void
    {
        $this->actingAsPersona('Administrador');
        $idRol = Rol::where('nombre', 'Asistente')->value('id_rol');

        $this->postJson('/api/v1/personas', [
            'cedula'                  => '999888777',
            'nombre'                  => 'Ana',
            'apellidos'               => 'López García',
            'correo'                  => 'ana@test.com',
            'contrasena'              => 'password123',
            'contrasena_confirmation' => 'password123',
            'id_rol'                  => $idRol,
        ])->assertStatus(201)
          ->assertJsonFragment(['cedula' => '999888777']);
    }

    // Verifica que no se puede crear una persona con una cédula ya registrada en el sistema
    public function test_crear_persona_falla_cedula_duplicada(): void
    {
        $this->actingAsPersona('Administrador');
        $existente = $this->crearPersona('Ganadero');
        $idRol = Rol::where('nombre', 'Ganadero')->value('id_rol');

        $this->postJson('/api/v1/personas', [
            'cedula'                  => $existente->cedula,
            'nombre'                  => 'Pedro',
            'apellidos'               => 'Test',
            'correo'                  => 'pedro@test.com',
            'contrasena'              => 'password123',
            'contrasena_confirmation' => 'password123',
            'id_rol'                  => $idRol,
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['cedula']);
    }

    // Verifica que no se puede crear una persona con un correo ya registrado en el sistema
    public function test_crear_persona_falla_correo_duplicado(): void
    {
        $this->actingAsPersona('Administrador');
        $existente = $this->crearPersona('Ganadero');
        $idRol = Rol::where('nombre', 'Ganadero')->value('id_rol');

        $this->postJson('/api/v1/personas', [
            'cedula'                  => '111222333',
            'nombre'                  => 'Pedro',
            'apellidos'               => 'Test',
            'correo'                  => $existente->correo,
            'contrasena'              => 'password123',
            'contrasena_confirmation' => 'password123',
            'id_rol'                  => $idRol,
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['correo']);
    }

    // Verifica que la creación falla con 422 si no se envía la confirmación de contraseña
    public function test_crear_persona_falla_sin_confirmation_password(): void
    {
        $this->actingAsPersona('Administrador');
        $idRol = Rol::where('nombre', 'Ganadero')->value('id_rol');

        $this->postJson('/api/v1/personas', [
            'cedula'     => '555666777',
            'nombre'     => 'Pedro',
            'apellidos'  => 'Test',
            'correo'     => 'pedro@test.com',
            'contrasena' => 'password123',
            'id_rol'     => $idRol,
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['contrasena']);
    }

    // Verifica que se puede obtener el detalle de una persona específica por su cédula
    public function test_ver_persona_especifica(): void
    {
        $this->actingAsPersona('Administrador');
        $persona = $this->crearPersona('Ganadero');

        $this->getJson("/api/v1/personas/{$persona->cedula}")
            ->assertOk()
            ->assertJsonFragment(['cedula' => $persona->cedula]);
    }

    // Verifica que al eliminar una persona se aplica soft delete y el registro permanece en BD
    public function test_desactivar_persona_soft_delete(): void
    {
        $this->actingAsPersona('Administrador');
        $persona = $this->crearPersona('Ganadero');

        $this->deleteJson("/api/v1/personas/{$persona->cedula}")
            ->assertOk();

        $this->assertSoftDeleted($persona);
    }

    // Verifica que se puede obtener el listado de fincas asignadas a una persona
    public function test_listar_fincas_de_persona(): void
    {
        $this->actingAsPersona('Administrador');
        $persona = $this->crearPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($persona->cedula, ['es_dueno' => true]);

        $this->getJson("/api/v1/personas/{$persona->cedula}/fincas")
            ->assertOk()
            ->assertJsonCount(1);
    }

    // Verifica que se pueden sincronizar las fincas de una persona reemplazando las asignaciones existentes
    public function test_sincronizar_fincas_de_persona(): void
    {
        $this->actingAsPersona('Administrador');
        $persona = $this->crearPersona('Ganadero');
        $finca1 = Finca::factory()->create();
        $finca2 = Finca::factory()->create();

        $this->putJson("/api/v1/personas/{$persona->cedula}/fincas", [
            'fincas' => [
                ['id_finca' => $finca1->id_finca, 'es_dueno' => true],
                ['id_finca' => $finca2->id_finca, 'es_dueno' => false],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('finca_persona', [
            'cedula'   => $persona->cedula,
            'id_finca' => $finca1->id_finca,
            'es_dueno' => true,
        ]);
        $this->assertDatabaseHas('finca_persona', [
            'cedula'   => $persona->cedula,
            'id_finca' => $finca2->id_finca,
            'es_dueno' => false,
        ]);
    }
}
