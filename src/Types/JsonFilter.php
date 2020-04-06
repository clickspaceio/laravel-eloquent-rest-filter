<?php

namespace Clickspace\LaravelEloquentRestFilter\Types;


class JsonFilter extends Type
{

    public function isValid() {
        if (preg_match('/->/', $this->field)) {
            return !is_array($this->value);
        } else {
            return is_array($this->value);
        }
    }

    public function applyFilter() {
        switch ($this->operator) {
            case "=":
                $this->whereEqual();
                break;
            default:
                $this->exception("Invalid operator in {$this->field}: {$this->operator}");

        }
        return $this->query;
    }

    public function whereEqual() {
        $this->query->whereJsonContains($this->field, $this->value);
    }

}