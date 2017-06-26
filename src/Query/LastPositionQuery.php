<?php
namespace Pion\Support\Eloquent\Position\Query;

use Pion\Support\Eloquent\Position\Traits\PositionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LastPositionQuery
 *
 * Runs the query for last position and sets the model position if the position has
 * changed
 *
 * @package Pion\Support\Eloquent\Position\Query
 */
class LastPositionQuery extends AbstractPositionQuery
{
    /**
     * Creates the base query and builds the query
     *
     * @param Model|PositionTrait $model
     * @param int                 $oldPosition
     */
    public function __construct($model, $oldPosition)
    {
        parent::__construct($model, $oldPosition, true);
    }

    /**
     * Runs the query for last position and sets the model position if the position has
     * changed
     *
     * @param Builder $query
     *
     * @return int the last position returned
     */
    public function runQuery($query)
    {
        // Get the last position and move the position
        $lastPosition = $query->max($this->model()->getPositionColumn());

        // Check if the last position is not same as original position - the same object
        if ($lastPosition != $this->oldPosition) {
            $this->model()->setPosition($lastPosition + 1);
        }

        return $lastPosition;
    }
}
