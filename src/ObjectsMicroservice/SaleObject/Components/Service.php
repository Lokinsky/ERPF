<?php


namespace ObjectsMicroservice\SaleObject\Components;


use ObjectsMicroservice\SaleObject\Sale;

class Service extends Sale
{

    public static function getTableName()
    {
        return 'services';
    }

    public function getTypeNumber()
    {
        return 1;
    }
}