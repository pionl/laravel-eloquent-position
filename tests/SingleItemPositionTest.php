<?php
namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Models\AbstractPositionModel;
use Tests\Models\SingleItem;
use Orchestra\Testbench\TestCase;

class SingleItemPositionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    protected function createItem(string $name, string $position = null): AbstractPositionModel
    {
        return SingleItem::create([
            'name' => $name,
            'position' => $position,
        ]);
    }

    public function testCreateItemsWithCorrectOrderWithoutSettingPosition()
    {
        for ($i = 1; $i <= 10; $i++) {
            $item = $this->createItem('Test ' . $i);
            $this->assertEquals($item->position, $i);
        }
    }

    public function testCreateItemAtLastPositionWithEmptyStringAndUsesAsLastPosition()
    {
        $this->assertEquals(1, $this->createItem('Test', '')->position);
        $this->assertEquals(2, $this->createItem('Test', 0)->position);
    }

    public function testCreateItemWithFixedPositionButEmptyListEnsuresCorrectNumber()
    {
        $this->assertEquals(1, $this->createItem('Test 1', 1)->position);
        $this->assertEquals(2, $this->createItem('Test 2', 2)->position);
        $this->assertEquals(2, $this->createItem('Test 3', 2)->position);

        $this->assertListOrder([
            'Test 1 - 1',
            'Test 3 - 2',
            'Test 2 - 3',
        ]);

        $this->assertEquals( 1, $this->createItem('Test 4', 1)->position);

        $this->assertListOrder([
            'Test 4 - 1',
            'Test 1 - 2',
            'Test 3 - 3',
            'Test 2 - 4',
        ]);
    }

    public function testUpdatePosition()
    {
        $item = $this->createItem('Test 1');
        $this->assertEquals(1, $item->position);
        $item2 = $this->createItem('Test 2');
        $this->assertEquals(2, $item2->position);
        $item3 = $this->createItem('Test 3');
        $this->assertEquals(3, $item3->position);

        $this->assertListOrder([
            'Test 1 - 1',
            'Test 2 - 2',
            'Test 3 - 3',
        ]);

        $item->update(['position' => 2]);

        $this->assertListOrder([
            'Test 2 - 1',
            'Test 1 - 2',
            'Test 3 - 3',
        ]);

        $item->update(['position' => 1]);

        $this->assertListOrder([
            'Test 1 - 1',
            'Test 2 - 2',
            'Test 3 - 3',
        ]);

        $item->update(['position' => 4]);

        $this->assertListOrder([
            'Test 2 - 1',
            'Test 3 - 2',
            'Test 1 - 3',
        ]);

    }

    public function testCreateItemWithForcedPositionAndDelete()
    {
        for ($i = 1; $i <= 8; $i++) {
            $item = $this->createItem('Test ' . $i);
            $this->assertEquals($i, $item->position);
        }

        $item = $this->createItem('Test middle', 5);
        $this->assertEquals(5, $item->position);

        $this->assertListOrder([
            'Test 1 - 1',
            'Test 2 - 2',
            'Test 3 - 3',
            'Test 4 - 4',
            'Test middle - 5',
            'Test 5 - 6',
            'Test 6 - 7',
            'Test 7 - 8',
            'Test 8 - 9',
        ]);
        $item->delete();

        $this->assertListOrder([
            'Test 1 - 1',
            'Test 2 - 2',
            'Test 3 - 3',
            'Test 4 - 4',
            'Test 5 - 5',
            'Test 6 - 6',
            'Test 7 - 7',
            'Test 8 - 8',
        ]);
    }

    protected function query(): Builder
    {
        return SingleItem::query();
    }

    protected function assertListOrder(array $expectedItems): void
    {
        $items = $this->query()
            ->sorted()
            ->get()
            ->map(fn(AbstractPositionModel $item) => $item->name . ' - ' . $item->position)
            ->all();

        $this->assertEquals($expectedItems, $items);
    }
}
