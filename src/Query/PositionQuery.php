<?php

namespace Pion\Support\Eloquent\Position\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Pion\Support\Eloquent\Position\Traits\PositionTrait;

class PositionQuery extends AbstractPositionQuery
{
    /**
     * @var string
     */
    protected $position = null;

    /**
     * @var string
     */
    protected $positionColumn = null;

    /**
     * When only the new model is updated, we don`t need to run query
     * @var bool
     */
    protected $shouldRunQuery = false;

    /**
     * Creates the base query and builds the query
     *
     * @param Model|PositionTrait $model
     * @param int                 $position
     */
    public function __construct($model, $position)
    {
        // Store the new position
        $this->position = $position;

        // Get the position column
        $this->positionColumn = $model->getPositionColumn();

        // Build the query
        parent::__construct($model, null, true);
    }

    /**
     * Builds the basic query and appends a where conditions for group is set
     * @return Builder
     */
    protected function buildQuery()
    {
        // Create query
        $query = parent::buildQuery();

        // Get the last position and move the position
        $lastPosition = $query->max($this->positionColumn);

        // If the new position is last position, just update the position or if
        // new position is out of bounds
        if ($this->position >= $lastPosition) {
            $this->model()->setPosition($lastPosition + 1);
        } else {
            $this->shouldRunQuery = true;

            // Set the forced position
            $this->model()->setPosition($this->position);

            // Move other models
            $query->where($this->positionColumn, '>=', $this->position);
        }

        return $query;
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
        if ($this->shouldRunQuery) {
            return $query->increment($this->positionColumn);
        }

        return $this->model()->getPosition();
    }
}
