<?php
namespace ClientManagerMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use ClientManagerMicroservice\ClientManager;
use AuthenticationMicroservice\Authentication;
/**
 * Модель таблицы Client.
 */
class Customer extends ArrayObject
{
    protected $id;
    protected $offset;
    protected $limit;
    protected $user;

    protected $idTag;
    protected $customers;

    public $name;
    public $description;
    public $contact;
    public $status;
    


    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);

        
       
        $this->limit = $from["limit"];
        $this->offset = $from["offset"];

        $this->idTag = $from["tag"];
        $this->user = Authentication::getInstance()->getCurrentUser();
        
        
    }
    public function create(){
        
        $q["customers"] = array(
            "res"=>ClientManager::getInstance()->db->insert("customers",$this->getFields()),
            "lastId"=>ClientManager::getInstance()->db->id()
        );
        $cu = new CustomersOwners(array(
            "idOwner"=>$this->user->getId(),
            "idCustomer"=>$q["customers"]["lastId"]
        ));
        $q["customersowners"] = $cu->create();
        if($q["customers"]["res"]->errorInfo()[0]>0||!$q["customersowners"]){
            return false;
        }else{
            return  true;
        }
        
    }
    /**
     * Удаление с проверкой на связь клиента и менеджера
     */
    public function delete(){

        $cu = new CustomersOwners(array(
            "idCustomer"=>$this->getId(),
            "idOwner"=>$this->user->getId(),
        ));

        if($cu->isOwn()){

            $q["customers"] = array(
                "res"=>ClientManager::getInstance()->db->delete("customers",[
                    "id[=]"=>$this->getId()
                ]),
            );
            
            $taged = new TagedCustomers(array(
                "idCustomer"=>$this->getId()
            ));

            $q["customersowners"] = $cu->delete();
            $q["tagedcustomers"] = $taged->delete();


            if($q["customers"]["res"]->errorInfo()[0]>0){
                return false;
            }else{
                return  true;
            }
        }else{
            return false;
        }
        
        
    }
    public function edit(){

        $cu = new CustomersOwners(array(
            'idCustomer'=>$this->getId(),
            "idOwner"=>$this->user->getId()
        ));

        if($cu->isOwn())
            $q["customers"] = array(
                "res"=>ClientManager::getInstance()->db->update("customers",$this->getFields(),
                ["id[=]"=>$this->getId()])
            );
        
        if(empty($q["customers"]["res"])){
            return false;
        }else{
            return  true;
        }
        
    }
    /**
     * "Пуллинг" клиентов с учетом связи менеджр->клиент, а также
     * слияние с тегами, если они указаны в зависимостях
     * 
     * @Request
     * ClientManager->GetCustomers
     * input var: id==null:ALL?SINGLE
     * 
     */
    function fetchCustomers()
    {
        $res = array();
        $taged = new TagedCustomers();
        $cu = new CustomersOwners();
        $cu->fetchCustomersOwner();

        if(!$cu->isOwn($this->getId())) return false;

        $q = ClientManager::getInstance()->db->select("customers","*",[
            "id[=]"=>!empty($this->getId())?$this->getId():$cu->getIdCustomers(),
            "LIMIT"=>[$this->offset,$this->limit]
        ]);

       if($taged->fetchTagedCustomers()){
            $tags = new Tags(array(
                "tagsCustomer"=>$taged->getIdTags()
            ));
            if($tags->fetchTags()){

                foreach ($q as $num => $customer) {
                    $res[$num] = $customer;
                    foreach ($taged->getTagedCustomers() as $iTaged => $tagedCustomer) {
                        
                        if($customer["id"]==$tagedCustomer["idCustomer"]){
                            if(empty($res[$num]["tags"]))
                                $res[$num]["tags"] = array();
                            foreach ($tags->getTag() as $iTag => $tag) {
                                if($tagedCustomer["idTag"]==$tag["id"])
                                    array_push($res[$num]["tags"],$tag);
                            }
                            
                            
                        }
                        
                    }
                    
                }
                
            }

        }
        if(!empty($res)){
            $this->customers = $res;
            return true;
        }else{
            return false;
        }
        
    }
    
    public function getCustomer(){

        return empty($this->customers)?[]:$this->customers;
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
            'name' => ['name'],
            'contact'=>['contact'],
            'description' => ['description','descr'],
            'status'=>['status'],

        ];
    }
}
