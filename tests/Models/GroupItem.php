<?php

namespace Tests\Models;


/**
 * @property int    $group
 */
class GroupItem extends AbstractPositionModel
{
    protected $fillable = [
        'name',
        'position',
        'group',
    ];

    public $positionGroup = ['group'];
}
