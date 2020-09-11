<?php


namespace ObjectsMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{
    public static function getRules()
    {
        return [
            'createComponent' => [
                'cot' => ['int',['range'=>[0,7]]],
                'id' => ['empty'],
                'name' => ['!empty',['lmax' => 32]],
                'description' => [['lmax' => 256]],
                'cost,price'=> ['numeric',['lmax' => 20]],
                'amount' => ['numeric'],
                'createdAt' => ['empty'],
            ],
            'getComponent' => [
                'id' => ['int'],
                'cot' => ['int',['range'=>[0,7]]],
            ],
            'editComponent' => [
                'cot' => ['int',['range'=>[0,7]]],
                'name' => ['!empty',['lmax' => 32]],
                'description' => [['lmax' => 256]],
                'cost,price'=> ['numeric',['lmax' => 20]],
                'amount' => ['numeric'],
                'createdAt' => ['empty'],
            ],
            'createSaleObject' => [
                'id' => ['empty'],
                'name' => ['!empty',['lmax' => 32]],
                'description' => [['lmax' => 256]],
                'cost,price'=> ['numeric',['lmax' => 20]],
                'amount' => ['numeric'],
                'createdAt' => ['empty'],
                'serialized' => [['lmax' => 8196]],
            ],
            'updateSaleObject' => [
                'id' => ['int'],
                'name' => ['!empty',['lmax' => 32]],
                'description' => [['lmax' => 256]],
                'cost,price'=> ['numeric',['lmax' => 20]],
                'amount' => ['numeric'],
                'createdAt' => ['empty'],
                'serialized' => [['lmax' => 8196]],
            ]
        ];
    }
}