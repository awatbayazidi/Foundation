<?php namespace AwatBayazidi\Foundation\Tree;

use App;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class NestableCollection extends Collection
{
    private $total;
    private $parentColumn;

    public function __construct($items = [])
    {
        parent::__construct($items);
        $this->parentColumn = 'parent_id';
        $this->total = count($items);
    }

    /**
     * Nest items.
     *
     * @return mixed boolean|NestableCollection
     */
    public function nest()
    {
        $parentColumn = $this->parentColumn;
        if (!$parentColumn) {
            return $this;
        }

        // Set id as keys
        $this->items = $this->getDictionary();

        $keysToDelete = [];

        // add empty children collection.
        $this->each(function ($item) {
            if (!$item->items) {
                $item->items = app('Illuminate\Support\Collection');
            }
        });

        // add items to children collection
        foreach ($this->items as $key => $item) {
            if ($item->$parentColumn && isset($this->items[$item->$parentColumn])) {
                $this->items[$item->$parentColumn]->items->push($item);
                $keysToDelete[] = $item->id;
            }
        }

        // Delete moved items
        $this->items = array_values(array_except($this->items, $keysToDelete));

        return $this;
    }

    /**
     * Recursive function that flatten a nested Collection
     * with characters (default is four spaces).
     *
     * @param BaseCollection|null $collection
     * @param string              $column
     * @param int                 $level
     * @param array               &$flattened
     * @param string              $indentChars
     *
     * @return array
     */
    public function listsFlattened($column = 'title', $key = 'id', BaseCollection $collection = null, $level = 0, array &$flattened = [], $indentChars = '&nbsp;&nbsp;&nbsp;&nbsp;')
    {
        $collection = $collection ?: $this;
        foreach ($collection as $item) {
            $flattened[$item->$key] = str_repeat($indentChars, $level).$item->$column;
            if ($item->items) {
                $this->listsFlattened($column,$key, $item->items, $level + 1, $flattened, $indentChars);
            }
        }

        return $flattened;
    }

    /**
     * Get total items in nested collection.
     *
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * Get total items for laravel 4 compatibility.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total();
    }
}
