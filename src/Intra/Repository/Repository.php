<?php

namespace Intra\Repository;

use Illuminate\Database\Eloquent\Model;

abstract class Repository implements RepositoryInterface
{
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

    public function all($columns = ['*'], $order = 'date', $orderType = 'desc')
    {
        return $this->model->orderBy('date', $order)->get($columns);
    }

    public function paginate($take = 10, $skip = 0, $columns = ['*'], $order = 'date', $orderType = 'desc')
    {
        return $this->model->orderBy($order, $orderType)->take($take)->skip($skip)->get($columns);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, $id, $attribute = "id")
    {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    public function count()
    {
        return $this->model->count();
    }
}
