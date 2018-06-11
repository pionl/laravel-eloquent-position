<?php

namespace Pion\Support\Eloquent\Position\Traits;

/**
 * Trait BasePositionTrait
 *
 * Returns and sets the position
 *
 * @package Pion\Support\Eloquent\Position\Traits
 */
trait BasePositionTrait
{
    /**
     * Stores the property values - prevents multiple calls of property_exists.
     *
     * @var array
     */
    protected $optionCache = [];

    /**
     * Returns the position
     * @return int|null
     */
    public function getPosition()
    {
        return $this->{$this->getPositionColumn()};
    }

    /**
     * Sets the position column value
     *
     * @param $value
     *
     * @return $this
     */
    public function setPosition($value)
    {
        $this->{$this->getPositionColumn()} = $value;
        return $this;
    }

    /**
     * Converts the position value to numeric (if not null)
     *
     * @param mixed $value
     */
    protected function setPositionAttribute($value)
    {
        // Convert to numeric value if needed
        $finalValue = is_null($value) || $value === '' ?
            $this->positionOption('defaultPositionValue', null) : intval($value);

        $this->attributes['position'] = $finalValue;
    }

    //region Helpers

    /**
     * Checks if given key is position column
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isPositionColumn($key)
    {
        $positionColumn = $this->getPositionColumn();
        return $key == $positionColumn;
    }

    /**
     * Returns the position column. Checks if positionColumn property is set.
     * Uses the `positionColumnCache` property to cache the value
     * @return null|string
     */
    public function getPositionColumn()
    {
        return $this->positionOption('positionColumn', 'position');
    }

    /**
     * Returns the position property option
     *
     * @param string        $propertyName
     * @param mixed|null    $defaultValue
     * @param \Closure|null $onInitValueCallback Provides a way, how to change the retrieved value
     *                                           function($value) {return $value}
     *
     * @return mixed
     */
    protected function positionOption($propertyName, $defaultValue = null, $onInitValueCallback = null)
    {
        // Check if value is already in cache
        if (!isset($this->optionCache[$propertyName])) {
            // Check if property is provided
            if (property_exists($this, $propertyName)) {
                $value = $this->{$propertyName};
            } else {
                $value = $defaultValue;
            }

            // Change the value via callback
            if (is_callable($onInitValueCallback)) {
                $value = $onInitValueCallback($value);
            }

            // Store the value
            $this->optionCache[$propertyName] = $value;
        }

        return $this->optionCache[$propertyName];
    }

    /**
     * Resets the position option cache to get new data
     * @return $this
     */
    protected function resetPositionOptionCache()
    {
        $this->optionCache = [];
        return $this;
    }

    //endregion

    //region Override of attribute set for options
    /**
     * Enables setting disablePositionUpdate option in runtime
     *
     * @param boolean $value
     */
    public function setDisablePositionUpdateAttribute($value)
    {
        $this->optionCache['disablePositionUpdate'] = $value;
    }

    /**
     * Enables setting positionColumn option in runtime
     *
     * @param string $value
     */
    public function setPositionColumnAttribute($value)
    {
        $this->optionCache['positionColumn'] = $value;
    }
    //endregion
}
