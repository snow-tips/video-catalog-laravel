<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

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
        $sendData = [
            'name' => 'test'
        ];

        $defaultAttributes = [
            'description' => null,
            'is_active' => true,
            'deleted_at' => null
        ];

        $response = $this->assertStore($sendData, $sendData + $defaultAttributes);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $sendData = [
            'name' => 'test',
            'description' => 'description',
            'is_active' => false
        ];

        $this->assertStore($sendData, $sendData + [
            'description' => 'description',
            'is_active' => false
        ]);
    }

    public function testUpdate()
    {
        $sendData = [
            'name' => 'test',
            'description' => 'description',
            'is_active' => true
        ];
        
        $response = $this->assertUpdate($sendData, $sendData + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $sendData = [
            'name' => 'test',
            'description' => '',
            'is_active' => true
        ];
        
        $this->assertUpdate($sendData, array_merge($sendData, ['description' => null]));

        $sendData['description'] = 'test';
        $this->assertUpdate($sendData, array_merge($sendData, ['description' => 'test']));

        $sendData['description'] = null;
        $this->assertUpdate($sendData, array_merge($sendData, ['description' =>  null]));
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
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }
}
