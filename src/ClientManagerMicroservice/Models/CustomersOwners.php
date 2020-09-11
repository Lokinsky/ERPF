<?php
namespace ClientManagerMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use ClientManagerMicroservice\ClientManager;
use AuthenticationMicroservice\Authentication;
/**
 * Модель таблицы общей сводки.
 */
class CustomersOwners extends ArrayObject
{
    protected $id;
    protected $offset;
    protected $limit;
    protected $user;

    protected $idCustomers;

    public $idOwner;
    public $idCustomer;
   

    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);

        
        $this->limit = $from["limit"];
        $this->offset = $from["offset"];
        
        $this->user = Authentication::getInstance()->getCurrentUser();   
        $this->fetchCustomersOwner();
             
        
    }
    public function create(){
        
        $q["customers"] = array(
            "res"=>ClientManager::getInstance()->db->insert("customersowners",$this->getFields())
        );
        
        if($q["customers"]["res"]->errorInfo()[0]>0){
            return false;
        }else{
            return  true;
        }
        
    }
    public function delete(){
        
        $q["customersowners"] = array( 
            "res" => ClientManager::getInstance()->db->delete("customersowners",[
                "idOwner"=>$this->idOwner,
                "idCustomer[=]"=>$this->idCustomer
            ])
        );

        if($q["customersowners"]["res"]->errorInfo()[0]>0){
            return false;
        }else{
            return  true;
        }
    }

    public function edit(){
        
        $q["customersowners"] = array(
            "res"=>ClientManager::getInstance()->db->update("customersowners",$this->getFields(), [ "id[=]" => $this->getId() ] )
        );
        
        if($q["customersowners"]["res"]->errorInfo()[0]>0){
            return false;
        }else{
            return  true;
        }
        
    }

    public function fetchCustomersOwner(){
        
        $q["customersowners"] = array(
            "res"=>ClientManager::getInstance()->db->select("customersowners","idCustomer", 
            [ "idOwner[=]" => $this->user->getId() ] )
        );

        
        if(empty($q["customersowners"]["res"])){
            return false;
        }else{
            $this->idCustomers = $q["customersowners"]["res"];
            return  true;
        } 
    }
    
    public function getIdCustomers()
    {
        return $this->idCustomers;
    }

    

    public function isOwn($id=null)
    {
        if(in_array(!empty($id) ? $id:$this->idCustomer, $this->idCustomers)!=false){
            
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    public function getFieldsAliases()
    {
        return[

            'id'=>['id'],
            'idOwner' => ['idOwner'],
            'idCustomer'=>['idCustomer'],
        ];
    }
}