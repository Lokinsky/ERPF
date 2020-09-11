<?php


namespace ClientManagerMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{
    public static function getRules()
    {
        return [
            "create"=>[
                'id' => ['empty'],
                'name' => [['length' => [1, 50]]],
                'description' => [['lmax' => 250]],
                'contact'=>['!empty',['length'=>[1,13]]],
                'status'=>['!empty',['length'=>[1,20]]]
            ],
            "createTag"=>[
                'id' => ['empty'],
                'name' => [['length' => [1, 50]]],
                'expires' => [['lmax' => 250]],
            ],
            "delete"=>[
                'id'=>['!empty']
            ],
            'edit' => [
                'id' => ['!empty'],
                'name' => [['length' => [1, 50]]],
                'description' => [['lmax' => 250]],
                'contact'=>['!empty',['length'=>[1,13]]],
                'status'=>['!empty',['length'=>[1,20]]]
            ],
            'editTag' => [
                'id' => ['empty'],
                'name' => [['length' => [1, 50]]],
                'expires' => [['lmax' => 250]],
            ],
            'get'=>[
                'id'=> [['lmax'=>11]]
            ],
            "addTag"=>[
                'id' => ['!empty'],
                'idCustomer'=>['!empry']
            ]
        ];
    }

}