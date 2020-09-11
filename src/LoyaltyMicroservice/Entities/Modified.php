<?php


namespace LoyaltyMicroservice\Entities;


use Microservices\DataObjects\Model;


class Modified extends Model
{

    public $specialId;
    public $modifierId;
    public $result;
    public $createdAt;

    public function __construct($fields=[])
    {
        if(!empty($fields)) $this->pull($fields);
    }

    public function getFor($specialId,$result){
        $where = [
            'result' => $result,
            'specialId' => $specialId,
            'LIMIT' => [0,100],
        ];

        $what = ['id','modifierId'];

        return $this->find($where,$what);
    }


    public static function getTableName()
    {
        return 'modified';
    }
}