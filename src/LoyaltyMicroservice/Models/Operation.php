<?php


namespace LoyaltyMicroservice\Models;


use Microservices\DataObjects\Model;

class Operation extends Model
{
    public $operation;
    public $idStaff;
    public $aimId;
    public $createdAt;

    public function __construct($fields=[])
    {
        if(!empty($fields)) $this->pull($fields);
    }
}