<?php


namespace StockMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{
    public static function getRules()
    {
        return [

            'addNote' => [
                'id,initiatorId,createdAt' => ['empty'],
                'objectId,duration,ownerId' => ['int',['lmax'=>11]],
                'objectType' => ['int',['lmax'=>1]],
            ],
            'getNote' => [
                'id' => ['int',['lmax'=>11]],
            ],
            'editNote' => [
                'id,initiatorId,createdAt' => ['empty'],
                'objectId,duration,ownerId' => ['int',['lmax'=>11]],
                'objectType' => ['int',['lmax'=>1]],
            ],
            'createReason' => [
                'id,createdAt' => ['empty'],
                'name' => [['lmax'=>32]],
                'description' => [['lmax'=>256]],
            ],
            'editReason' => [
                'id,createdAt' => ['empty'],
                'name' => [['lmax'=>32]],
                'description' => [['lmax'=>256]],
            ],
            'getReason' => [
                'id' => ['int',['lmax'=>11]],
                'name' => [['lmax'=>32]],
            ],
            'deleteReason' => [
                'id' => ['int',['lmax'=>11]],
                'name' => [['lmax'=>32]],
            ],
            "createGroup"=>[
                'id' => ['empty'],
                'name' => [['length' => [1, 25]]],
                'address' => [['length' => [1,50]]],
            ],
            "editGroup"=>[
                'id' => [!'empty'],
                'name' => [['length' => [1, 25]]],
                'address' => [['length' => [1,50]]],
            ],
            "createGroupContent"=>[
                'id' => ['empty'],
                'idGroup' => [['lmax' => 11]],
                'idObject ' => [['lmax' => 11]],
                'counts ' => [['lmax' => 11]],
                'date ' => [['lmax' => 11]],
            ],
            "editGroupContent"=>[
                'id' => ['!empty'],
                'idGroup' => [['lmax' => 11]],
                'idObject ' => [['lmax' => 11]],
                'counts ' => [['lmax' => 11]],
                'date ' => [['lmax' => 11]],
            ],
            "createTag"=>[
                'id' => ['empty'],
                'name' => [['length' => [1, 25]]],
                'expires' => [['lmax' => 11]],
                'createdAt'=>['empty'],
            ],
            "delete"=>[
                'id'=>['!empty']
            ],
            'editTag' => [
                'id' => ['!empty'],
                'name' => [['length' => [1, 25]]],
                'expires' => [['lmax' => 11]],
                'createdAt'=>['empty'],
            ],
            'get'=>[
                'id'=> [['lmax'=>11]]
            ],
            'getByTag'=>[
                'id'=> ['!empty',['lmax'=>11]]
            ],
            "addTag"=>[
                'id' => ['!empty'],
                'idTag'=>['!empty']
            ],
            "deleteTag"=>[
                'id' => ['!empty'],
                'idTag'=>['!empty']
            ]
        ];
    }


}