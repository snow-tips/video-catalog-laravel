<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidationDataInPost()
    {
        $response = $this->json('POST', route('categories.store'), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('categories.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationNotBoolean($response);
    }

    public function testInvalidationDataInPut()
    {
        $category = factory(Category::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id]
            ),
            []
        );
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id]
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
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test'
        ]);

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());

        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test',
            'description' => 'test_description',
            'is_active' => false
        ]);

        $response
            ->assertJsonFragment([
                'description' => 'test_description',
                'is_active' => false
            ]);
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description' => 'test_description',
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id]
            ),
            [
                'name' => 'test',
                'description' => 'test',
                'is_active' => true
            ]
        );

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'name' => 'test',
                'description' => 'test',
                'is_active' => true
            ]);

        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id]
            ),
            [
                'name' => 'test',
                'description' => ''
            ]
        );

        $response->assertJsonFragment([
            'description' => null
        ]);

        $category->description = 'test';
        $category->save();


        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id]
            ),
            [
                'name' => 'test',
                'description' => null
            ]
        );

        $response->assertJsonFragment([
            'description' => null
        ]);
    }

    public function testDelete()
    {
        $category = factory(Category::class)->create();
        $this->assertNotNull(Category::find($category->id));

        $response = $this->json(
            'DELETE',
            route('categories.destroy', ['category' => $category->id])
        );

        $response
            ->assertStatus(204)
            ->assertNoContent();

        $this->expectException(ModelNotFoundException::class);

        Category::findOrFail($category->id);

        $this->assertNotNull(Category::withTrashed()->find($category->id));
    }
}
