<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('cast-members.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('cast-members.show', ['cast_member' => $this->castMember->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->castMember->toArray());
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
            'type' => 'a'
        ];

        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testStore()
    {
        $sendData = [
            'name' => 'test',
            'type' => 1
        ];

        $defaultAttributes = [
            'deleted_at' => null
        ];

        $response = $this->assertStore($sendData, $sendData + $defaultAttributes);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $sendData = [
            'name' => 'test_name',
            'type' => 1
        ];

        $this->assertStore($sendData, $sendData + [
            'name' => 'test_name',
            'type' => 1
        ]);
    }

    public function testUpdate()
    {
        $this->castMember = factory(CastMember::class)->create([
            'name' => 'test',
            'type' => 1
        ]);

        $sendData = [
            'name' => 'test1',
            'type' => 2
        ];

        $response = $this->assertUpdate($sendData, $sendData + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
    }

    public function testDelete()
    {
        $this->assertNotNull(CastMember::find($this->castMember->id));

        $response = $this->json(
            'DELETE',
            route('cast-members.destroy', ['cast_member' => $this->castMember->id])
        );

        $response
            ->assertStatus(204)
            ->assertNoContent();
    }

    protected function routeStore()
    {
        return route('cast-members.store');
    }

    protected function routeUpdate()
    {
        return route('cast-members.update', ['cast_member' => $this->castMember->id]);
    }

    protected function model()
    {
        return CastMember::class;
    }
}
