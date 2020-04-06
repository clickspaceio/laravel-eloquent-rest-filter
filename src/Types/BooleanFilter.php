<?php

namespace Clickspace\LaravelEloquentRestFilter\Types;


class BooleanFilter extends Type
{

    public function isValid() {
        return $this->value === "1" or $this->value === "0";
    }

    public function applyFilter() {
        switch ($this->operator) {
            case "=":
                $this->whereEqual();
                break;
            default:
                $this->exception(
                    "invalid_parameter",
                    [
                        "{$this->field}" => "invalid_value"
                    ]
                );
        }
        return $this->query;
    }

}