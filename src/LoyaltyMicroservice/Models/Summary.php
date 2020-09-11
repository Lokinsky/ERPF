<?php
namespace LoyaltyMicroservice\Models;


use LoyaltyMicroservice\Loyalty;
use Microservices\DataObjects\Model;
/**
 * Модель таблицы общей сводки.
 */
class Summary extends Model
{



    //protected $summaries;
    
    public $idClient;
    public $bonus;
    public $active;
    public $createdAt;



    public function __construct($from=[])
    {
        
        if(!empty($from)) $this->pull($from);
        

        
    }
  
}
