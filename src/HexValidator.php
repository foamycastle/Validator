<?php

namespace Foamycastle\Util\Validator;


class HexValidator extends Validator
{
    protected function validation(mixed $dataToTest): bool
    {
        if (!is_string($dataToTest)) {
            return false;
        }
        return preg_match('/^[A-Fa-f0-9]+$/', $dataToTest)==1;
    }

}