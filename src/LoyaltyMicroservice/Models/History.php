<?php
namespace LoyaltyMicroservice\Models;


use LoyaltyMicroservice\Loyalty;
use Microservices\DataObjects\Model;
/**
 * Модель таблицы общей сводки.
 */
class History extends Model
{



    //protected $summaries;
    
    public $idClient;
    public $commonPrice;
    public $idOrder;
    public $createdAt;



    public function __construct($from=[])
    {
        
        if(!empty($from)) $this->pull($from);
        

        
    }
  public function getCount($where=[])
  {
    
    if(empty($where))
        $where = [
            "iClient"=>$this->idClient
        ];
    
    $count = Loyalty::getInstance()->db->count("histories",$where);
    if(!empty($count)) return $count;
    else return false;
  }
}
