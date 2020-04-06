<?php

namespace Clickspace\LaravelEloquentRestFilter;

use Clickspace\LaravelClickException\Exceptions\InvalidParameterException;
use Clickspace\LaravelEloquentRestFilter\Types\Type;

class Filter
{

    public $fields = [];
    public $orderFields = [];
    public $searchFields = [];

    public $filters = [];
    public $request;
    public $filtersRequest;
    public $limit = 50;
    public $q;
    public $orderByField;
    public $orderByDirection = "ASC";

    public function __construct($fields, $orderFields, $searchFields, $request = null)
    {
        $this->fields = $fields;
        $this->orderFields = $orderFields;
        $this->searchFields = $searchFields;
        $this->request = $request ?? request();
        $this->load();
    }

    public function loadArray($id, $field, $operator, $value) {
        $filters = [];
        foreach ($value as $filterValueItemKey => $filterValueItemValue) {
            $fieldItem = "{$field}->{$filterValueItemKey}";
            if (is_array($filterValueItemValue)) {
                $filters = array_merge($filters, $this->loadArray($id, $fieldItem, $operator, $filterValueItemValue));
            } else {
                $filters[] = [
                    'id' => $id,
                    'field' => $fieldItem,
                    'operator' => $operator,
                    'value' => $filterValueItemValue
                ];
            }
        }
        return $filters;
    }

    public function loadField($field, $value) {
        $filters = [];
        $id = $field;
        $operator = '=';
        if (is_array($value)) {
            $filters = array_merge($filters, $this->loadArray($id, $field, $operator, $value));
        } else {
            if (preg_match('/(>=|<=|!=|>|<|in:)(.{1,})/', urldecode($value), $matches)) {
                $operator = $matches[1];
                $value = $matches[2];
            } elseif (preg_match('/%/', urldecode($value), $matches)) {
                $operator = 'like';
            } elseif ($value == 'null') {
                $value = null;
            }
            $filters[] = [
                'id' => $id,
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ];
        }
        return $filters;
    }

    public function loadFilter() {
        $this->filtersRequest = $this->request->all(array_keys($this->fields));
        $filters = [];
        foreach ($this->filtersRequest as $filterField => $filterValue) {
            if (is_null($filterValue))
                continue;
            if (is_array($filterValue) and is_integer(array_keys($filterValue)[0])) {
                foreach ($filterValue as $index => $value) {
                    $filters = array_merge($filters, $this->loadField($filterField, $value));
                }
            } else {
                $filters = array_merge($filters, $this->loadField($filterField, $filterValue));
            }
        }
        $this->filters = $filters;
    }

    public function loadLimit() {
        if (isset($this->request->limit)) {
            $limit = (integer) $this->request->limit;
            if (is_numeric($this->request->limit) && $limit >= 1 && $limit <= 100) {
                $this->limit = $limit;
            } else {
                throw new InvalidParameterException([
                    "limit" => "invalid_value"
                ]);
            }
        }
    }

    public function loadOrderBy() {
        if (isset($this->request->order_by)) {
            $sortRule = explode(',', $this->request->order_by);
            $field = $sortRule[0];
            $direction = count($sortRule) > 1 ? (mb_strtolower($sortRule[1]) === 'asc' ? 'ASC' : 'DESC') : 'ASC';

            if (in_array($field, $this->orderFields)) {
                $this->orderByField = $field;
                $this->orderByDirection = $direction;
            } else {
                throw new InvalidParameterException([
                    "order_by" => "invalid_value"
                ]);
            }
        }
    }

    public function loadSearch() {
        $this->q = str_replace(' ', '%', $this->request->q);
    }

    public function load() {
        $this->loadFilter();
        $this->loadLimit();
        $this->loadOrderBy();
        $this->loadSearch();
    }

    public function applyFilter($query, $filter) {
        $filterClass = $this->fields[$filter['id']];
        if (is_subclass_of($filterClass, Type::class))
            return (new $filterClass($query, $filter['field'], $filter['operator'], $filter['value']))->applyFilter();
        else
            return $query;
    }

    public function applyFilters($query) {
        foreach ($this->filters as $filter) {
            $query = $this->applyFilter($query, $filter);
        }
        return $query;
    }

    public function applyOrderBy($query) {
        if ($this->orderByField) {
            $query->orderBy($this->orderByField, $this->orderByDirection);
        }
        return $query;
    }

    public function applySearch($query) {

        if ($this->q && $this->searchFields) {
            $q = $this->q;
            $searchFields = $this->searchFields;
            $query->where(function ($query) use ($searchFields, $q) {
                foreach ($searchFields as $searchField) {
                    $query->orWhere($searchField, 'like', "%{$q}%");
                }
            });
        }
        return $query;
    }

    public function apply($query) {
        $query = $this->applyFilters($query);
        $query = $this->applyOrderBy($query);
        $query = $this->applySearch($query);
        return $query;
    }

}