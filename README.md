# Laravel eloquent model position

[![Total Downloads](https://poser.pugx.org/pion/laravel-eloquent-position/downloads?format=flat)](https://packagist.org/packages/pion/laravel-eloquent-position)
[![Latest Stable Version](https://poser.pugx.org/pion/laravel-eloquent-position/v/stable?format=flat)](https://packagist.org/packages/pion/laravel-eloquent-position)
[![Latest Unstable Version](https://poser.pugx.org/pion/laravel-eloquent-position/v/unstable?format=flat)](https://packagist.org/packages/pion/laravel-eloquent-position)


Position logic for Eloquent models with minimum setup. Before saving it will check if the position has changed
and updates the other entries based on the models position value.


* [Installation](#installation)
* [Usage](#usage)
    * [Migration example](#migration-example)
    * [Events](#events)
        * [Positioning](#positioning)
        * [Positioned](#positioned)
    * [Command](#command)
    * [Traits](#traits)
* [Changelog](#changelog)
* [Todo](#todo)
* [Contribution](#contribution)

## Installation

> Tested in Laravel 5.1 - 5.6, should work in all 5.* releases

**Install via composer**

```
composer require pion/laravel-eloquent-position
```

## Usage

1. Add a `position` (can be custom) column in your table (model)
2. Add `PositionTrait` into your model (if you are using custom column set the `$positionColumn` property)
3. If you are using grouped entries (like parent_id and etc), you can set the `$positionGroup` with the column name/names (supports single string or multiple columns)
4. Add to form the position input (can be input[type=number] and etc) and fill/set the position on save
5. When position is null or empty string, the last position will be used.
6. If you are not using migration (the column exists), run the php artisian model:position` command to fix current entries (it will create correct order)

**Then you can get your entries sorted:**

```php
// ASC
YourModel::sorted()->get()

// DESC
YourModel::sortedByDESC()->get()
```

If using default column name (position), the value will be converted to numeric value (if not null or empty string).

**Get the position**
Use the `$model->getPosition()` or use the standard way by using the column name `$model->position`

### Migration example

```php
public function up()
    {
    Schema::table('pages', function (Blueprint $table) {
        $table->smallInteger('position')->default(0)->after('id');
    });

    // Update the order pages
    Artisan::call('model:position', [
        'model'=> \App\Models\Page\Page::class
    ]);
}
```

### Model example

```php
class Page extends Model
{
    use PositionTrait;

    public $table = 'pages';
    public $positionGroup = ['parent_slug'];

    protected $fillable = [
        'title', 'slug', 'parent_slug', 'content', 'description', 'position'
    ];
    
}
```

### Events
You can listen to events for positioning changes. You can use the `PositionEventsTrait` for easy model registration.

```php
....

class YourModel extends Model {
    use PositionTrait, PositionEventsTrait;
    ....
}
```

#### Positioning
Called before running the last position calculation and the final movement of other entries for given position.

**Enables to:**
* Restore the position to original value - return false
* Add additional query conditions via AbstractPositionQuery object in second parameter ($query->query() => Builder)

Name: `positioning`

```php
YourModel::positioning(function($model, $query) {
    $query->query()->where('type', 'type'); // or etc
    \Log::info('positioning', 'To '.$model->getPosition().' from '.$query->oldPosition());
});
```

#### Positioned

Name: `positioned`

Example via trait:

```php
YourModel::positioned(function($model) {
    /// TODO
});
```

### Command

#### Reposition command
This command will help you to fix the order of your models. You must provide a model class. 
You must include the `RecalculatePositionCommand` into your Console `Kernel` class.

```bash
php artisan model:position App\\Models\\YourModel
```

### Traits

#### PositionTrait
Uses the `BasePositionTrait` and `PositionScopeTrait`

You can set:
* *string* `positionColumn` *to enable overriding for the position column*
* *boolean* `disablePositionUpdate` *disables the updated of other entries*
* *string|array* `positionGroup` *builds a filter from columns for position calculation. Supports single column or multiple columns*
* *string* `defaultPositionValue` *allows returning different value when position is empty string or null. Default value is null*
 
#### PositionScopeTrait


## Todo

- [ ] Add the custom position trait to enable automatic convert to numeric value (don't want to use the setAttribute method) - In progress
- [ ] Add service provider for automatic command registration
- [ ] Add all docs for all features
- [ ] Add next/prev scope functions in `PositionScopeTrait`
- [ ] Add `PositionHelperTrait` with (getLastUsedPosition, getNextPosition($position = null)) 

## Contribution
See [CONTRIBUTING.md](CONTRIBUTING.md) for how to contribute changes. All contributions are welcome.

## Copyright and License

[laravel-eloquent-position](https://github.com/pionl/laravel-eloquent-position)
was written by [Martin Kluska](http://kluska.cz) and is released under the 
[MIT License](LICENSE.md).

Copyright (c) 2016 Martin Kluska
