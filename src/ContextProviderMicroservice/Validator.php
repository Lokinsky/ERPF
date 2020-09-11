<?php


namespace ContextProviderMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{
    public static function getRules()
    {
        return [
            "create"=>[
                'id' => ['empty'],
                'name' => [['length' => [1, 25]]],
                'description' => [['lmax' => 256]],
                'stages' => [['lmax' => 256]],
            ],
            "createStage"=>[
                'id' => ['empty'],
                'name' => [['length' => [1, 25]]],
                'description' => [['lmax' => 256]],
            ],
            "delete"=>[
                'id'=>['!empty']
            ],
            'editStage' => [
                'id' => ['!empty'],
                'name' => [['lmax' => 32],'!empty'],
                'description' => [['lmax' => 256],"!empty"],
            ],
            'edit' => [
                'id' => ['!empty'],
                'name' => [['lmax' => 25],'!empty'],
                'description' => [['lmax' => 256],"!empty"],
                'stages' => [['lmax' => 256],"!empty"],

            ],
            'get'=>[
                'id'=> [['lmax'=>11]]
            ]
        ];
    }

}