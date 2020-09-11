<?php


namespace StockMicroservice\Entities;


use Microservices\DataObjects\Model;

class Operation extends Model
{
    public $reasonId;
    public $initiatorId;
    public $storeId;
    public $aimId;
    /**
     * Типы операций: 0 - добавление(по умолчанию), 1 - забрать, 2 поменять владельца
     *
     * @var int
     */
    public $type;
    public $createdAt;

    public function __construct($fields=[])
    {
        if(!empty($fields)) $this->pull($fields);
    }
}