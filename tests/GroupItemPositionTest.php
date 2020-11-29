<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Tests\Models\AbstractPositionModel;
use Tests\Models\GroupItem;

class GroupItemPositionTest extends SingleItemPositionTest
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create items for different group - single item position test should contain only its own group
        $this->createItem('DifferentGroup', null, 2);
        $this->createItem('DifferentGroup', null, 2);
        $this->createItem('DifferentGroup', null, 2);
        $this->createItem('DifferentGroup', null, 2);
    }


    protected function query(): Builder
    {
        return GroupItem::query()->where('group', 1);
    }

    protected function createItem(string $name, string $position = null, int $group = 1): AbstractPositionModel
    {
        return GroupItem::create([
            'name' => $name,
            'position' => $position,
            'group' => $group,
        ]);
    }
}
