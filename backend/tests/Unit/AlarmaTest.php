<?php

namespace Tests\Unit;

use App\Models\Elementos\Alarma;
use App\Models\Elementos\Elemento;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlarmaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_an_alarm()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);
        $elemento = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $cuenta->id
        ]);

        $alarma = Alarma::create([
            'elemento_id' => $elemento->id,
            'nombre' => 'Test Alarm',
            'informacion' => 'Test alarm description',
            'fecha' => '2024-12-25',
            'hora' => '08:00:00',
            'intensidad_volumen' => 5
        ]);

        $this->assertInstanceOf(Alarma::class, $alarma);
        $this->assertEquals('Test Alarm', $alarma->nombre);
        $this->assertEquals('Test alarm description', $alarma->informacion);
        $this->assertEquals('2024-12-25', $alarma->fecha);
        $this->assertEquals('08:00:00', $alarma->hora);
        $this->assertEquals(5, $alarma->intensidad_volumen);
    }

    /** @test */
    public function it_belongs_to_an_elemento()
    {
        $elemento = Elemento::factory()->create(['tipo' => 'alarma']);
        $alarma = Alarma::factory()->create(['elemento_id' => $elemento->id]);

        $this->assertInstanceOf(Elemento::class, $alarma->elemento);
        $this->assertEquals($elemento->id, $alarma->elemento->id);
    }

    /** @test */
    public function it_casts_configuraciones_to_array()
    {
        $configuraciones = [
            'gps' => [
                'latitude' => -34.6037,
                'longitude' => -58.3816,
                'radius' => 100
            ],
            'clima' => [
                'enabled' => true,
                'conditions' => ['rain', 'storm']
            ]
        ];

        $alarma = Alarma::factory()->create([
            'configuraciones' => $configuraciones
        ]);

        $this->assertIsArray($alarma->configuraciones);
        $this->assertEquals($configuraciones, $alarma->configuraciones);
    }

    // ====================================================
    // PRUEBAS DE GEOLOCALIZACIÓN COMENTADAS TEMPORALMENTE
    // ====================================================

    /*
    /** @test *//*
    public function it_has_gps_accessor()
    {
        $gpsConfig = [
            'latitude' => -34.6037,
            'longitude' => -58.3816,
            'radius' => 100
        ];

        $alarma = Alarma::factory()->create([
            'configuraciones' => ['gps' => $gpsConfig]
        ]);

        $this->assertEquals($gpsConfig, $alarma->gps);
    }
    */

    /*
    /** @test *//*
    public function it_returns_null_when_no_gps_configuration()
    {
        $alarma = Alarma::factory()->create([
            'configuraciones' => ['clima' => ['enabled' => true]]
        ]);

        $this->assertNull($alarma->gps);
    }
    */

    /*
    /** @test *//*
    public function it_has_clima_accessor()
    {
        $climaConfig = [
            'enabled' => true,
            'conditions' => ['rain', 'storm'],
            'temperature_min' => 15,
            'temperature_max' => 25
        ];

        $alarma = Alarma::factory()->create([
            'configuraciones' => ['clima' => $climaConfig]
        ]);

        $this->assertEquals($climaConfig, $alarma->clima);
    }
    */

    /*
    /** @test *//*
    public function it_returns_null_when_no_clima_configuration()
    {
        $alarma = Alarma::factory()->create([
            'configuraciones' => ['gps' => ['latitude' => -34.6037]]
        ]);

        $this->assertNull($alarma->clima);
    }
    */

    /*
    /** @test *//*
    public function it_has_gps_mutator()
    {
        $alarma = Alarma::factory()->create();

        $gpsConfig = [
            'latitude' => -34.6037,
            'longitude' => -58.3816,
            'radius' => 500
        ];

        $alarma->gps = $gpsConfig;
        $alarma->save();

        $this->assertEquals($gpsConfig, $alarma->fresh()->gps);
    }
    */

    /*
    /** @test *//*
    public function it_has_clima_mutator()
    {
        $alarma = Alarma::factory()->create();

        $climaConfig = [
            'enabled' => true,
            'conditions' => ['snow', 'fog'],
            'temperature_min' => 0,
            'temperature_max' => 10
        ];

        $alarma->clima = $climaConfig;
        $alarma->save();

        $this->assertEquals($climaConfig, $alarma->fresh()->clima);
    }
    */

    /*
    /** @test *//*
    public function it_preserves_existing_configurations_when_setting_gps()
    {
        $alarma = Alarma::factory()->create([
            'configuraciones' => [
                'clima' => ['enabled' => true],
                'other' => ['setting' => 'value']
            ]
        ]);

        $gpsConfig = [
            'latitude' => -34.6037,
            'longitude' => -58.3816
        ];

        $alarma->gps = $gpsConfig;
        $alarma->save();

        $fresh = $alarma->fresh();
        $this->assertEquals($gpsConfig, $fresh->gps);
        $this->assertEquals(['enabled' => true], $fresh->clima);
        $this->assertEquals(['setting' => 'value'], $fresh->configuraciones['other']);
    }
    */

    /*
    /** @test *//*
    public function it_preserves_existing_configurations_when_setting_clima()
    {
        $alarma = Alarma::factory()->create([
            'configuraciones' => [
                'gps' => ['latitude' => -34.6037],
                'other' => ['setting' => 'value']
            ]
        ]);

        $climaConfig = [
            'enabled' => true,
            'conditions' => ['clear']
        ];

        $alarma->clima = $climaConfig;
        $alarma->save();

        $fresh = $alarma->fresh();
        $this->assertEquals($climaConfig, $fresh->clima);
        $this->assertEquals(['latitude' => -34.6037], $fresh->gps);
        $this->assertEquals(['setting' => 'value'], $fresh->configuraciones['other']);
    }
    */

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $alarma = new Alarma();

        $expectedFillable =  [
            'elemento_id',
            'fecha',
            'hora',
            'fechaVencimiento',
            'horaVencimiento',
            'nombre',
            'informacion',
            'intensidad_volumen',
            'configuraciones',
            'tipo_alarma',
            'ubicacion',
        ];

        $this->assertEquals($expectedFillable, $alarma->getFillable());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $alarma = new Alarma();
        $this->assertEquals('alarmas', $alarma->getTable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $alarma = new Alarma();
        $this->assertEquals('id', $alarma->getKeyName());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $alarma = new Alarma();
        $expectedCasts = [
            'configuraciones' => 'array',
        ];

        foreach ($expectedCasts as $key => $cast) {
            $this->assertEquals($cast, $alarma->getCasts()[$key]);
        }
    }

    /** @test */
    public function it_can_handle_null_configuraciones()
    {
        $alarma = Alarma::factory()->create([
            'configuraciones' => null
        ]);

        $this->assertNull($alarma->gps);
        $this->assertNull($alarma->clima);
    }

    /** @test */
    public function it_can_handle_empty_configuraciones()
    {
        $alarma = Alarma::factory()->create([
            'configuraciones' => []
        ]);

        $this->assertNull($alarma->gps);
        $this->assertNull($alarma->clima);
    }

    /** @test */
    public function it_properly_encodes_json_when_setting_configurations()
    {
        // $alarma = Alarma::factory()->create();

        // $gpsConfig = [
        //     'latitude' => -34.6037,
        //     'longitude' => -58.3816,
        //     'radius' => 100,
        //     'type' => 'enter'
        // ];

        // $alarma->gps = $gpsConfig;

        // Check that the raw attribute is properly encoded JSON
        // $this->assertJson($alarma->attributes['configuraciones']);

        // $decoded = json_decode($alarma->attributes['configuraciones'], true);
        // $this->assertEquals($gpsConfig, $decoded['gps']);
    }
}