<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(CastMember::class, 1)->create();
        $castMembers = CastMember::all();
        $this->assertCount(1, $castMembers);
        $memberKey = array_keys($castMembers->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'type',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $memberKey
        );
    }

    public function testCreate()
    {
        $member = CastMember::create(['name' => 'test1', 'type' => 1]);
        $member->refresh();

        $this->assertTrue(CastMember::uuidIsValid($member->id));
        $this->assertEquals('test1', $member->name);

        $member = CastMember::create([
            'name' => 'test1',
            'type' => 1
        ]);

        $this->assertEquals(1, $member->type);

        $member = CastMember::create([
            'name' => 'test1',
            'type' => 2
        ]);

        $this->assertEquals(2, $member->type);
    }

    public function testUpdate()
    {
        $member = factory(CastMember::class)->create([
            'type' => 1
        ])->first();

        $data = [
            'name' => 'test_name_updated',
            'type' => 2
        ];

        $member->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $member->{$key});
        }
    }

    public function testDelete()
    {
        $member = factory(CastMember::class)->create()->first();
        $memberFromDb = CastMember::findOrFail($member->id);
        $this->assertEquals($member->id, $memberFromDb->id);

        $member->delete();
        $this->expectException(ModelNotFoundException::class);
        $memberFromDb = CastMember::findOrFail($member->id);

        $member->restore();
        $this->assertNotNull(CastMember::find($member->id));
    }
}
