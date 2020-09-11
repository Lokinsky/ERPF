<?php


namespace LoyaltyMicroservice\Entities;


use BooleanParser\Parser;
use Microservices\DataObjects\ExpiringModel;

class Special extends ExpiringModel
{
    public $name;
    public $description;
    public $expression;
    public $status;

    protected $context;

    public function getConditions(){
        $conditionIds = [];
        $chars = mb_str_split($this->expression);
        if(empty($chars)) $chars = [];
        $current = '';
        foreach ($chars as $char){
            if(is_int($char)){
                $current .= $char;
            }else{
                if(is_numeric($current)) $conditionIds[] = $current;
                $current = '';
            }

        }

        return $conditionIds;
    }

    public function findActive($limit=[]){
        $where = [
            'status' => 1,
            'LIMIT' => (empty($limit)) ? static::createLimit(null) : $limit,
        ];

        return $this->find($where);
    }


    public function setContext($conditionsValues){
        $this->context = $conditionsValues;
    }

    public function getContext(){
        if(empty($this->context)) return [];

        return $this->context;
    }

    public function getExpression(){
        if(empty($this->expression)) return '';

        return $this->expression;
    }

    public function evaluate(){
        $booleanParser = new Parser();

        return $booleanParser->evaluate($this->getExpression(),$this->getContext());
    }
}