<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionClass;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;

class BasicCrudControllerTest extends TestCase
{
    private $controller;
    private $category;
    private $ID_LAST_CATEGORY_STUB_CREATED;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryControllerStub();

        /** @var CategoryStub $category */
        $this->category = CategoryStub::create(
            [
                'name' => 'test_name',
                'description' => 'test_description'
            ]
        );
        $this->category['deleted_at'] = null;

        $this->ID_LAST_CATEGORY_STUB_CREATED = $this->category->id;
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {   
        $result = $this->controller->index()->toArray();
        $this->assertEquals([$this->category->toArray()], $result);
    }

    public function testShow()
    {   
        $result = $this->controller->show($this->category->id)->toArray();
        $this->assertEquals($this->category->toArray(), $result);
    }

    public function testInvalidationDataInStore()
    {
        /** @var Request $request */
        $request = Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);

        $this->expectException(ValidationException::class);
    
        $this->controller->store($request);
    }

    public function testStore()
    {
        /** @var Request $request */
        $request = Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name', 'description' => 'test_description']);
    
        $object = $this->controller->store($request);
        $this->assertEquals(
            $object->toArray(),
            CategoryStub::find($this->ID_LAST_CATEGORY_STUB_CREATED + 1)->toArray()
        );
    }

    public function testUpdate()
    {
        /** @var Request $request */
        $request = Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name', 'description' => 'test_description']);
    
        $object = $this->controller->update($request, $this->category->id);
        $this->assertEquals(
            $object->toArray(),
            CategoryStub::find($this->category->id)->toArray()
        );
    }

    public function testDestroy()
    {   
        $response = $this->controller->destroy($this->category->id);
        $this
            ->createTestResponse($response)
            ->assertStatus(204);
        $this->assertCount(0, CategoryStub::all());
    }

    public function testIfFindOrFailFetchModel()
    {
        $reflectionClass = new ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [ $this->category->id ]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }
    
    public function testIfFindOrFailThrowExceptionWhenInvalidId()
    {
        $reflectionClass = new ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $INVALID_ID = -999;

        $this->expectException(ModelNotFoundException::class);
        $reflectionMethod->invokeArgs($this->controller, [ $INVALID_ID ]);
    }
}
