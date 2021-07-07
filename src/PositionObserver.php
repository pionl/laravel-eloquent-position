<?php

namespace Pion\Support\Eloquent\Position;

use Pion\Support\Eloquent\Position\Query\AbstractPositionQuery;
use Pion\Support\Eloquent\Position\Query\LastPositionQuery;
use Pion\Support\Eloquent\Position\Query\PositionQuery;
use Pion\Support\Eloquent\Position\Query\MoveQuery;
use Pion\Support\Eloquent\Position\Traits\PositionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Traits PositionObserver
 *
 * Listens for saved state and checks if the position should be update in
 * related entries.
 *
 * @package App\Models\Traits
 */
class PositionObserver
{
    /**
     * @var Dispatcher
     */
    private $events;

    /**
     * PositionObserver constructor.
     *
     * @param Dispatcher $events
     */
    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * Updates the position before saving
     *
     * @param Model|PositionTrait $model
     */
    public function saving($model)
    {
        if ($model->isPositionUpdateDisabled() === false) {
            // Get the position for current and old value
            $position = $model->getPosition();
            
            // Prevent modifying position column when updating and position columns has not changed
            if ($model->exists === true && $model->isDirty($model->getPositionColumn()) === false) {
                return;
            }

            // Get the old position
            $oldPosition = $model->getOriginal($model->getPositionColumn());

            // Check if the position is set
            if (is_null($position) || $position == '') {
                $this->appendLast($model, $oldPosition);
            } elseif (is_null($oldPosition)) {
                $this->forcedPosition($model, $position);
            } else {
                $this->move($model, $position, $oldPosition);
            }
        }
    }

    /**
     * Updates the position before saving
     *
     * @param Model|PositionTrait $model
     */
    public function deleting($model)
    {
        if ($model->isPositionUpdateDisabled() === false) {
            // Get the old position
            $oldPosition = $model->getOriginal($model->getPositionColumn());

            // Append deleted model as last to re-index other models
            $this->appendLast($model, $oldPosition);
        }
    }

    /**
     * Forces the new position, will be overridden if it's out of maximum bounds.
     *
     * @param Model|PositionTrait $model
     * @param int                 $position
     * @param int|null            $oldPosition
     */
    protected function forcedPosition($model, $position, $oldPosition = null)
    {
        // Build the new position
        $query = new PositionQuery($model, $position);

        // Run the query
        $this->runQuery($query, $oldPosition);
    }

    /**
     * Setups the position to be at the end
     *
     * @param Model|PositionTrait $model
     * @param int                 $oldPosition
     */
    protected function appendLast($model, $oldPosition)
    {
        // Build the last position query
        $query = new LastPositionQuery($model, $oldPosition);

        // Run the query
        $this->runQuery($query, $oldPosition);
    }

    /**
     * Moves other entries from the given position
     *
     * @param Model|PositionTrait $model
     * @param int                 $position
     * @param int                 $oldPosition
     */
    protected function move($model, $position, $oldPosition)
    {
        // Check if the position has change and we need to recalculate
        if ($oldPosition != $position) {
            // Build the position query
            $query = new MoveQuery($model, $position, $oldPosition);

            // Run the position query
            $this->runQuery($query, $oldPosition);
        }
    }

    /**
     * Runs the position events and query if can. If positioning event returns
     * false, it will revert the new position to old position.
     *
     * @param AbstractPositionQuery $query
     * @param int                   $oldPosition
     */
    protected function runQuery(AbstractPositionQuery $query, $oldPosition)
    {
        // Fire the validation event
        $eventResponse = $this->firePositioningEvent($query);

        // Ignore updating the position and revert the position to original value
        if ($eventResponse === false) {
            // Update the new position to original position
            $query->model()->setPosition($oldPosition);
        } else {
            // Run the query to update other entries to fix the position order
            $query->run();

            // Fire the final state
            $this->firePositionedEvent($query->model());
        }
    }

    /**
     * Fire the name-spaced validating event.
     *
     * @param AbstractPositionQuery $query
     *
     * @return mixed
     */
    protected function firePositioningEvent($query)
    {
        $model = $query->model();
        return $this->events->until('eloquent.positioning: '.get_class($model), [$model, $query]);
    }

    /**
     * Fire the name-spaced post-validation event.
     *
     * @param Model $model
     *
     * @return void
     */
    protected function firePositionedEvent(Model $model)
    {
        $this->events->dispatch('eloquent.positioned: '.get_class($model), [$model]);
    }
}
