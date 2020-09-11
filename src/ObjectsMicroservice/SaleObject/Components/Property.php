<?php


namespace ObjectsMicroservice\SaleObject\Components;


class Property extends Base
{
//    public $value;
//    public $price;
//    public $cost;
//    public $amount;

    public static function getTableName()
    {
        return 'properties';
    }

    public function getFieldNames()
    {
        $parentFields = parent::getFieldNames();
        $childFields = [
            'price',
            'cost',
            'amount',
            'value',
        ];

        return array_merge($parentFields, $childFields);
    }


    public function getCost(){
        if(isset($this->cost)){
            return $this->cost;
        }

        return 0;
    }

    public function getPrice(){
        if(isset($this->price)){
            return $this->price;
        }

        return 0;
    }

    public function getAmount(){
        if(isset($this->amount)){
            return $this->amount;
        }

        return 1;
    }


    public function getTypeNumber()
    {
        return 6;
    }

}