<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use Tests\TestCase;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Uuid;

class CategoryUnitTest extends TestCase
{
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = new Category();
    }

    public function testFillableAttribute()
    {
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $this->category->getFillable());
    }

    public function testCastsAttribute()
    {
        $casts = [
            'id' => 'string',
            'is_active' => 'boolean'
        ];
        $this->assertEquals($casts, $this->category->getCasts());
    }

    public function testDatesAttibute()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];

        foreach($dates as $date) {;
            $this->assertContains($date, $this->category->getDates());
        };

        $this->assertEqualsCanonicalizing($dates, $this->category->getDates());

        $this->assertCount(count($dates), $this->category->getDates());
    }

    public function testIfIncrementAttributeIsDisabled()
    {
        $this->assertFalse($this->category->incrementing);
    }

    public function testIfUseTraits()
    {
        $traits = [
            SoftDeletes::class, Uuid::class
        ];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);
    }
}
