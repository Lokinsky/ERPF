<?php


namespace StockMicroservice\Entities;


use Microservices\DataObjects\ArrayObject;
use Microservices\DataObjects\Model;

class Store extends Model
{
    public $objectId;
    public $objectType;
    public $duration;
    public $initiatorId;
    public $ownerId;
    public $createdAt;

    /**
     * Store constructor.
     * @param array|ArrayObject $fields
     */
    public function __construct($fields=[])
    {
        if(is_object($fields)) $fields = $fields->getFields();
        if(!empty($fields)){
            $this->pull($fields);
        }
    }

    public static function getType(){
        return 1;
    }

    public static function getTableName()
    {
        return 'store';
    }



}