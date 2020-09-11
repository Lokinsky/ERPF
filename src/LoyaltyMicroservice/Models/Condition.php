<?php
namespace LoyaltyMicroservice\Models;


use CalculationsMicroservice\Entities\SaleCalculator;
use Microservices\DataObjects\Model;
use ObjectsMicroservice\SaleObject\Sale;

/**
 * Модель таблицы условий.
 */
class Condition extends Model
{



    protected $idClient;
    
    public $name;
    public $description;
    public $type;
    public $value;
    public $duration;
    public $createdAt;

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


    public function __construct($from=[])
    {
        
        if(!empty($from)) $this->pull($from);
        
    }

    function date($params)
    {
        return $params[0]<time()&&time()<$params[1];
    }
    function day($params)
    {
        return strftime("%u",time())==strftime("%u",$params[0]);
    }
    function month($params)
    {
        return strftime("%m",time())==$params[0];
    }
    function time($params)
    {
        return $params[0]<date("H")&&date("H")<$params[1];
    }
    function orderBy($params)
    {
        $historyClient = new History([
            "idClient"=>$this->idClient
        ]);

        $orderBy = $historyClient->getCount();

        return $orderBy+1 == $params[0];
    }
    function multiplicity($params)
    {
        $historyClient = new History([
            "idClient"=>$this->idClient
        ]);

        $orderBy = $historyClient->getCount();

        return $orderBy % $params[0] == 0;
    }
    function countOrdersByDate($params)
    {
        $historyClient = new History([
            "idClient"=>$this->idClient
        ]);

        $orderByDate = $historyClient->getCount([
            "idClient"=>$this->idClient,
            "AND"=>[
                "createdAt[>]" => $params[0],
                "createdAt[<]" => $params[1],
            ]
        ]);

        return $orderByDate == $params[2];
    }
    function countSumByDate($params)
    {
        $historyClient = new History([
            "idClient"=>$this->idClient
        ]);

        $orderSumByDate = $historyClient->getCount([
            "idClient"=>$this->idClient,
            "AND"=>[
                "createdAt[>]" => $params[0],
                "createdAt[<]" => $params[1],
            ]
        ]);
        $sum = 0;
        foreach ($orderSumByDate as $i => $order) {
            $sum+=$order["commonPrice"];
        }
        return $sum == $params[2];
    }

    public function check($sale)
    {
        $params = json_decode($this->value,true);
        switch ($this->type) {
            case 1:
//                return $this->date($params);
                return $this->dateRange($params);
                break;
            case 2:
//                return $this->day($params);
                return $this->checkWeekDays($params);
                break;
            case 3:
//                return $this->month($params);
                return $this->month($params);
                break;
            case 4:
//                return $this->time($params);
                return $this->checkTime($params);
                break;
            case 5:
                return $this->componentsCounted($params,$sale);
                break;
            case 6:
                return $this->lvlCounted($params,$sale);
                break;
            case 7:
                return $this->costRange($params,$sale);
                break;
            case 8:
                return $this->multiplicity($params);
                break;
            case 9:
                return $this->countOrdersByDate($params);
                break;
            case 10:
                return $this->countSumByDate($params);
                break;
            case 11:
                return $this->orderBy($params);
                break;
            default:
                return  false;
                break;
        }
    }
    public function addIdClient($idClient)
    {
        $this->idClient = $idClient;
    }

  
}
