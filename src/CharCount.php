<?php

namespace Foamycastle\Util\Validator;

use Foamycastle\Util\Validator\Validator;

class CharCount extends Validator
{
    private int $maxChars;
    public function __construct(int $maxChars)
    {
        parent::__construct();
        $this->maxChars = $maxChars;
    }

    protected function validation(mixed $dataToTest): bool
    {
        return
            is_string($dataToTest) &&
            strlen($dataToTest) <= $this->maxChars;
    }

}