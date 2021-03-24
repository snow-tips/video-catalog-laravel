<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
        ];

        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidationMax()
    {

        $data = [
            'title' => str_repeat('a', 256)
        ];

        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {

        $data = [
            'duration' => 'NOT_INTEGER'
        ];

        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField()
    {

        $data = [
            'year_launched' => 'NOT_YEAR_FORMAT'
        ];

        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' =>  'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' =>  'Y']);
    }

    public function testInvalidationBoolean()
    {

        $data = [
            'opened' => 'NOT_BOOLEAN'
        ];

        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingFieldIn()
    {

        $data = [
            'rating' => 'INVALID_RATING'
        ];

        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testStore()
    {
        $sendData = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => ''
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
        $this->assertNotNull(Video::find($this->video->id));

        $response = $this->json(
            'DELETE',
            route('videos.destroy', ['video' => $this->video->id])
        );

        $response
            ->assertStatus(204)
            ->assertNoContent();
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
