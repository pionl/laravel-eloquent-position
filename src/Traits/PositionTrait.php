<?php
namespace Pion\Support\Eloquent\Position\Traits;

use Pion\Support\Eloquent\Position\PositionObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait PositionTrait
 *
 * @property array        attributes
 * @property string       positionColumn        to enable overriding for the position column
 * @property boolean      disablePositionUpdate disables the updated of other entries
 * @property string|array positionGroup         builds a filter from columns for position calculation. Supports single
 *                                              column or multiple columns
 * ### Fix warnings
 * @method static void observe($className)
 * @method Builder newQuery()
 */
trait PositionTrait
{
    use BasePositionTrait, PositionScopeTrait;

    /**
     * Boot the position observer
     */
    public static function bootPositionTrait()
    {
        // Observe the model changes with app context
        static::observe(app(PositionObserver::class));
    }

    //region Helpers

    /**
     * Builds the position query. Uses `newQuery` method.
     *
     * @return Builder
     *
     * @uses Model::newQuery()
     */
    public function newPositionQuery()
    {
        return $this->newQuery();
    }

    /**
     * Returns the position group settings
     *
     * @return string|array|null
     */
    public function getPositionGroup()
    {
        return $this->positionOption('positionGroup', function ($group) {

            // Convert single column into array
            if (is_string($group)) {
                return [$group];
            }

            return $group;
        });
    }

    /**
     * Checks if the position update/movement is disabled
     * @return bool
     */
    public function isPositionUpdateDisabled()
    {
        return $this->positionOption('disablePositionUpdate');
    }

    //endregion
}
