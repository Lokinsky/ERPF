<?php
namespace ClientManagerMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use ClientManagerMicroservice\ClientManager;
use AuthenticationMicroservice\Authentication;
//use ClientManagerMicroservice\Models\Customer;
/**
 * Модель таблицы общей сводки.
 */
class Tags extends ArrayObject
{
    protected $id;
    protected $offset;
    protected $limit;
    protected $user;

    protected $tagsCustomer;
    protected $idCustomer;
    protected $tags;

    public $name;
    public $expires;

    


    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);

        
        $this->limit = $from["limit"];
        $this->offset = $from["offset"];
        
        $this->idCustomer = $from["idCustomer"];
        $this->tagsCustomer = $from["tagsCustomer"];
        
        $this->user = Authentication::getInstance()->getCurrentUser();
        
        
    }
    public function create(){
        
        $q["customers"] = array(
            "res"=>ClientManager::getInstance()->db->insert("tags",$this->getFields())
        );
        
        if($q["customers"]["res"]->errorInfo()[0]>0){
            return false;
        }else{
            return  true;
        }
        
    }
    public function delete(){
        $q["tags"] = array(
            "res"=>ClientManager::getInstance()->db->delete("tags",[
                "id[=]"=>$this->getId()
            ]),
        );
        $tagedCustomer = new TagedCustomers(array(
            "idTag"=>$this->getId()
        ));

        $q["tagedcustomer"] = array( 
            "res" => $tagedCustomer->delete()
        );

        if($q["tags"]["res"]->errorInfo()[0]>0||!$q["tagedcustomer"]["res"]){
            return false;
        }else{
            return true;
        }
       
        
        
    }
    public function edit(){
        
        $q["tags"] = array(
            "res"=>ClientManager::getInstance()->db->update("tags",$this->getFields(),
            ["id[=]"=>$this->getId()])
        );
        
        if($q["tags"]["res"]->errorInfo()[0]>0){
            return false;
        }else{
            return true;
        }
        
    }
    public function setTag()
    {
        $q = array();
        $tagedCustomer = new TagedCustomers(array(
            "idTag"=>$this->getId(),
            "idCustomer"=>$this->idCustomer
        ));
        
        
        $q["tags"] = array(
            "res"=>$tagedCustomer->create()
        );
        if($q["tags"]["res"]||empty($q)){
            return false;
        }else{
            return  true;
        }
    }

    public function fetchAllTagsById()
    {
        $q = array();
        $q = ClientManager::getInstance()->db->get("tags","*",[
            "id[=]"=>$this->getTagsCustomer(),
        ]);
        if(!empty($q)) {
            $this->tags = $q;
            return true;
        }
        else return false;

    }
    /**
     * "Пуллинг" тегов
     * @return bool 
     */
    public function fetchTags(){
        
        $q = "";
        $res = array();
        if($this->getId()!=null&&$this->getId()==0) return false;

        if(!empty($this->getId()) and $this->getId()>0){
            

            
            $q = ClientManager::getInstance()->db->get("tags","*",[
                    "id[=]"=>$this->getId(),
            ]);

            
            if($q==null){
                return false;
            }
            $res = array([
                "name"=>$q["name"],
                "expires"=>$q["expires"],
                "id"=>$q["id"],
            ]);
            
        }else{

            $q = ClientManager::getInstance()->db->select("tags","*",[
                "LIMIT"=>[$this->offset,$this->limit],
                "id[=]"=>$this->tagsCustomer
            ]);
            
            $res = $q;
        }
        
        if($res==null){
            return false;
        }else{
            $this->tags = $res;
            return true;
        }
        
    }
    public function getTag(){
        return $this->tags;
    }
    /**
     * Получение тегов ранее выбранного клиента
     * @return mixed 
     */
    public function getTagsCustomer()
    {
        return $this->tagsCustomer;    
    }

    public function fetchTagsCustomers(){
        $q = "";
        $res = array();

        $q = ClientManager::getInstance()->db->select("tags","*",[
            "LIMIT"=>[$this->offset,$this->limit],
            "id[=]"=>$this->tagsCustomer
            
        ]);
        
        $res = $q;
                
        if($res==null){
            return false;
        }else{
            $this->tags = $res;
            return true;
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
            'name' => ['name'],
            'expires'=>['expires'],
        ];
    }
}
