<?php

namespace Clickspace\LaravelEloquentRestFilter\Types;


use Carbon\Carbon;

class DateFilter extends Type
{
    public function __construct($query, $field, $operator, $value)
    {
        $this->query = $query;
        $this->field = $field;
        $this->operator = $operator;
        try {
            $this->value = Carbon::createFromTimestamp($value);
        } catch (\Exception $exception) {
            $this->value = null;
        }

        if (!$this->isValid()) {
            $this->exception(
                "invalid_parameter",
                [
                    "$this->field" => "invalid_value"
                ]
            );
        }
    }

    public function isValid() {
        return $this->value instanceof Carbon;
    }

}