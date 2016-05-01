<?php namespace AwatBayazidi\Foundation\Model\Repository;

use AwatBayazidi\Contracts\Model\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;


abstract class AbstractRepository implements Repository
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var
     */
    protected $className;

    /**
     * @var
     */
    protected $model;

    /**
     * @var
     */
    protected $perPage;

    /**
     * @var
     */
    protected $query;


    protected $has_query;

    /**
     * AbstractRepository constructor.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->makeModel();
    }


    public function getModelClass()
    {
        return property_exists($this, 'className') ? $this->className : null;
    }


    public function resetModel()
    {
        $this->makeModel();
    }


    /**
     * @return mixed
     */
    public function makeModel()
    {
        if(is_null($this->getModelClass())){
            throw new \Exception("className property must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }
        $model = $this->app->make($this->getModelClass());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->setModel($model);
    }

    /**
     * @param $model
     *
     * @return mixed
     */
    public function setModel($model)
    {
        $this->setQuery($model->query());
        return $this->model = $model;
    }

    public function setQuery($Query)
    {

        return $this->query = $Query;
    }


    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    public function getQuery()
    {
        return $this->query;
    }



    /**
     * @return string table
     */
    public function getTable()
    {
        return $this->model['table'];
    }

    /**
     * @return string table
     */
    public function getPerPage()
    {

        return $this->perPage?:15;
    }

    /**
     * Set hidden fields.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function hidden(array $fields)
    {
        $this->getModel()->setHidden($fields);

        return $this;
    }

    /**
     * Set visible fields.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function visible(array $fields)
    {
        $this->getModel()->setVisible($fields);

        return $this;
    }
}
