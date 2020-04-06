<?php

namespace Clickspace\LaravelEloquentRestFilter\Types;

use Clickspace\LaravelClickException\Exceptions\InvalidParameterException;

class Type
{

    public $query;
    public $field;
    public $operator;
    public $value;

    public function __construct($query, $field, $operator, $value)
    {
        $this->query = $query;
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;

        if (!$this->isValid()) {
            $this->exception(
                "invalid_parameter",
                [
                    "$this->field" => "invalid_value"
                ]
            );
        }
    }

    public function exception($code, $data = null) {
        switch ($code) {
            case "invalid_parameter":
                throw new InvalidParameterException($data);
                break;
            default:
                throw new \Exception($code);
        }
    }

    public function isValid() {
        return true;
    }

    public function applyFilter() {
        switch ($this->operator) {
            case "=":
                $this->whereEqual();
                break;
            case "like":
                $this->whereLike();
                break;
            case ">":
                $this->whereGreater();
                break;
            case ">=":
                $this->whereGreaterOrEqual();
                break;
            case "<":
                $this->whereLess();
                break;
            case "<=":
                $this->whereLessOrEqual();
                break;
            case "!=":
                $this->whereDifferent();
                break;
            case "in:":
                $this->whereIn();
                break;
        }
        return $this->query;
    }

    public function whereEqual() {
        $this->query->where($this->field, $this->value);
    }

    public function whereLike() {
        $this->query->where($this->field, 'like', $this->value);
    }

    public function whereGreater() {
        $this->query->where($this->field, '>', $this->value);
    }

    public function whereGreaterOrEqual() {
        $this->query->where($this->field, '>=', $this->value);
    }

    public function whereLess() {
        $this->query->where($this->field, '<', $this->value);
    }

    public function whereLessOrEqual() {
        $this->query->where($this->field, '<=', $this->value);
    }

    public function whereDifferent() {
        $this->query->where($this->field, '<>', $this->value);
    }

    public function whereIn() {
        $value = is_array($this->value) ? $this->value : explode(',', $this->value);
        $this->query->whereIn($this->field, $value);
    }

}