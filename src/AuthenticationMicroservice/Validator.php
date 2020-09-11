<?php


namespace AuthenticationMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{
    public static function getRules()
    {
        return [
            'changePassword' => [
                'newPassword' => [['length' => [6, 64]]],
            ],
            'userLogin' => [
                'login' => [['length' => [1, 32]], 'onlyLatinCharsAndNumeric'],
                'email' => [['length' => [3, 64]], 'email'],
                'password' => [['lmax' => 64]],
            ],
            'userRegister' => [
                'login' => [['length' => [1, 32]], 'onlyLatinCharsAndNumeric'],
                'email' => [['length' => [3, 64]], 'email'],
                'surname' => [['lmax' => 64]],
                'password' => [['length' => [6, 64]]],
                'name,patronymic' => [['lmax' => 32]],
                'hash' => [['length' => [32, 128]]],
                'createdAt' => ['int', ['lmax' => 11]],
            ],
            'userUpdate' => [
                'userId' => [['lmax' => 11]],
                'surname' => [['lmax' => 64]],
                'name,patronymic' => [['lmax' => 32]],
                'password,hash,createdAt' => ['empty'],
            ],
            'lawCreate' => [
                'userId,actionId' => ['!empty'],
                'userId,actionId,createdAt,duration' => ['int', ['lmax' => 11]],
            ],
            'tokenCreate' => [
                'userId,createdAt,duration' => ['int', ['lmax' => 11]],
            ],

            'actionCreate' => [
                'id,createdAt' => ['int', ['lmax' => 11]],
                'value' => [['lmax' => 64]],
            ]
        ];
    }
}