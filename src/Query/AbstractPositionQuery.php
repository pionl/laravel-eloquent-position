<?php
namespace Pion\Support\Eloquent\Position\Query;

use Pion\Support\Eloquent\Position\Traits\PositionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractPositionQuery
{
    /**
     * @var PositionTrait|Model
     */
    protected $model;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var int
     */
    protected $oldPosition;

    /**
     * Creates the base query and builds the query
     *
     * @param Model|PositionTrait $model
     * @param string              $oldPosition
     * @param bool                $buildQuery
     */
    public function __construct($model, $oldPosition, $buildQuery = true)
    {
        // Store the data
        $this->model = $model;
        $this->oldPosition = $oldPosition;

        // Prepare the query
        if ($buildQuery) {
            $this->query = $this->buildQuery();
        }
    }

    //region Query

    /**
     * Runs the query for position change
     *
     * @return mixed
     */
    public function run()
    {
        return $this->runQuery($this->query);
    }

    /**
     * Runs the given query
     *
     * @param Builder $query
     *
     * @return mixed
     */
    abstract public function runQuery($query);

    /**
     * Builds the basic query and appends a where conditions for group is set
     * @return Builder
     */
    protected function buildQuery()
    {
        // Create query
        $query = $this->model->newPositionQuery();

        // Get the position group
        $group = $this->getPositionGroup();

        // Handle the group as array to support multiple columns
        if (is_array($group)) {
            foreach ($group as $column) {
                $this->applyGroupWhere($query, $column);
            }
        }

        return $query;
    }

    //endregion


    //region Group settings
    /**
     * @return array|null
     */
    protected function getPositionGroup()
    {
        // Apply the groups columns to filter
        return $this->model->getPositionGroup();
    }

    /**
     * Applies where condition for given column into the query. Takes value
     * from the model.
     *
     * @param Builder $query
     * @param string  $column
     *
     * @return Builder
     */
    protected function applyGroupWhere($query, $column)
    {
        // Get value
        $value = $this->model->{$column};

        // Add support to add null condition - column must be null
        if (is_null($value)) {
            return $query->whereNull($column);
        }

        // Apply exact value condition
        return $query->where($column, $value);
    }

    //endregion

    //region Getters

    /**
     * @return Model|PositionTrait
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function oldPosition()
    {
        return $this->oldPosition;
    }

    /**
     * Returns the current query
     * @return Builder
     */
    public function query()
    {
        return $this->query;
    }

    //endregion
}
