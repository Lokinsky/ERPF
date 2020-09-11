<?php
namespace ClientManagerMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use ClientManagerMicroservice\ClientManager;
use AuthenticationMicroservice\Authentication;
/**
 * Модель таблицы общей сводки.
 */
class TagedCustomers extends ArrayObject
{
    protected $id;
    protected $offset;
    protected $limit;
    protected $user;

    protected $idCustomers;
    protected $idTags;
    protected $tagedCustomers;

    public $idTag;
    public $idCustomer;

    
    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);

        
        $this->limit = $from["limit"];
        $this->offset = $from["offset"];
        
        
        
        $this->user = Authentication::getInstance()->getCurrentUser();        
        
    }
    public function create(){
        
        $q["customers"] = array(
            "res"=>ClientManager::getInstance()->db->insert("tagedcustomers",$this->getFields())
        );
        
        if($q["customers"]["res"]->errorInfo()[0]>0){
            return false;
        }else{
            return true;
        }
        
    }
    public function delete(){
        
        $q["tagedcustomer"] = array( 
            "res" => ClientManager::getInstance()->db->delete("tagedCustomers",[
                    "idTag[=]"=>$this->idTag,
                    "idCustomer[=]"=>$this->idCustomer
            ])
        );

        if($q["tagedcustomer"]["res"]->errorInfo()[0]>0){
            return false;
        }else{
            return  true;
        }
    }
    
    public function fetchTagedCustomers(){
        $q["tagedcustomer"] = array( 
            "res" => ClientManager::getInstance()->db->select("tagedCustomers",[
                "idTag",
                "idCustomer"
            ],[
                "LIMIT"=>[$this->offset,$this->limit]
            ])
        );
        if($q["tagedcustomer"]["res"]>0){
            $this->tagedCustomers = $q["tagedcustomer"]["res"];
            return  true;
            
        }else{
            return false;
        }
    }
    public function fetchAllTagsById(){
        $q["tagedcustomer"] = array( 
            "res" => ClientManager::getInstance()->db->select("tagedCustomers",[
                "idTag",
                "idCustomer"
            ],[
                "LIMIT"=>[$this->offset,$this->limit],
                "idCustomer[=]"=>$this->idCustomer
            ])
        );
        
        if($q["tagedcustomer"]["res"]>0){
            $this->tagedCustomers = $q["tagedcustomer"]["res"];
            return  true;
            
        }else{
            return false;
        }
    }


    
    public function getTagedCustomers(){
        return $this->tagedCustomers;
    }
    public function getIdTags(){
        $res = array();
        
        
        foreach ($this->tagedCustomers as $id => $tag) {
            array_push($res,$tag["idTag"]);
        }
        return $res;
    }
    public function getIdCustomers(){
        $res = array();
        
        foreach ($this->tagedCustomers as $id => $tag) {
            array_push($res,$tag["idCustomer"]);
        }
        return $res;
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
            'idTag' => ['idTag'],
            'idCustomer'=>['idCustomer'],
        ];
    }

}