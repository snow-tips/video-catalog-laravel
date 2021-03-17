<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testInvalidationDataInPost()
    {
        $response = $this->json('POST', route('genres.store'), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('genres.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationNotBoolean($response);
    }

    public function testInvalidationDataInPut()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'genres.update',
                ['genre' => $genre->id]
            ),
            []
        );
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'PUT',
            route(
                'genres.update',
                ['genre' => $genre->id]
            ),
            [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]
        );
        $this->assertInvalidationMax($response);
        $this->assertInvalidationNotBoolean($response);
    }

    protected function assertInvalidationRequired($response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('name')
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    protected function assertInvalidationMax($response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('name')
            ->assertJsonFragment([
                Lang::get('validation.max.string', [
                    'attribute' => 'name',
                    'max' => 255
                ])
            ]);
    }

    protected function assertInvalidationNotBoolean($response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('is_active')
            ->assertJsonFragment([
                Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    public function testStore()
    {
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test'
        ]);

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());

        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test1',
            'is_active' => false
        ]);

        $response
            ->assertJsonFragment([
                'name' => 'test1',
                'is_active' => false
            ]);
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'name' => 'test_name',
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route(
                'genres.update',
                ['genre' => $genre->id]
            ),
            [
                'name' => 'test',
                'is_active' => true
            ]
        );

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name' => 'test',
                'is_active' => true
            ]);

        $response = $this->json(
            'PUT',
            route(
                'genres.update',
                ['genre' => $genre->id]
            ),
            [
                'name' => '',
            ]
        );

        $this->assertInvalidationRequired($response);

        $genre->name = 'test';
        $genre->save();


        $response = $this->json(
            'PUT',
            route(
                'genres.update',
                ['genre' => $genre->id]
            ),
            [
                'name' => null
            ]
        );

        $this->assertInvalidationRequired($response);
    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create();
        $this->assertNotNull(Genre::find($genre->id));

        $response = $this->json(
            'DELETE',
            route('genres.destroy', ['genre' => $genre->id])
        );

        $response
            ->assertStatus(204)
            ->assertNoContent();

        $this->expectException(ModelNotFoundException::class);

        Genre::findOrFail($genre->id);
        
        $this->assertNotNull(Genre::withTrashed()->find($genre->id));
    }
}
