<?php


namespace ObjectsMicroservice\SaleObject\Components;


use ObjectsMicroservice\SaleObject\Sale;

class Material extends Sale
{

    public static function getTableName()
    {
        return 'materials';
    }

    public function getTypeNumber()
    {
        return 5;
    }
}