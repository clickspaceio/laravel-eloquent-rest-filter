<?php

namespace Clickspace\LaravelEloquentRestFilter\Types;


class StringFilter extends Type
{

    public function applyFilter() {
        switch ($this->operator) {
            case "=":
                $this->whereEqual();
                break;
            case "like":
                $this->whereLike();
                break;
            case "!=":
                $this->whereDifferent();
                break;
            case "in:":
                $this->whereIn();
                break;
            default:
                $this->whereLike();
                break;
        }
        return $this->query;
    }

}