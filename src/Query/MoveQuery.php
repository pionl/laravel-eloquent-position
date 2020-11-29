<?php

namespace Pion\Support\Eloquent\Position\Query;

use Pion\Support\Eloquent\Position\Traits\PositionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MoveQuery extends AbstractPositionQuery
{
    /**
     * @var bool
     */
    protected $increment = false;

    /**
     * @var string
     */
    protected $positionColumn = null;

    /**
     * @var int
     */
    protected $position;

    /**
     * Comparison condition for old position value.
     * In default is for decrement.
     * @var string
     */
    protected $oldComparisonCondition = '>';
    /**
     * Comparison condition for new position value.
     * In default is for decrement.
     * @var string
     */
    protected $newComparisonCondition = '<=';


    /**
     * @param Model|PositionTrait $model
     * @param int                 $position
     * @param int|null            $oldPosition
     */
    public function __construct($model, $position, $oldPosition)
    {
        parent::__construct($model, $oldPosition, false);

        $this->position = $position;

        // Indicate if si the increment/decrement
        $this->increment = $position < $oldPosition;

        // Get the column for position to build correct query
        $this->positionColumn = $model->getPositionColumn();

        // Build the comparision condition
        $this->buildComparisonCondition();

        // Prepare the query
        $this->query = $this->buildQuery();
    }

    //region Query

    /**
     * Runs the increment/decrement query
     *
     * @param Builder $query
     *
     * @return int
     */
    public function runQuery($query)
    {
        if ($this->increment) {
            return $query->increment($this->positionColumn);
        } else {
            // Get the last position and move the position
            $lastPosition = $query->max($this->positionColumn) ?: 0;

            // If the set position is out of the bounds of current items, force new position
            if ($this->position > $lastPosition) {
                $this->position = $lastPosition;
                $this->model()->setPosition($this->position);
            }

            return $query->decrement($this->positionColumn);
        }
    }

    /**
     * Builds the basic query with where condition (includes the position conditions)
     * @return Builder
     */
    protected function buildQuery()
    {
        // Create query
        $query = parent::buildQuery();

        // Build the where condition for the position. This will ensure to update only required rows
        return $query->where($this->positionColumn, $this->oldComparisonCondition, $this->oldPosition)
            ->where($this->positionColumn, $this->newComparisonCondition, $this->position);
    }

    /**
     * Builds the correct comparison condition
     */
    protected function buildComparisonCondition()
    {
        if ($this->increment) {
            $this->oldComparisonCondition = '<';
            $this->newComparisonCondition = '>=';
        }
    }
    //endregion

    //region Getters

    /**
     * @return mixed
     */
    public function position()
    {
        return $this->position;
    }
    //endregion
}
