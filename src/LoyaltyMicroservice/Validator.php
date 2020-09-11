<?php


namespace LoyaltyMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{


    public static function getRules()
    {
        return [
            'addModified' => [
                'specialId,modifierId' => ['int',['lmax'=>11]],
                'result' => [['lmax'=>7]],
                'createdAt' => ['empty'],
            ],

            'editModified' => [
                'id' => ['!empty'],
                'id,specialId,modifierId' => ['int',['lmax'=>11]],
                'result' => [['lmax'=>7]],
                'createdAt' => ['empty'],
            ],

            'getModified' => [
                'id' => ['!empty','int',['lmax'=>11]],
            ],

            'getSpecialModifiers' => [
                'specialId' => ['!empty','int',['lmax'=>11]],
                'result' => ['!null']
            ],

            'getAllSpecialModifiers' => [
                'specialId' => ['!empty','int',['lmax'=>11]],
            ],
            "create"=>[
                'id' => ['empty'],
                'name' => [['length' => [1, 32]]],
                'description' => [['lmax' => 256]],
            ],
            "delete"=>[
                'id'=>['!empty']
            ],
            'edit' => [
                'id' => ['!empty'],
                'name' => [['lmax' => 32],'!empty'],
                'description' => [['lmax' => 256],"!empty"],
            ],
            'get'=>[
                'id'=> [['lmax'=>11]]
            ]
        ];
    }
}