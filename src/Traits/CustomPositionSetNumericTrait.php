<?php
namespace Pion\Support\Eloquent\Position\Traits;

/**
 * Traits PositionSetNumericTrait
 *
 * Loads the position trait. In progress!!!!
 *
 * @package App\Models\Traits
 *
 * @deprecated
 */
trait CustomPositionSetNumericTrait
{
    use PositionTrait;

    /**
     * Prepare the
     * @var string
     */
    protected $positionColumn = null;

    //region Column

    /**
     * Returns the position column from the implemented `buildPositionColumn` method.
     *
     * @return string
     */
    public function getPositionColumn()
    {
        if (is_null($this->positionColumn)) {
            $this->positionColumn = $this->buildPositionColumn();
        }

        return $this->positionColumn;
    }

    /**
     * Builds the position column. Called once.
     * @return string
     */
    abstract protected function buildPositionColumn();

    //endregion

    //region Change to numeric


    /**
     * Converts the position into numeric value
     *
     * @param  string  $key
     * @return mixed

    protected function getAttributeFromArray($key)
    {
    // Call the parent method
    $value = parent::getAttributeFromArray($key);
    $positionColumn = $this->getPositionColumn();
    if ($key != $positionColumn || is_null($value)) {
    return $value;
    }

    return intval($value);
    } */

    /**
     * We don't want to override the setAttribute (other trait can use it), instead detect the
     * set{Attribute}Attribute call (we force true in hasSetMutator for position column). Converts
     * the value to numeric value (looks better).
     *
     * @param $name
     * @param $args
     */
    public function __call($name, $args)
    {
        // Build the position column name
        $positionSetMethod = 'set'.Str::studly($this->getPositionColumn()).'Attribute';
        if ($positionSetMethod === $name) {
            call_user_func([$this, 'setPosition'], $args);
        }
    }

    /**
     * Determine if a set mutator exists for an attribute. For position column returns always true.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        if ($this->isPositionColumn($key)) {
            return true;
        }
        return parent::hasSetMutator($key);
    }

    //endregion
}
