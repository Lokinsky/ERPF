<?php


namespace CalculationsMicroservice\Entities;


use ObjectsMicroservice\SaleObject\Components\Base;
use ObjectsMicroservice\SaleObject\Components\PriceModifier;
use ObjectsMicroservice\SaleObject\Sale;

class SaleCalculator
{
    /**
     * @var Sale
     */
    public $parent;
    public $price;
    public $cost;
    public $areaLvls = [];
    public $areaNested = [];

    public $active = [];

    public $story = [
        'cost' => '',
        'price' => '',
    ];
    public $mode;


    public function __construct()
    {
        $this->price = 0;
        $this->cost = 0;
    }

    public function addCompressedStory(){
        $this->story['ccost'] = $this->compressStory($this->story['cost']);
        $this->story['cprice'] = $this->compressStory($this->story['price']);
    }

    public function compressStory($story){
        $goodSymbols = '{}[]()0123456789.+-×=|:';
//        $goodSymbols = mb_str_split($goodSymbols);
        $storySymbols = mb_str_split($story);

        $compressedStory = '';
        foreach ($storySymbols as $storySymbol){
            if(mb_strpos($goodSymbols,$storySymbol)!==false){
                $compressedStory .= $storySymbol;
            }
        }

        return $compressedStory;
    }

    /**
     * @param Sale $sale
     */
    public function calc($sale)
    {
        $this->prepare($sale);

        $before = [$this, 'before'];
        $after = [$this, 'after'];

        $this->before($sale);
        $sale->walkSales($before, $after, 1);
        $this->after($sale, 0);

        $this->addCompressedStory();
        $story = $this->getStory();

        return [
            'cost' => [
                'value' =>$this->getCost(),
                'story' => $story['cost'],
                'compressed' => $story['ccost'],
            ],
            'price' => [
                'value' => $this->getPrice(),
                'story' => $story['price'],
                'compressed' => $story['cprice'],
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function getStory()
    {
        return $this->story;
    }

    public function getCost()
    {
        return $this->cost;
    }


    public function getPrice(){
        return $this->price;
    }


    public function prepare($sale)
    {
        $lvl = 0;
        $this->parent = $sale;

        $before = [$this, 'scan'];
        $after = [$this, 'mapComponent'];

        $this->scan($sale, $lvl);
        $sale->walkSales($before, $after, $lvl + 1);
        $this->mapComponent($sale);
    }

    public function scan($sale,$lvl){
        $this->findModifiers($sale,$lvl);
        $this->findProperties($sale);
    }

    public function findProperties($sale){
        $props = $sale->getProperties();

        foreach ($props as $prop){
            $this->mapComponent($prop);
        }
    }

    /**
     * @param Sale $sale
     */
    public function findModifiers($sale, $lvl)
    {
        $modifiers = $sale->getPriceModifiers();

        foreach ($modifiers as $modifier) {
            $this->mapComponent($modifier);
            $area = $modifier->getArea();
            $address = $modifier->getAddress();
            if ($area == 'lvl') {
                $this->areaLvls[$lvl][] = $address;
            } elseif ($area == 'all') {
                $this->turnOnModifier($address);
            } elseif ($area == 'nested') {
                $this->areaNested[$sale->getAddress()][] = $address;
            }
        }
    }

    public function mapComponent($component)
    {
        $this->parent->loadComponentToMap($component);
    }

    public function turnOnModifier($address)
    {
        if (!in_array($address, $this->active)) {
            $this->active[] = $address;
        }
    }


    /**
     * @param Sale $sale
     */
    public function before($sale)
    {
        $this->saleStartTrigger($sale);
//        $this->addStart($sale);
        $this->turnOnNestedModifiers($sale);
//        $this->turnOffLvlModifiers($lvl);

    }

    public function saleStartTrigger($sale){
        $string = $sale->getName().':'.$sale->getAddress().'{ ';
        $this->story['cost'] .= $string;
        $this->story['price'] .= $string;
    }

    public function saleEndTrigger($sale){
        $string = '|'.$sale->getName().':'.$sale->getAddress().'} ';
        $this->story['cost'] .= $string;
        $this->story['price'] .= $string;
    }

    public function propCalcTrigger($property){
        if(is_array($property)){
            if(empty($property['name'])){
                $name = 'Unnamed';
            }else{
                $name = $property['name'];
            }
            $name .= ':'.$property['address'];
            $cost = $this->createTermString($property['cost']);
            $price = $this->createTermString($property['price']);

            if(empty($amount) or empty($property['amount'])){
                $amount = 1;
            }else{
                $amount = $property['amount'];
            }
        }else{
            $name = $property->getName();
            $name .= ':'.$property->getAddress();
            $cost = $this->createTermString($property->getCost());
            $price = $this->createTermString($property->getPrice());
            $amount = $property->getAmount();
        }

        $costString = ' '.$cost.'×'.$amount.'('.$name.')';
        $priceString = ' '.$price.'×'.$amount.'('.$name.')';

        $this->story['cost'] .= $costString;
        $this->story['price'] .= $priceString;
    }

    public function saleStartPerformTrigger($sale){
        $amount = $sale->getAmount();
        $cost = $sale->getCost();
        $price = $sale->getPrice();
//        $priceString = 'Итого: ';
        // подразумевается, что базовые значения есть в опк и они не изменялись
        $costString = $cost.'×'.$amount;
        $priceString = $price.'×'.$amount;

        $this->story['cost'] .= 'Итого: '.$costString;
        $this->story['price'] .= 'Итого: '.$priceString;

    }

    public function saleEndPerformTrigger($sum){
        $termCost = $sum['cost'];
        $termPrice = $sum['price'];

        $this->story['cost'] .= ' = '.$termCost;
        $this->story['price'] .= ' = '.$termPrice;
    }

//    public function startCalcDifferentTrigger($sale){
//
//    }
//
//    public function endCalcDifferentTrigger($sale){
//
//    }
//
//    public function startUseModifierTrigger($modifier){
//
//    }

    public function endUseModifierTrigger($different){
        $addCost = $this->createTermString($different['cost']);
        $addPrice = $this->createTermString($different['price']);
        $name = $different['name'].':'.$different['address'];

        $costNote = $addCost.'['.$name.']';
        $priceNote = $addPrice.'['.$name.']';

        $this->story['cost'] .= ' '.$costNote;
        $this->story['price'] .= ' '.$priceNote;
    }

    public function createTermString($value){
        if(empty($value)) $value = 0;
        if($value<0){
            $term = '- '.abs($value);
        }else{
            $term = '+ '.$value;
        }

        return $term;
    }


//    public function addStart($sale)
//    {
//        $note = $sale->getName() . '{';
//        $this->addNoteToEnd($note);
//    }

//    public function addNoteToEnd($note)
//    {
//        $this->story .= $note;
//    }

    /**
     * @param Sale $sale
     */
    public function turnOnNestedModifiers($sale)
    {
        $address = $sale->getAddress();
        if (!empty($this->areaNested[$address])) {
            foreach ($this->areaNested[$address] as $modifierAddress) {
                $this->turnOnModifier($modifierAddress);
            }
        }
    }

    public function after($sale, $lvl)
    {
        $this->turnOffNestedModifiers($sale);
        $this->turnOnLvlModifiers($lvl);

//        $basSaleArray = $this->parent->getComponentFromMap($sale->getAddress());
//        $baseSelfValues = $this->sumComponent($basSaleArray);
//        $this->startResume($baseSelfValues);
        $this->saleStartPerformTrigger($sale);

        $result = $this->performSale($sale);
        $sum = $this->sumData($result['self'], $result['props']);
        $this->saleEndPerformTrigger($sum);

        $this->cost += $sum['cost'];
        $this->price += $sum['price'];

        $this->saleEndTrigger($sale);
//        $this->addEnd($sale);
    }

    public function turnOffNestedModifiers($sale)
    {
        $address = $sale->getAddress();
        if (!empty($this->areaNested[$address])) {
            foreach ($this->areaNested[$address] as $modifierAddress) {
                $this->turnOffModifier($modifierAddress);
            }
        }
    }

    public function turnOffModifier($address)
    {
        $findModifier = array_search($address, $this->active);
        if ($findModifier !== false) {
            unset($this->active[$findModifier]);
        }
    }

    public function turnOnLvlModifiers($lvl)
    {
        if (empty($this->areaLvls)) return;
        foreach ($this->areaLvls as $areaLvl => $modifiers) {
            if (empty($modifiers)) continue;
            foreach ($modifiers as $modifierAddress) {
                if ($lvl == $areaLvl) {
                    $this->turnOnModifier($modifierAddress);
                } else {
                    $this->turnOffModifier($modifierAddress);
                }
            }
        }
    }

    /**
     * @param Sale $sale
     */
    public function performSale($sale)
    {
        $base['self'] = $this->sumComponent($sale);
        $base['props'] = $this->sumProperties($sale);
//        $this->saleStartPerformTrigger($this->sumData($base['props'], $base['self']));
//        $this->startResume($this->sumData($base['props'], $base['self']));

        $current = $base;

        $localModifiers = $sale->getPriceModifiers();
        foreach ($localModifiers as $modifier) {
            $area = $modifier->getArea();
            if ($area == 'self') {
                $current = $this->useModifier($base, $current, $modifier);
            }
        }

        $modifier = new PriceModifier();
        foreach ($this->active as $activeModifierAddress) {
            $fields = $this->parent->getComponentFromMap($activeModifierAddress);
            $modifier->pull($fields);

            $current = $this->useModifier($base, $current, $modifier);
        }
//        $this->endResume($this->sumData($current['self'], $current['props']));

        return $current;
    }

    /**
     * @param Base|array $baseSale
     */
    public function sumComponent($baseSale)
    {
        $sum = $this->getZeroValues();

        if (is_object($baseSale) and is_numeric($baseSale->getCost())) {
            $sum['cost'] += $baseSale->getCost();
        } elseif (is_array($baseSale) and isset($baseSale['cost']) and is_numeric($baseSale['cost'])) {
            $sum['cost'] += $baseSale['cost'];
        }

        if (is_object($baseSale) and is_numeric($baseSale->getPrice())) {
            $sum['price'] += $baseSale->getPrice();
        } elseif (is_array($baseSale) and isset($baseSale['price']) and is_numeric($baseSale['price'])) {
            $sum['price'] += $baseSale['price'];
        }

        if (is_object($baseSale)) {
            $sum['cost'] *= $baseSale->getAmount();
            $sum['price'] *= $baseSale->getAmount();
        } elseif (is_array($baseSale) and !empty($baseSale['amount'])) {
            $sum['cost'] *= $baseSale['amount'];
            $sum['price'] *= $baseSale['amount'];
        }

        return $sum;
    }

    public function getZeroValues()
    {
        return [
            'cost' => 0,
            'price' => 0,
        ];
    }

    /**
     * @param Sale $sale
     */
    public function sumProperties($sale, $base = false)
    {
        $result = $this->getZeroValues();

        $props = $sale->getProperties();
        foreach ($props as $prop) {
            if ($base) {
                $propAddress = $prop->getAddress();
                $address = $prop->getAddress();
                $prop = $this->parent->getComponentFromMap($propAddress);
                $prop['address'] = $address;

            }
            $this->propCalcTrigger($prop);
            $propSum = $this->sumComponent($prop);
            $result = $this->sumData($result, $propSum);
        }

        return $result;
    }

    /**
     * @param array $term1
     * @param array $term2
     */
    public function sumData(...$terms)
    {
        $result = $this->getZeroValues();


        foreach ($terms as $term) {
            if (!empty($term['cost'])) {
                $result['cost'] += $term['cost'];
            }

            if (!empty($term['price'])) {
                $result['price'] += $term['price'];
            }
        }

        return $result;

    }

//    public function startResume($base)
//    {
//        $resume = 'Итого: ' . $base['cost'];
//
//        $this->addNoteToEnd($resume);
//    }

    /**
     * @param PriceModifier $modifier
     */
    public function useModifier($base, $current, $modifier)
    {
        $parts = $modifier->getParts();

        $different = $this->getZeroValues();

        foreach ($parts as $part) {
            $differentPart = $this->calcDifferent($base, $current, $part, $modifier);
            $different = $this->sumData($different, $differentPart);

            $current[$part] = $this->sumData($current[$part], $differentPart);
        }

        $different['name'] = $modifier->getName();
        $different['address'] = $modifier->getAddress();
        $this->endUseModifierTrigger($different);

        return $current;
    }

    /**
     * @param array $parts
     * @param PriceModifier $modifier
     */
    public function calcDifferent($base, $current, $part, $modifier)
    {
        $aims = $modifier->getAims();
        $form = $modifier->getForm();
        $direction = $modifier->getDirection();
        $different = $this->getZeroValues();
        foreach ($aims as $aim) {
            if ($form == '%1') {
                $different[$aim] += $base[$part][$aim] * ($modifier->value / 100);
            } elseif ($form == '%2') {
                $different[$aim] += $current[$part][$aim] * ($modifier->value / 100);
            } else {
                $different[$aim] += $modifier->value;
            }
        }

        if ($direction == '-') {
            foreach ($different as &$aim) {
                $aim *= -1;
            }
        }

        return $different;
    }

//    public function addModifierNote($different)
//    {
//        $term = $different['cost'] . '[' . $different['name'] . ']';
//
//        if ($different['cost'] < 0) {
//            $term = '(' . $term . ')';
//        }
//
//        $term = ' + ' . $term;
//
//        $this->addNoteToEnd($term);
//    }
//
//    public function endResume($current)
//    {
//        $resume = ' = ' . $current['cost'];
//        $this->addNoteToEnd($resume);
//    }
//
//    public function addEnd($sale)
//    {
//        $this->addNoteToEnd('|' . $sale->getName() . '}');
//    }


//    public function addNoteToBegin($note)
//    {
//        $this->story = $note . $this->story;
//    }

}