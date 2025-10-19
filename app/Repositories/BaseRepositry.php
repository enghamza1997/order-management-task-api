<?php

namespace App\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;
use App\Contracts\BaseInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class BaseRepositry implements BaseInterface
{
    /**
     * get Model Class Name
     * @var string
     */
    protected $modelName;

    /**
     * Order Options
     *
     * @var array
     */
    protected $orderOptions = [];
    /**
     * Default order by
     *
     * @var string
     */
    private $orderBy = 'name';
    /**
     * defult where has relation
     *
     * @var string
     */
    protected $whereHasRelation = false;

    /**
     * defult where has relation
     *
     * @var string
     */
    protected $withWhereHasRelation = false;

    /**
     * Default order direction
     *
     * @var string
     */
    private $orderDirection = 'asc';


    /**
     * @param bool $whereHasRelation - only model with relation
     * @return self
     */

    public function viaRelationOnly($whereHasRelation = true)
    {
        $this->whereHasRelation = $whereHasRelation;
        return $this;
    }
    /**
     * @param bool $withWhereHasRelation - only model with relation
     * @return self
     */

    public function viaRelationCallBackOnly($withWhereHasRelation = true)
    {
        $this->withWhereHasRelation = $withWhereHasRelation;
        return $this;
    }
    /**
     * Return all records
     *
     * @param string $orderBy
     * @param array $relations
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function all($orderBy = 'id', array $relations = [], array $parameters = [], $relationCount = 1, $withWhereHasClause = false, $operator = "=", $limit = null)
    {
        $instance = $this->getQueryBuilder();

        $this->parseOrder($orderBy);

        $this->applyFilters($instance, $parameters);

        if ($withWhereHasClause) {
            foreach ($relations as $key => $value) {
                is_string($value) ? $instance->withWhereHas($value, null, $operator, $relationCount) : $instance->withWhereHas($key, $value, $operator, $relationCount);
            }
        } else {
            $instance->with($relations);
        }

        if ($limit)
        {
            $instance->limit($limit);
        }

        return $instance
            ->orderBy($this->getOrderBy(), $this->getOrderDirection())->get();
    }

    /**
     * Return paginated items
     *
     * @param string $orderBy
     * @param array $relations
     * @param int $paginate
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function paginate($orderBy = 'id', array $relations = [],  array $parameters = [], $paginate = 25, $withWhereHasClause = false, $operator = "=", $count = 1)
    {
        $instance = $this->getQueryBuilder();

        $this->parseOrder($orderBy);

        $this->applyFilters($instance, $parameters);

        if ($withWhereHasClause) {
            foreach ($relations as $key => $value) {
                is_string($value) ? $instance->withWhereHas($value, null, $operator, $count) : $instance->withWhereHas($key, $value, $operator, $count);
            }
        } else {
            $instance->with($relations);
        }
        $instance->orderBy($this->getOrderBy(), $this->getOrderDirection());

        return  $instance->paginate($paginate);
    }


    /**
     * select records
     * @param string $orderBy
     * @param array $relations
     * @param string $fieldName
     * @param string $fieldId
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function select($orderBy = 'id', $fieldName = 'name', $fieldId = 'id', $relations = [], array $parameters = [])
    {
        $instance = $this->getQueryBuilder();

        $this->parseOrder($orderBy);

        $this->applyFilters($instance, $parameters);

        if ($this->withWhereHasRelation) {
            $instance = $instance->withWhereHas($relations[0], $relations[1]);
        } else {
            $instance = $instance->with($relations);
        }
        return $instance->orderBy($this->getOrderBy(), $this->getOrderDirection())
            ->select($fieldId, $fieldName)->without('catalog')->get();
    }

    /**
     * Apply parameters, which can be extended in child classes for filtering.
     */
    protected function applyFilters(Builder $instance, array $filters = []): void
    {
        // Should be implemented in specific repositories.
    }

    /**
     * Get many records by a field and value
     *
     * @param int
     * @param array $parameters
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function getBy($parameters, array $relations = [])
    {
        $instance = $this->getQueryBuilder()
            ->with($relations);

        foreach ($parameters as $field => $value) {
            $instance->where($field, $value);
        }

        return $instance->get();
    }

    /**
     * List all records
     *
     * @param string $fieldName
     * @param string $fieldId
     * @return mixed
     * @throws Exception
     */
    public function pluck($fieldName = 'name', $fieldId = 'id')
    {
        return $this->getQueryBuilder()
            ->orderBy($fieldName)
            ->pluck($fieldName, $fieldId)
            ->all();
    }

    /**
     * List all records matching a field's value
     *
     * @param string $field
     * @param mixed $value
     * @param string $listFieldName
     * @param string $listFieldId
     * @return mixed
     * @throws Exception
     */
    public function pluckBy($field, $value, $listFieldName = 'name', $listFieldId = 'id')
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return $this->getQueryBuilder()
            ->whereIn($field, $value)
            ->orderBy($listFieldName)
            ->pluck($listFieldName, $listFieldId)
            ->all();
    }

    /**
     * Find a single record
     *
     * @param string $id
     * @param array $relations
     * @param string $method
     * @return mixed
     * @throws Exception
     */
    public function find($id, array $relations = [], $withWhereHasClause = false, $method = 'findOrFail')
    {
        $model = $this->getQueryBuilder();
        if ($withWhereHasClause) {
            foreach ($relations as $key => $value) {
                is_string($value) ? $model->withWhereHas($value) : $model->withWhereHas($key, $value);
            }
        } else {
            $model->with($relations);
        }
        return $model->$method($id);
    }

    /**
     * Find a single record by a field and value
     *
     * @param string $field
     * @param mixed $value
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function findBy($field, $value, array $relations = [], $method = 'first')
    {
        return $this->getQueryBuilder()
            ->with($relations)
            ->where($field, $value)
            ->$method();
    }

    /**
     * Find a single record by multiple fields

     * @param array $data
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function findByMany(array $data, array $relations = [], $method = 'first')
    {
        $model = $this->getQueryBuilder()->with($relations);

        foreach ($data as $key => $value) {
            $model->where($key, $value);
        }

        return $model->$method();
    }

    /**
     * search in records by multiple fields

     * @param array $data
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function searchByMany(array $data, array $relations = [], $withWhereHasClause = false, $method = 'get')
    {
        $model = $this->getQueryBuilder()->with($relations);

        foreach ($data as $key => $value) {
            $model->orWhere($key, 'like', "%{$value}%");
        }

        if ($withWhereHasClause) {
            foreach ($relations as $key => $value) {
                is_string($value) ? $model->withWhereHas($value) : $model->withWhereHas($key, $value);
            }
        } else {
            $model->with($relations);
        }

        return $model->$method();
    }

    /**
     * Find with filter
     *
     * @param array $id
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function getWhere($field, $value, $method = "get")
    {
        return $this->getQueryBuilder()
            ->where($field, $value)->$method();
    }

    /**
     * Find multiple models
     *
     * @param array $id
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function getWhereIn(array $data, array $relations = [], string $column = 'id')
    {
        return $this->getQueryBuilder()
            ->with($relations)
            ->whereIn($column, $data)->get();
    }

    /**
     * Find multiple models
     *
     * @param array $id
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function getNotWhereIn($column, array $data, array $relations = [], $parameters = [])
    {
        $instance = $this->getQueryBuilder();

        $this->applyFilters($instance, $parameters);

        return $instance
            ->with($relations)
            ->whereNotIn($column, $data)->get();
    }

    /**
     * Create a new record
     *
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function store(array $data)
    {
        $instance = $this->getNewInstance();

        return $this->executeSave($instance, $data);
    }

    /**
     * Update the model instance
     *
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function update(array $data, $id)
    {
        $instance = $id instanceof Model ? $id : $this->find($id);
        return $this->executeSave($instance, $data);
    }

        /**
     * Update the model instance
     *
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function createOrUpdate(array $data, array $findByArray)
    {
        $instance = $this->findByMany($findByArray);
        return $instance ? $this->executeSave($instance, $data) : $this->store($data);
    }

    /**
     * Save the model
     *

     * @param mixed $instance
     * @param array $data
     * @return mixed
     */
    protected function executeSave($instance, array $data)
    {
        $data = $this->setBooleanFields($instance, $data);
        $instance->fill($data);
        $instance->save();
        return $instance;
    }

    /**
     * Delete a record or records
     *
     * @param array|string|Model $modal
     * @return mixed
     * @throws Exception
     */
    public function destroy(array|string|Model $modal)
    {
        if (is_array($modal)) {
            return $this->modelName::destroy($modal);
        } else {
            $instance =  $modal instanceof Model ? $modal :  $this->find($modal);
            return $instance->delete();
        }
    }

    /**
     * Count of all records
     *
     * @return int
     * @throws Exception
     */
    public function count(): int
    {
        return $this->getNewInstance()->count();
    }

    /**
     * @inheritDoc
     */
    public function getModelName(): string
    {
        if (!$this->modelName) {
            throw new RuntimeException('Model has not been set in ' . get_called_class());
        }

        return $this->modelName;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getQueryBuilder(): Builder
    {
        return $this->getNewInstance()->newQuery();
    }

    /**
     * @inheritDoc
     */
    public function getNewInstance()
    {
        $model = $this->getModelName();

        return new $model;
    }

    /**
     * Parse the order by data
     *
     * @param string $orderBy
     * @return void
     */
    protected function parseOrder($orderBy): void
    {
        if (substr($orderBy, -3) === 'Asc') {
            $this->setOrderDirection('asc');
            $orderBy = substr_replace($orderBy, '', -3);
        } elseif (substr($orderBy, -4) === 'Desc') {
            $this->setOrderDirection('desc');
            $orderBy = substr_replace($orderBy, '', -4);
        }

        $this->setOrderBy($orderBy);
    }

    /**
     * Set the order by field
     *
     * @param string $orderBy
     * @return void
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * Get the order by field
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Set the order direction
     *
     * @param string $orderDirection
     * @return void
     */
    public function setOrderDirection($orderDirection)
    {
        $this->orderDirection = $orderDirection;
    }

    /**
     * Get the order direction
     *
     * @return string
     */
    public function getOrderDirection()
    {
        return $this->orderDirection;
    }

    /**
     * Set the model's boolean fields from the input data
     *
     * @param mixed $instance
     * @param array $data
     * @return array
     */
    protected function setBooleanFields($instance, array $data)
    {
        foreach ($this->getModelBooleanFields($instance) as $booleanField) {
            $data[$booleanField] = Arr::get($data, $booleanField, 0);
        }

        return $data;
    }

    /**
     * Retrieve the boolean fields from the model
     *
     * @param mixed $instance
     * @return array
     */
    protected function getModelBooleanFields($instance)
    {
        if (function_exists($instance::class . 'getBooleanFields')) {
            return $instance->getBooleanFields();
        }
        return [];
    }
}
