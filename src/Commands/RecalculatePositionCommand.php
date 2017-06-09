<?php
namespace Pion\Support\Eloquent\Position\Commands;

use Pion\Support\Eloquent\Position\Traits\PositionTrait;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class RecalculatePositionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:position {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates given model position. You must provide full model class';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get the model
        /** @var PositionTrait $modelClass */
        $modelClass = $this->argument('model');

        //
        /**
         * Build the model instance so we can get the settings
         * @var PositionTrait $model
         */
        $model = new $modelClass();

        // Check if using the PositionTrait
        if (!method_exists($model, 'newPositionQuery')) {
            $this->error("Model {$modelClass} is not using PositionTrait");
            return false;
        }

        // Holds the positions for every group (defined by the values
        $positionsByGroup = [];

        // Get the group
        $groups = $model->getPositionGroup();

        // Run sorted query for every entry
        $modelClass::sorted()->chunk(200, function (Collection $collection) use ($groups, &$positionsByGroup) {
            /** @var PositionTrait|Model $model */
            foreach ($collection as $model) {
                // Prevent the move action and force the position we set
                $model->disablePositionUpdate = true;

                // Builds the group key to get position
                $groupKey = $this->buildGroupKeyForPosition($model, $groups);

                // Set the new position
                $model->setPosition($this->getPositionForGroup($groupKey, $positionsByGroup))
                    ->save();
            }
        });

        // Render the table layout about the positions
        $this->table([
            'Group', 'Last position'
        ], collect($positionsByGroup)->map(function ($value, $key) {
            return [$key, $value];
        }));

        return true;
    }

    /**
     * Stores/updates the next position
     *
     * @param string $groupKey
     * @param array  $positionsByGroup referenced array of currently stores positions
     *
     * @return mixed
     */
    protected function getPositionForGroup($groupKey, &$positionsByGroup)
    {
        if (!isset($positionsByGroup[$groupKey])) {
            $positionsByGroup[$groupKey] = 0;
        }
        // Increment the position
        $positionsByGroup[$groupKey]++;

        // Return the new position
        return $positionsByGroup[$groupKey];
    }

    /**
     * Builds the group key from the group columns and the values form the model
     *
     * @param Model|PositionTrait $model
     * @param array               $groups
     *
     * @return string
     */
    protected function buildGroupKeyForPosition($model, $groups)
    {
        if (is_null($groups)) {
            return 'No group';
        }

        // Prepare array of values to create single string
        $groupValues = [];

        // Get the group column values
        foreach ($groups as $column) {
            $groupValues[] = $model->{$column};
        }

        // Build the key
        return implode('_', $groupValues);
    }
}
