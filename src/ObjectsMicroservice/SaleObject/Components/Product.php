<?php


namespace ObjectsMicroservice\SaleObject\Components;


use ObjectsMicroservice\SaleObject\Sale;

class Product extends Sale
{
    public static function getTableName()
    {
        return 'products';
    }

    public function getTypeNumber()
    {
        return 3;
    }
}