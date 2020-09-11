<?php


namespace StaffMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{
    public static function getRules()
    {
        return [
            'roleCreate' => [
                'id' => ['empty'],
                'name' => [['length' => [1, 32]]],
                'description' => [['lmax' => 256]],
            ],
            'roleEdit' => [
                'id' => ['!empty'],
                'name' => [['lmax' => 32]],
                'description' => [['lmax' => 256]],
            ],
            'roleGet' => [
                'id' => [['lmax' => 11]],
                'name' => [['lmax' => 32]],
            ],
            'workerCreate' => [
                'id' => ['empty'],
                'userId,createdAt' => [['lmax' => 11]],
                'name,patronymic' => [['lmax' => 32]],
                'surname' => [['lmax' => 64]],
                'phones,skills' => [['lmax' => 256]],
                'links,attachments' => [['lmax' => 512]],
            ],
            'workerUpdate' => [
                'id,userId' => [['lmax' => 11]],
                'surname' => [['lmax' => 64]],
                'name,patronymic' => [['lmax' => 32]],
                'phones,skills' => [['lmax' => 256]],
                'links,attachments' => [['lmax' => 512]],
            ],
            'workerGet' => [
                'id,userId' => [['lmax' => 11]],
            ],
            'addRoleToWorker' => [
                'roleId,workerId' => ['!empty', ['lmax' => 11]],
            ],
            'getWorkerRoles' => [
                'id' => ['!empty', ['lmax' => 11]],
                'userId' => [['lmax' => 11]],
            ],
        ];
    }

}