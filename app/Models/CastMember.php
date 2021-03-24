<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use SoftDeletes, Traits\Uuid;

    const TYPE_ACTOR = 1;
    const TYPE_DIRECTOR = 2;

    protected $fillable = ['name', 'type'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id' => 'string',
        'type' => 'integer'
    ];

    public static function getTypes()
    {
        return [
            self::TYPE_ACTOR,
            self::TYPE_DIRECTOR
        ];
    }

    public $incrementing = false;
}
