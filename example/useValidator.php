<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Foamycastle\Util\Validator\CharCount;
use Foamycastle\Util\Validator\Validator;
use Foamycastle\Util\Validator\HexValidator;

//Register an object that extends the Validator base class
Validator::Register(HexValidator::class);
echo Validator::HexValidator('7dddf7e') ? "VALID HEX\n": "NOT VALID HEX\n";

//Optionally pass arguments to the Validator constructor.
//The CharCount Validator indicates that the tested string
//has less than the specified number of characters.
Validator::Register(CharCount::class,32);
echo Validator::CharCount('7dddf7e') ? "VALID CHARCOUNT\n": "INVALID CHARCOUNT\n";

//use a callable to enjoy access to a specific scope
//using the ::From method requires a name argument
Validator::From(
    "Hex",
    function ($hex) {
        return preg_match('/^[0-9a-fA-F]*$/i', $hex)==1;
    }
);
echo Validator::Hex('7dddf7e') ? "VALID HEX\n": "NOT VALID HEX\n";