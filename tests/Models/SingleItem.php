<?php

namespace Tests\Models;


class SingleItem extends AbstractPositionModel
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'position',
    ];
}
