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
     * Comparision condition for old position value.
     * In default is for decrement.
     * @var string
     */
    protected $oldComparisionCondition = '>';
    /**
     * Comparision condition for new position value.
     * In default is for decrement.
     * @var string
     */
    protected $newComparisionCondition = '<=';


    /**
     * PositionQuery constructor.
     *
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
        $this->buildComparisionCondition();

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
        return $query->where($this->positionColumn, $this->oldComparisionCondition, $this->oldPosition)
                    ->where($this->positionColumn, $this->newComparisionCondition, $this->position);
    }

    /**
     * Builds the correct comparision condition
     */
    protected function buildComparisionCondition()
    {
        if ($this->increment) {
            $this->oldComparisionCondition = '<';
            $this->newComparisionCondition = '>=';
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
