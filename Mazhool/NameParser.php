<?php namespace AwatBayazidi\Foundation\Mazhool;

class NameParser
{

    protected $name;
    protected $data = [];

    protected $actions = [
        'create' => [
            'create',
            'make',
        ],
        'delete' => [
            'delete',
            'remove',
        ],
        'add' => [
            'add',
            'update',
            'append',
            'insert',
        ],
        'drop' => [
            'destroy',
            'drop',
        ],
    ];

    public function __construct($name)
    {
        $this->name = $name;
        $this->data = $this->fetchData();
    }

    public function getOriginalName()
    {
        return $this->name;
    }

    public function getAction()
    {
        return head($this->data);
    }

    public function getTable()
    {
        return $this->getTableName();
    }

    public function getTableName()
    {
        $matches = array_reverse($this->getMatches());
        return array_shift($matches);
    }


    public function getMatches()
    {
        preg_match($this->getPattern(), $this->name, $matches);
        return $matches;
    }

    public function getPattern()
    {
        switch ($action = $this->getAction()) {
            case 'add':
            case 'append':
            case 'update':
            case 'insert':
                return "/{$action}_(.*)_to_(.*)_table/";
                break;

            case 'delete':
            case 'remove':
            case 'alter':
                return "/{$action}_(.*)_from_(.*)_table/";
                break;

            default:
                return "/{$action}_(.*)_table/";
                break;
        }
    }

    protected function fetchData()
    {
        return explode('_', $this->name);
    }

    public function getData()
    {
        return $this->data;
    }


    public function is($type)
    {
        return $type == $this->getAction();
    }

    public function isAdd()
    {
        return in_array($this->getAction(), $this->actions['add']);
    }

    public function isDelete()
    {
        return in_array($this->getAction(), $this->actions['delete']);
    }

    public function isCreate()
    {
        return in_array($this->getAction(), $this->actions['create']);
    }


    public function isDrop()
    {
        return in_array($this->getAction(), $this->actions['drop']);
    }
}
