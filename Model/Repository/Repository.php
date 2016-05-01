<?php namespace AwatBayazidi\Foundation\Model\Repository;

use AwatBayazidi\Abzar\Facades\Payam;
use AwatBayazidi\Abzar\Traits\Repositories\DateTimeTrait;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException as NotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
class ModelNotFoundException extends NotFoundException
{
    //
}

class Repository extends AbstractRepository
{
    use DateTimeTrait;


    /**
     * Repository constructor.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        parent::__construct($app);
    }

    /**
     * @param Model $model
     */
    public function modelNotFound(Model $model)
    {
        throw (new ModelNotFoundException())->setModel(get_class($model));
    }

    /**
     * @param $column
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function requireBy($column, $value, $columns = ['*'])
    {
        if (!$record = $this->findFirstBy($column, $value, $columns)) {
            $this->modelNotFound($this->getModel());
        }
        return $record;
    }

    /**
     * @param $id
     * @param array $columns
     *
     * @return mixed
     */
    public function requireById($id, $columns = ['*'])
    {
        return $this->requireBy('id', $id, $columns);
    }


    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function get($columns = ['*'])
    {
        $model = $this->getQuery()->get($columns);
        $this->makeModel();
        return $model;
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return $this->get(['*']);
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $model = $this->getQuery()->first($columns);
        $this->makeModel();
        return $model;
    }

    /**
     * @param $value
     * @param null $key
     *
     * @return mixed
     */
    public function lists($value, $key = null)
    {
        $model = $this->getQuery()->lists($value, $key);
        $this->makeModel();
        return $model;
    }

    /**
     * @param array $searchQuery
     *
     * @return mixed
     */
    public function allOrSearch($searchQuery = null, $columns = ['*'])
    {
        if (is_null($searchQuery)) {
            return $this->all();
        }
        return $this->search($searchQuery, $columns);
    }


    /**
     * @param array $input
     *
     * @return mixed
     */
    public function search($input, $columns = ['*'])
    {
        $query = $this->getModel()->query();
        $_columns = Schema::getColumnListing($this->getTable());
        $attributes = array();
        foreach ($_columns as $attribute) {
            if (isset($input[$attribute]) and !empty($input[$attribute])) {
                $query->where($attribute, $input[$attribute]);
                $attributes[$attribute] = $input[$attribute];
            } else {
                $attributes[$attribute] = null;
            }
        };
        //  dd([$query->get(), $attributes]);
        return $query->get($columns);

    }

    public function searchBy($input)
    {
        $query = $this->getQuery();
        $_columns = Schema::getColumnListing($this->getTable());
        $attributes = array();
        foreach ($_columns as $attribute) {
            if (isset($input[$attribute]) and !empty($input[$attribute])) {
                $query->where($attribute, $input[$attribute]);
                $attributes[$attribute] = $input[$attribute];
            } else {
                $attributes[$attribute] = null;
            }
        };
        $this->setQuery($query);
        return $this;
    }

    /**
     * @param int $perPage
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($perPage = null, $columns = ['*'])
    {
        if (is_null($perPage)) $this->getPerPage();
        $model = $this->getQuery()->paginate($perPage, $columns);
        $this->makeModel();
        return $model;
    }

    /**
     * @param int $perPage
     * @param array $columns
     *
     * @return mixed
     */
    public function simplePaginate($perPage = null, $columns = ['*'])
    {
        if (is_null($perPage)) $this->getPerPage();
        $model = $this->getQuery()->simplePaginate($perPage, $columns);
        $this->makeModel();
        return $model;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function listBy($key, $value)
    {
        return $this->get([$key, explode('.', $value)[0],])->keyBy($key)->map(function ($item, $key) use ($value) {
            return array_get($item->toArray(), $value);
        });
    }

    /* ------------------------------------------------------------------------------------------------
     |  orderBy
     | ------------------------------------------------------------------------------------------------
     */

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function getFirst($columns = ['*'])
    {
        $this->orderBy('created_at', 'asc');
        return $this->first($columns);
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function getLast($columns = ['*'])
    {
        $this->last();
        return $this->first($columns);
    }


    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function getLastUpdate($columns = ['*'])
    {
        $this->lastUpdated();
        return $this->first($columns);
    }


    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function getLastDeleted($columns = ['*'])
    {
        $this->lastDeleted();
        return $this->first($columns);
    }



    /**
     * @param $column
     * @$sort $value desc or asc
     *
     * @return mixed
     */
    public function orderBy($column = 'created_at', $sort ='desc')
    {
        $this->setQuery($this->getQuery()->orderBy($column, $sort));
        return $this;
    }

    /**
     * @param $column
     * @return mixed
     */
    public function last($column = 'created_at')
    {
        $this->orderBy($column, 'desc');
        return $this;
    }

    /**
     * @return mixed
     */
    public function lastDeleted()
    {
        $this->last('deleted_at');
        return $this;
    }

    /**
     * @return mixed
     */
    public function lastUpdated()
    {
        $this->last('updated_at');
        return $this;
    }

    /**
     * @return mixed
     */
    public function findLastCreated()
    {
        $this->last();
        return $this;
    }

    /**
     * @return mixed
     */
    public function findLastUpdated($columns = ['*'])
    {
        $this->lastUpdated();
        return $this->get($columns);
    }

    /**
     * @return mixed
     */
    public function findLastDeleted()
    {
        $this->withTrashed()->lastDeleted();
        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Condition
     | ------------------------------------------------------------------------------------------------
     */

    /**
     * @param $column
     * @param $value
     *
     * @return mixed
     */
    public function where($column, $value, $operator = '=')
    {
        $this->setQuery($this->getQuery()->where($column, $operator, $value));
        return $this;
    }

    /**
     * @param $column
     * @param $value
     *
     * @return mixed
     */
    public function orWhere($column, $value, $operator = '=')
    {
        $this->setQuery($this->getQuery()->orWhere($column, $operator, $value));
        return $this;
    }


    /**
     * @param $column
     * @param $value
     *
     * @return mixed
     */
    public function whereNull($column)
    {
        $this->setQuery($this->getQuery()->whereNull($column));
        return $this;
    }

    public function whereNotNull($column)
    {
        $this->setQuery($this->getQuery()->whereNotNull($column));
        return $this;
    }


    /**
     * @param $column
     * @param array  $values
     * @param string $boolean
     * @param bool   $not
     *
     * @return mixed
     */
    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $this->setQuery($this->getQuery()->whereBetween($column, $values, $boolean, $not));
        return $this;
    }


    /**
     * @param $column
     * @param array  $values
     *
     * @return mixed
     */
    public function whereIn($column, array $values)
    {
        $this->setQuery($this->getQuery()->whereIn($column, $values));
        return $this;
    }

    /**
     * @param $column
     * @param array  $values
     *
     * @return mixed
     */
    public function whereNotIn($column, array $values)
    {
        $this->setQuery($this->getQuery()->whereNotIn($column, $values));
        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Trashed
     | ------------------------------------------------------------------------------------------------
     */


    /**
     *  include the soft deleted models in the results
     */
    public function withTrashed()
    {
        $this->setQuery($this->getQuery()->withTrashed());
        return $this;
    }


    /**
     *   Force the result set to only included soft deletes
     */
    public function onlyTrashed()
    {
        $this->setQuery($this->getQuery()->onlyTrashed());
        return $this;
    }


    public function onlyTrashedBy($column, $value)
    {
        $this->onlyTrashed()->where($column, '=', $value);
        return $this;
    }

    public function trashedBy($column, $value)
    {
        $this->withTrashed()->where($column, '=', $value);
        return $this;
    }


    /* ------------------------------------------------------------------------------------------------
     |  find
     | ------------------------------------------------------------------------------------------------
     */

    /**
     * @param $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $model = $this->getQuery()->find($id, $columns);
        $this->makeModel();
        return $model;
    }


    public function findById($id, $columns = ['*'])
    {
        return $this->find($id, $columns);
    }

    /**
     * @param $column
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($column, $value, $columns = ['*'])
    {
        $this->where($column,$value);
        return $this->first($columns);
    }

    /**
     * @param $column
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findManyBy($column, $value, $columns = ['*'])
    {
        $this->where($column,$value);
        return $this->get($columns);
    }

    /**
     * @param $where
     * @param array $columns
     * @param bool  $or
     *
     * @return mixed
     */
    public function findWhere($where, $columns = ['*'], $or = false)
    {
        $model = $this->getQuery();
        foreach ($where as $field => $value) {
            if ($value instanceof \Closure) {
                $model = (!$or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;

                    $model = (!$or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;

                    $model = (!$or)
                        ? $model->where($field, '=', $search)
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (!$or)
                    ? $model->where($field, '=', $value)
                    : $model->orWhere($field, '=', $value);
            }
        }

        $collection = $model->get($columns);

        $this->makeModel();

        return $collection;
    }

    /**
     * @param $column
     * @param array  $values
     * @param string $boolean
     * @param bool   $not
     *
     * @return mixed
     */
    public function findWhereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $this->whereBetween($column, $values, $boolean, $not);
        return $this->get();
    }

    /**
     * @param $column
     * @param array  $values
     *
     * @return mixed
     */
    public function findWhereIn($column, array $values, $columns = ['*'])
    {
        $this->whereIn($column, $values);
        return $this->get($columns);
    }

    /**
     * @param $column
     * @param array  $values
     * @param bool   $not
     *
     * @return mixed
     */
    public function findWhereNotIn($column, array $values, $columns = ['*'])
    {
        $this->whereNotIn($column, $values);
        return $this->get($columns);
    }

    /**
     * @param $column
     * @param array  $values
     * @param bool   $not
     *
     * @return mixed
     */
    public function findWhereNull($column, $columns = ['*'])
    {
        $this->whereNull($column);
        return $this->get($columns);
    }

    /**
     * @param $column
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findFirstBy($column, $value, $columns = ['*'])
    {
        $this->where($column, $value);
        return $this->getFirst($columns);
    }

    /**
     * @param $column
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findLastBy($column, $value, $columns = ['*'])
    {
        $this->where($column, $value);
        return $this->getLast($columns);
    }

    /**
     * @param $column
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findManyLastBy($column, $value, $columns = ['*'])
    {
        $this->where($column, $value)->last();
        return $this->get($columns);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function findNext(Model $model)
    {
        $this->where('created_at', $model->created_at,'>=')->where('id', $model->id,'<>')->orderBy('created_at', 'asc');
        return $this->first();
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function findPrevious(Model $model)
    {
        $this->where('created_at', $model->created_at ,'<=')->where('id', $model->id ,'<>')->last();
        return $this->first();
    }


    public function findOnlyTrashed($columns = ['*'])
    {
        $this->onlyTrashed();
        return $this->get($columns);
    }

    public function findOnlyTrashedBy($column, $value, $columns = ['*'])
    {
        $this->onlyTrashed()->where($column, $value);
        return $this->get($columns);
    }

    public function findTrashedBy($column, $value, $columns = ['*'])
    {
        $this->withTrashed()->where($column, $value);
        return $this->get($columns);
    }

    /* ------------------------------------------------------------------------------------------------
     |   Aggregate
     | ------------------------------------------------------------------------------------------------
     */

    /**
     * @return mixed
     */
    public function count()
    {
        $model = $this->getQuery()->count();
        $this->makeModel();
        return $model;
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function max($column)
    {
        $model = $this->getQuery()->max($column);
        $this->makeModel();
        return $model;
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function min($column)
    {
        $model = $this->getQuery()->min($column);
        $this->makeModel();
        return $model;
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function avg($column)
    {
        $model = $this->getQuery()->avg($column);
        $this->makeModel();
        return $model;
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function sum($column)
    {
        $model = $this->getQuery()->sum($column);
        $this->makeModel();
        return $model;
    }


    /* ------------------------------------------------------------------------------------------------
     |   Relation
     | ------------------------------------------------------------------------------------------------
     */

    /**
     * @param $modelOrId
     * @param $relation
     * @param Model $relationModel
     * @param array $joining
     * @param bool  $touch
     *
     * @return mixed
     *  User::find(1)->roles()->save($role, ['expires' => $expires]);
     */
    public function saveRelation($modelOrId, $relation, Model $relationModel, array $joining = [], $touch = true)
    {
        if (!$modelOrId instanceof Model) {
            $modelOrId = $this->requireById($modelOrId);
        }

        return $modelOrId->{$relation}->save($relationModel, $joining, $touch);
    }

    public function createRelation($id, $relation, array $joining = [])
    {
        $model = $this->requireById($id);
        return $model->{$relation}->create($joining);
    }

    /**
     * @param $modelOrId
     * @param $relation
     * @param array $models
     * @param array $joinings
     *
     * @return mixed
     */
    public function saveRelations($modelOrId, $relation, array $models, array $joinings = [])
    {
        if (!$modelOrId instanceof Model) {
            $modelOrId = $this->requireById($modelOrId);
        }

        return $modelOrId->{$relation}->saveMany($models, $joinings);
    }

    /**
     * @param $modelOrId
     * @param $relation
     * @param Model $relationModel
     *
     * @return mixed
     */
    public function associateRelation($modelOrId, $relation, Model $relationModel)
    {
        if (!$modelOrId instanceof Model) {
            $modelOrId = $this->requireById($modelOrId);
        }

        return $modelOrId->{$relation}->associate($relationModel);
    }

    /**
     * @param $modelOrId
     * @param $relation
     * @param $relationId
     * @param array $attributes
     * @param bool  $touch
     *
     * @return mixed
     */
    public function attachRelation($modelOrId, $relation, $relationId, array $attributes = [], $touch = true)
    {
        if (!$modelOrId instanceof Model) {
            $modelOrId = $this->requireById($modelOrId);
        }

        return $modelOrId->{$relation}->attach($relationId, $attributes, $touch);
    }

    /**
     * @param $modelOrId
     * @param $relation
     * @param array $ids
     * @param bool  $touch
     *
     * @return mixed
     */
    public function detachRelation($modelOrId, $relation, $ids = [], $touch = true)
    {
        if (!$modelOrId instanceof Model) {
            $modelOrId = $this->requireById($modelOrId);
        }

        return $modelOrId->{$relation}->detach($ids, $touch);
    }

    /**
     * @param $modelOrId
     * @param $relation
     * @param $ids
     * @param bool $detaching
     *
     * @return mixed
     */
    public function syncRelation($modelOrId, $relation, $ids, $detaching = true)
    {
        if (!$modelOrId instanceof Model) {
            $modelOrId = $this->requireById($modelOrId);
        }

        return $modelOrId->{$relation}->sync($ids, $detaching);
    }

    /**
     * @param $modelOrId
     * @param $relation
     * @param $relationId
     * @param array $attributes
     * @param bool  $touch
     *
     * @return mixed
     */
    public function updateExistingPivot($modelOrId, $relation, $relationId, array $attributes, $touch = true)
    {
        if (!$modelOrId instanceof Model) {
            $modelOrId = $this->requireById($modelOrId);
        }
        return $this->getQuery()->{$relation}->updateExistingPivot($relationId, $attributes, $touch);
    }

    /**
     * @param Model $model
     * @param $relation
     *
     * @return mixed
     */
    public function getRelation(Model $model, $relation)
    {
        return $model->{$relation};
    }

    /**
     * @param Model $model
     * @param $relation
     * @param int    $perPage
     * @param string $orderBy
     * @param string $orderByDirection
     *
     * @return mixed
     */
    public function getRelationPaginated(Model $model, $relation, $perPage = 10, $orderBy = 'created_at', $orderByDirection = 'desc')
    {
        return $model->{$relation}()->orderBy($orderBy, $orderByDirection)->paginate($perPage);
    }




    /* ------------------------------------------------------------------------------------------------
     |   Crudl
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function create($data)
    {
        return $this->getModel()->create($data);
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function saveModel(array $data)
    {
        foreach ($data as $key => $value) {
            $this->getModel()->$key = $value;
        }
        $model = $this->getModel()->save();
        $this->makeModel();

        return $model;
    }

    /**
     * @param $id
     * @param array  $data
     * @param string $column
     *
     * @return Model
     *
     * @throws \Exception
     */
    public function update($id, $data, $column = 'id')
    {
        $model = $this->requireBy($column, $id);
        return $this->updateModel($model, $data);
    }

    /**
     * @param $id
     * @param array  $data
     * @param string $column
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function updateFill($id,$data, $column = 'id')
    {
        $model = $this->requireBy($column, $id);
        dd($model);
        if (!$model = $model->fill($data)->save()) {
            throw new \Exception('Could not be saved');
        }

        $this->makeModel();

        return $model;
    }


    /**
     * @param Model $model
     * @param array $data
     *
     * @return Model
     *
     * @throws \Exception
     */
    public function updateModel(Model $model,$data)
    {
        if (!$model->update($data)) {
            throw new \Exception('Could not be saved');
        }
        $this->makeModel();
        return $model;
    }

    /**
     * @param $ids
     *
     * @return mixed
     */
    public function destroy($ids)
    {
        return $this->getModel()->destroy($ids);
    }

    public function delete($id)
    {
        $model = $this->findById($id);
        if (!is_null($model)) {
            $model->delete();
            return true;
        }else{
            return $this->forceDelete($id);
        }
    }

    /**
     * @return mixed
     */
    public function truncate()
    {
        return $this->getModel()->delete();
    }


    public function forceDelete($id)
    {
        $model = $this->withTrashed()->findById($id);
        if (!is_null($model)) {
            $model->forceDelete();
            return true;
        }
        return false;
    }


    public function restore($id)
    {
        $model = $this->getModel()->withTrashed()->where('id', $id)->restore();
        if (!is_null($model)) {
            return true;
        }
        return false;
    }


    /**
     * @param array $attributes
     *
     * @return mixed
     */
    public function firstOrCreate(array $attributes)
    {
        return $this->getModel()->firstOrCreate($attributes);
    }

    /**
     * @param array $attributes
     *
     * @return mixed
     */
    public function firstOrNew(array $attributes)
    {
        return $this->getModel()->firstOrNew($attributes);
    }

    /**
     * @param $relation
     * @param array $columns
     *
     * @return mixed
     */
    public function has($relation, $columns = ['*'])
    {
        $collection = $this->getModel()->has($relation)->get($columns);

        $this->makeModel();
        return $collection;
    }

    /**
     * @param Model $model
     * @param $relationship
     * @param array $attributes
     *
     * @return mixed
     *
     * User::find(1)->roles()->save($role, ['expires' => $expires]);
     */

    public function saveHasOneOrMany($model, $relationship, array $attributes)
    {
        if (!$model instanceof Model) {
            $model = $this->findById($model);
        }
        $relationshipModel = get_class($model->{$relationship}()->getModel());
        $relationshipModel = new $relationshipModel($attributes);

        return $model->{$relationship}()->save($relationshipModel);
    }
}
