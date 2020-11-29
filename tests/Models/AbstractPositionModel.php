<?php


namespace Tests\Models;


use Illuminate\Database\Eloquent\Model;
use Pion\Support\Eloquent\Position\Traits\PositionTrait;


/**
 * Only for tests
 * @property string $name
 * @property int    $position
 * @property int    $group
 */
abstract class AbstractPositionModel extends Model
{
    use PositionTrait;

    public $timestamps = false;

}
