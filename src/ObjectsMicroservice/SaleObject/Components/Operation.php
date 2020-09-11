<?php


namespace ObjectsMicroservice\SaleObject\Components;


use ObjectsMicroservice\SaleObject\Sale;

class Operation extends Sale
{
    public static function getTableName()
    {
        return 'operations';
    }

    public function getTypeNumber()
    {
        return 2;
    }
}