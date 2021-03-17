<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;

    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->category->toArray());
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => '' 
        ];

        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];

        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);


        $data = [
            'is_active' => 'a'
        ];

        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testStore()
    {
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test'
        ]);

        $id = $response->json('id');
        $this->category = Category::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($this->category->toArray());

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
        $this->category = factory(Category::class)->create([
            'description' => 'test_description',
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $this->category->id]
            ),
            [
                'name' => 'test',
                'description' => 'test',
                'is_active' => true
            ]
        );

        $id = $response->json('id');
        $this->category = Category::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($this->category->toArray())
            ->assertJsonFragment([
                'name' => 'test',
                'description' => 'test',
                'is_active' => true
            ]);

        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $this->category->id]
            ),
            [
                'name' => 'test',
                'description' => ''
            ]
        );

        $response->assertJsonFragment([
            'description' => null
        ]);

        $this->category->description = 'test';
        $this->category->save();


        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $this->category->id]
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
        $this->assertNotNull(Category::find($this->category->id));

        $response = $this->json(
            'DELETE',
            route('categories.destroy', ['category' => $this->category->id])
        );

        $response
            ->assertStatus(204)
            ->assertNoContent();

        $this->expectException(ModelNotFoundException::class);

        Category::findOrFail($this->category->id);

        $this->assertNotNull(Category::withTrashed()->find($this->category->id));
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }
}
