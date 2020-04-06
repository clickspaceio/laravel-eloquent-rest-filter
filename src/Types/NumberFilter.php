<?php

namespace Clickspace\LaravelEloquentRestFilter\Types;


class NumberFilter extends Type
{

    public function isValid() {
        return is_numeric($this->value);
    }

}