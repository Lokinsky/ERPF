<?php


namespace ObjectsMicroservice\SaleObject\Components;


class PriceModifier extends Base
{
    public $type;
    public $value;

    public static function getTableName()
    {
        return 'priceModifiers';
    }

    public function getFieldNames()
    {
        $parentFields = parent::getFieldNames();
        $childFields = [
            'value',
            'type',
        ];

        return array_merge($parentFields, $childFields);
    }

    public function getType(){
        return $this->type;
    }

    public function getArea(){
        $type = $this->getType();
        $area = mb_substr($type,0,1);

        switch ($area){
            case 1:
                return 'nested';
            case 2:
                return 'lvl';
            case 3:
                return 'all';
            default:
                return 'self';
        }
    }

    public function getParts(){
        $type = $this->getType();

        $parts = mb_substr($type,1,1);

        switch ($parts){
            case 1:
                return ['self'];
            case 2:
                return ['props'];
            default:
                return ['self','props'];
        }
    }

    public function getAims(){
        $type = $this->getType();
        $aims = mb_substr($type,2,1);

        switch ($aims){
            case 1:
                return ['cost'];
            case 2:
                return ['price'];
            default:
                return ['cost','price'];
        }
    }

    public function getForm(){
        $type = $this->getType();
        $form = mb_substr($type,3,1);

        switch ($form){
            case 1:
                return '%1';
            case 2:
                return '%2';
            default:
                return 'direct';
        }
    }

    public function getDirection(){
        $type = $this->getType();
        $direction = mb_substr($type,4,1);

        switch ($direction){
            case 1:
                return '-';
            default:
                return '+';
        }
    }

    public function getTypeNumber()
    {
        return 7;
    }

}