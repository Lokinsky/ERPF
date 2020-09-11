<?php


namespace ObjectsMicroservice\SaleObject\Components;


use ObjectsMicroservice\SaleObject\Sale;

class Item extends Sale
{
    public static function getTableName()
    {
        return 'items';
    }

    public function getTypeNumber()
    {
        return 4;
    }
}