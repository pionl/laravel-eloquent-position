<?php
namespace Pion\Support\Eloquent\Position\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class PositionScopeTrait
 *
 * @method static Builder sorted($way = 'ASC')
 * @method static Builder sortedByDESC()
 *
 * @package Pion\Support\Eloquent\Position\Traits
 */
trait PositionScopeTrait
{
    /**
     * Sorts the results
     *
     * @param Builder $builder
     * @param string  $way
     *
     * @return Builder
     */
    public function scopeSorted($builder, $way = 'ASC')
    {
        return $builder->orderBy($this->getPositionColumn(), $way);
    }

    /**
     * Sorts the results in DESC
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeSortedByDESC($builder)
    {
        return $this->scopeSorted($builder, 'DESC');
    }
}
