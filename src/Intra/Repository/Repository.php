<?php

namespace Intra\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

abstract class Repository implements RepositoryInterface
{
    /* @var Model|Builder $model*/
    private $model;

    public function __construct()
    {
        $this->makeModel();
    }

    abstract public function model();

    private function makeModel()
    {
        $modelName = $this->model();

        $model = new $modelName();

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }
        return $this->model = $model;
    }

    public function all($columns = ['*'], $order = 'created_at', $orderType = 'desc')
    {
        return $this->model->orderBy($order, $orderType)->get($columns);
    }

    public function first($condition, $columns = ['*'], $order = 'created_at', $orderType = 'desc')
    {
        return $this->model->where($condition)->orderBy($order, $orderType)->first($columns);
    }

    public function paginate($take = 10, $skip = 0, $columns = ['*'], $order = 'created_at', $orderType = 'desc')
    {
        return $this->model->orderBy($order, $orderType)->take($take)->skip($skip)->get($columns);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, $condition)
    {
        return $this->model->where($condition)->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function find($condition, $columns = ['*'])
    {
        return $this->model->where($condition)->get($columns);
    }

    public function count()
    {
        return $this->model->count();
    }
}
