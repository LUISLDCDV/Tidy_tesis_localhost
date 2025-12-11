<?php

namespace Tests\Unit;

use App\Models\Elementos\Nota;
use App\Models\Elementos\Elemento;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsTiposNotas;

class NotaTest extends TestCase
{
    use RefreshDatabase, SeedsTiposNotas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTiposNotas();
    }

    /** @test */
    public function it_can_create_a_note()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);
        $elemento = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $cuenta->id
        ]);

        $nota = Nota::create([
            'elemento_id' => $elemento->id,
            'nombre' => 'Test Note',
            'informacion' => 'Test note description',
            'contenido' => [
                'text' => 'This is the note content',
                'formatting' => ['bold'],
                'tags' => ['test', 'sample']
            ],
            'fecha' => '2024-12-25',
            'clave' => 'secret123'
        ]);

        $this->assertInstanceOf(Nota::class, $nota);
        $this->assertEquals('Test Note', $nota->nombre);
        $this->assertEquals('Test note description', $nota->informacion);
        $this->assertEquals('2024-12-25', $nota->fecha);
        $this->assertEquals('secret123', $nota->clave);
    }

    /** @test */
    public function it_belongs_to_an_elemento()
    {
        $elemento = Elemento::factory()->create(['tipo' => 'nota']);
        $nota = Nota::factory()->create(['elemento_id' => $elemento->id]);

        $this->assertInstanceOf(Elemento::class, $nota->elemento);
        $this->assertEquals($elemento->id, $nota->elemento->id);
    }

    /** @test */
    public function it_casts_contenido_to_array()
    {
        $contenido = [
            'text' => 'This is the note content',
            'formatting' => ['bold', 'italic'],
            'tags' => ['important', 'work'],
            'attachments' => [
                ['type' => 'image', 'url' => 'image.jpg'],
                ['type' => 'link', 'url' => 'https://example.com']
            ]
        ];

        $nota = Nota::factory()->create([
            'contenido' => $contenido
        ]);

        $this->assertIsArray($nota->contenido);
        $this->assertEquals($contenido, $nota->contenido);
    }

    /** @test */
    public function it_can_handle_null_contenido()
    {
        $nota = Nota::factory()->create([
            'contenido' => null
        ]);

        $this->assertNull($nota->contenido);
    }

    /** @test */
    public function it_can_handle_empty_contenido()
    {
        $nota = Nota::factory()->create([
            'contenido' => []
        ]);

        $this->assertIsArray($nota->contenido);
        $this->assertEmpty($nota->contenido);
    }

    /** @test */
    public function it_can_access_text_content()
    {
        $nota = Nota::factory()->create([
            'contenido' => [
                'text' => 'This is the main text content',
                'other' => 'data'
            ]
        ]);

        $this->assertEquals('This is the main text content', $nota->contenido['text']);
    }

    /** @test */
    public function it_can_access_tags()
    {
        $tags = ['work', 'important', 'meeting'];

        $nota = Nota::factory()->create([
            'contenido' => [
                'text' => 'Note with tags',
                'tags' => $tags
            ]
        ]);

        $this->assertEquals($tags, $nota->contenido['tags']);
        $this->assertContains('work', $nota->contenido['tags']);
        $this->assertContains('important', $nota->contenido['tags']);
    }

    /** @test */
    public function it_can_access_formatting_options()
    {
        $formatting = ['bold', 'italic', 'underline'];

        $nota = Nota::factory()->create([
            'contenido' => [
                'text' => 'Formatted note',
                'formatting' => $formatting
            ]
        ]);

        $this->assertEquals($formatting, $nota->contenido['formatting']);
        $this->assertContains('bold', $nota->contenido['formatting']);
    }

    /** @test */
    public function it_can_store_attachments()
    {
        $attachments = [
            ['type' => 'image', 'url' => 'photo.jpg', 'name' => 'Photo'],
            ['type' => 'document', 'url' => 'doc.pdf', 'name' => 'Document'],
            ['type' => 'link', 'url' => 'https://example.com', 'title' => 'External Link']
        ];

        $nota = Nota::factory()->create([
            'contenido' => [
                'text' => 'Note with attachments',
                'attachments' => $attachments
            ]
        ]);

        $this->assertEquals($attachments, $nota->contenido['attachments']);
        $this->assertCount(3, $nota->contenido['attachments']);
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $nota = new Nota();

        $expectedFillable = [
            'elemento_id',
            'tipo_nota_id',
            'fecha',
            'nombre',
            'informacion',
            'contenido',
            'clave',
        ];

        $this->assertEquals($expectedFillable, $nota->getFillable());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $nota = new Nota();
        $this->assertEquals('notas', $nota->getTable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $nota = new Nota();
        $this->assertEquals('id', $nota->getKeyName());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $nota = new Nota();
        $expectedCasts = [
            'contenido' => 'array',
        ];

        foreach ($expectedCasts as $key => $cast) {
            $this->assertEquals($cast, $nota->getCasts()[$key]);
        }
    }

    /** @test */
    public function it_can_update_content_preserving_structure()
    {
        $nota = Nota::factory()->create([
            'contenido' => [
                'text' => 'Original text',
                'tags' => ['original'],
                'formatting' => ['bold']
            ]
        ]);

        // Update only text, keeping other properties
        $newContent = $nota->contenido;
        $newContent['text'] = 'Updated text';
        $newContent['tags'][] = 'updated';

        $nota->update(['contenido' => $newContent]);

        $this->assertEquals('Updated text', $nota->fresh()->contenido['text']);
        $this->assertContains('original', $nota->fresh()->contenido['tags']);
        $this->assertContains('updated', $nota->fresh()->contenido['tags']);
        $this->assertEquals(['bold'], $nota->fresh()->contenido['formatting']);
    }

    /** @test */
    public function it_can_handle_complex_nested_content()
    {
        $complexContent = [
            'sections' => [
                [
                    'id' => 1,
                    'title' => 'Introduction',
                    'content' => 'This is the intro',
                    'formatting' => ['bold']
                ],
                [
                    'id' => 2,
                    'title' => 'Details',
                    'content' => 'This is the details section',
                    'subsections' => [
                        ['title' => 'Sub 1', 'content' => 'Sub content 1'],
                        ['title' => 'Sub 2', 'content' => 'Sub content 2']
                    ]
                ]
            ],
            'metadata' => [
                'version' => 1,
                'lastModified' => '2024-12-25T10:00:00Z',
                'wordCount' => 150
            ]
        ];

        $nota = Nota::factory()->create([
            'contenido' => $complexContent
        ]);

        $this->assertEquals($complexContent, $nota->contenido);
        $this->assertEquals('Introduction', $nota->contenido['sections'][0]['title']);
        $this->assertEquals(2, count($nota->contenido['sections'][1]['subsections']));
        $this->assertEquals(1, $nota->contenido['metadata']['version']);
    }

    /** @test */
    public function it_properly_encodes_and_decodes_json_content()
    {
        $nota = Nota::factory()->create();

        $content = [
            'text' => 'Test content with special chars: àáâãäå',
            'unicode' => '🌟⭐✨',
            'numbers' => [1, 2, 3.14, -5],
            'boolean' => true,
            'null_value' => null
        ];

        $nota->contenido = $content;
        $nota->save();

        // Refresh from database
        $fresh = $nota->fresh();

        $this->assertEquals($content['text'], $fresh->contenido['text']);
        $this->assertEquals($content['unicode'], $fresh->contenido['unicode']);
        $this->assertEquals($content['numbers'], $fresh->contenido['numbers']);
        $this->assertTrue($fresh->contenido['boolean']);
        $this->assertNull($fresh->contenido['null_value']);
    }
}