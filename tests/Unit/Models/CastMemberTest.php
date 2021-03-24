<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use Tests\TestCase;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Uuid;

class CastMemberTest extends TestCase
{
    private $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = new CastMember();
    }

    public function testFillableAttribute()
    {
        $fillable = ['name', 'type'];
        $this->assertEquals($fillable, $this->member->getFillable());
    }

    public function testCastsAttribute()
    {
        $casts = [
            'id' => 'string',
            'type' => 'integer'
        ];
        $this->assertEquals($casts, $this->member->getCasts());
    }

    public function testDatesAttibute()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];

        foreach($dates as $date) {;
            $this->assertContains($date, $this->member->getDates());
        };

        $this->assertEqualsCanonicalizing($dates, $this->member->getDates());

        $this->assertCount(count($dates), $this->member->getDates());
    }

    public function testIfIncrementAttributeIsDisabled()
    {
        $this->assertFalse($this->member->incrementing);
    }

    public function testIfUseTraits()
    {
        $traits = [
            SoftDeletes::class, Uuid::class
        ];
        $castMemberTraits = array_keys(class_uses(CastMember::class));
        $this->assertEquals($traits, $castMemberTraits);
    }
}
