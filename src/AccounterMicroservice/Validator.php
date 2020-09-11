<?php


namespace AccounterMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{
    public static function getRules()
    {
        return [
            "create"=>[
                'id' => ['empty'],
                'name' => [['length' => [1, 25]]],
                'ownerId '=>[['lmax' => 11]],
                'description' => [['lmax' => 50]],
            ],
            "delete"=>[
                'id'=>['!empty']
            ],
            'edit' => [
                'id' => ['!empty'],
                'name' => [['lmax' => 25],'!empty'],
                'description' => [['lmax' => 50],"!empty"],
            ],
            'get'=>[
                'id'=> [['lmax'=>11]]
            ]
        ];
    }

}