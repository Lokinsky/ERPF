<?php


namespace OrderConveyerMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{
    public static function getRules()
    {
        return [
            "create"=>[
                'id' => ['empty'],
                'name' => [['length' => [1, 25]]],
                'priority' => [['length' => [1, 25]]],
            ],
            "delete"=>[
                'id'=>['!empty']
            ],
            'edit' => [
                'id' => ['!empty'],
                'name' => [['length' => [1, 25]],'!empty'],
                'priority' => [['length' => [1, 25]],"!empty"],
            ],
            'get'=>[
                'id'=> [['lmax'=>11]]
            ]
        ];
    }

}