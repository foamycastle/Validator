<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Foamycastle\Util\Validator\Validator;
use Foamycastle\Util\Validator\HexValidator;

//Register an object that extends the Validator base class
Validator::Register(HexValidator::class);
echo Validator::HexValidator('7dddf7e') ? "VALID HEX\n": "NOT VALID HEX\n";

//Optionally name the registration or default to using the class name
Validator::Register(HexValidator::class,"ValidHex");
echo Validator::ValidHex('7dddf7e') ? "VALID HEX\n": "NOT VALID HEX\n";

//use a callable to enjoy access to a specific scope
//using the ::From method requires a name argument
Validator::From(
    "Hex",
    function ($hex) {
        return preg_match('/^[0-9a-fA-F]*$/i', $hex)==1;
    }
);
echo Validator::Hex('7dddf7e') ? "VALID HEX\n": "NOT VALID HEX\n";