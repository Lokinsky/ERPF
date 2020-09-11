<?php


namespace CalculationsMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{
    public static function getRules()
    {
        return [
            'id' => [
                'id' => ['!empty','int'],
            ],
        ];
    }
}