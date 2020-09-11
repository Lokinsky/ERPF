<?php


namespace LoyaltyMicroservice\Entities;


use CalculationsMicroservice\Entities\SaleCalculator;
use Microservices\DataObjects\Model;
use ObjectsMicroservice\SaleObject\Sale;

class Condition extends Model
{
    /**
     * @param array $components
     * @param Sale $sale
     * @return boolean
     */
    public function componentsCounted($components,$sale){
        $sale->countAllComponents();
        $counters = $sale->getComponentsCounters();
        $saleCounters = [];

        foreach ($counters as $lvl=>$components){
            if(empty($components)) $components = [];

            foreach ($components as $address=>$counted){
                if(!isset($saleCounters[$address])) $saleCounters[$address] = 0;
                $saleCounters[$address] += $counted;
            }
        }

        foreach ($components as $address=>$counted){
            if(isset($saleCounters[$address]) and $saleCounters[$address]==$counted) continue;
            return false;
        }

        return true;
    }

    /**
     * @param array $lvl
     * @param Sale $sale
     */
    public function lvlCounted($lvl,$sale){
        $sale->countAllComponents();
        $counted = $sale->getComponentsCounters();
        if(isset($counted[$lvl['lvl']])){
            $amount = 0;
            foreach ($counted[$lvl['lvl']] as $counted){
                $amount += $counted;
            }

            if($amount==$lvl['amount']) return true;
        }

        return false;
    }

    public function costRange($range,$sale){
        $calculator = new SaleCalculator();
        $result = $calculator->calc($sale);
        $cost = $result['cost']['value'];
        if($cost>=$range[0] and  $cost<=$range[1]) return true;

        return false;
    }

    public function dateRange($range){
        $now = time();

        return $range[0] >= $now and $now <= $range[1];
    }

    public function checkWeekDays($trueDays){
        $day = date('w',time());

        return in_array($day,$trueDays);
    }

    public function checkMonths($trueMonths){
        $month = date('n',time());
        return in_array($month,$trueMonths);
    }

    public function checkTime($timeRange){
        $time = time();
        $now = 0;
        $now += date('G',$time)*60*60;
        $now += date('i',$time)*60;
        $now += date('s',$time);

        return $timeRange[0] <= $now and $now<= $timeRange[1];
    }
}