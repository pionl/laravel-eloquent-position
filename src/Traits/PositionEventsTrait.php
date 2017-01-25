<?php
namespace Pion\Support\Eloquent\Position\Traits;

/**
 * Trait PositionEventsTrait
 *
 * Event register methods
 *
 * @method void registerModelEvent($event, $callback)
 *
 * @package App\Models\Traits
 */
trait PositionEventsTrait
{
    /**
     * Register a positioning model event with the dispatcher.
     *
     * You can add more query statements into the $query->query()
     *
     * function ($model, AbstractPositionQuery $query) {}
     *
     * @param \Closure|string $callback Closure that will receive model and AbstractPositionQuery
     *
     * @return void
     */
    public static function positioning($callback)
    {
        static::registerModelEvent('positioning', $callback);
    }

    /**
     * Register a positioned model event with the dispatcher.
     *
     * function ($model) {}
     *
     * @param \Closure|string $callback Closure that will receive model
     *
     * @return void
     */
    public static function positioned($callback)
    {
        static::registerModelEvent('positioned', $callback);
    }
}
