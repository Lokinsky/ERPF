<?php
namespace StockMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use StockMicroservice\Stock;
use AuthenticationMicroservice\Authentication;

class Group extends ArrayObject
{
    protected $id;
    protected $offset;
    protected $limit;
    protected $user;

    protected $lastId;
    protected $groups;
    
    protected $idTag;

    public $name;
    public $address;
    public $createdAt;

    


    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);

        
        $this->limit = $from["limit"];
        $this->offset = $from["offset"];
        
        $this->idTag = $from["idTag"];

        $this->user = Authentication::getInstance()->getCurrentUser();
        //$this->tags = ClientManager::getInstance()->db->select("tags","*",".");
        
        
    }
    public function create()
    {
        $q = Stock::getInstance()->db->insert("groups",
            [
                "name"=>$this->name,
                "address"=>$this->address,
                "createdAt"=>time()
            ]
        );
        if(empty($q)) return false;
        else return true;
        

    }
    public function edit()
    {
        $q = Stock::getInstance()->db->update("groups",
            [
                "name"=>$this->name,
                "address"=>$this->address,
                "createdAt"=>time()
            ],
            [
                "id[=]"=>$this->getId()
            ]
        );
        if(empty($q)) return false;
        else return true;
    }
    public function delete()
    {
        $q = Stock::getInstance()->db->delete("groups",[
            "AND"=>[
                "id[=]"=>$this->getId()
            ]
        ]);
        if(empty($q)) return false;
        else return true;   
    }

   

    public function get()
    {
        if(!empty($this->id))
            $q = Stock::getInstance()->db->select("groups","*",
                [
                    "id[=]"=>$this->id,
                    "LIMIT"=>[$this->offset,$this->limit]
                ]
            );
        else{
            $q = Stock::getInstance()->db->select("groups","*",
                [
                    "LIMIT"=>[$this->offset,$this->limit]
                ]
            );
        }

        if(empty($q)) return false;
        else {
            $this->groups = $q;
            return true;
        }   
        
    }
    /**
     * Множественная выборка
     */
    public function getMulti($ids=null)
    {
        $q = Stock::getInstance()->db->select("groups","*",
                [
                    "id[=]"=>$ids,
                    "LIMIT"=>[$this->offset,$this->limit]
                ]
            );
        if(empty($q)) return false;
        else {
            $this->groups = $q;
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
    /**
     * @return mixed
     */
    public function getType()
    {
        return 0;
    }
    /**
     * @return mixed
     * 
     * Слияние групп с тегами (если они для этих групп указаны)
     */
    public function getGroups()
    {
        $res = array();
        $taged = new TagedObject(array(
            "type"=>$this->getType()
        ));
        if($taged->get('*')){
            $tags = new Tags(array());
            if($tags->get()){
                $tagedO = $taged->getTagedObjects();
                foreach ($this->groups as $iGroup => $group) {
                    $res[$iGroup] = $group;
                    $tagsf = array();
                    foreach ($tagedO as $iTaged => $taged) {
                        if($taged["idObj"]==$group["id"]){
                            foreach ($tags->getTags() as $iTag => $tag) {
                                if($tag["id"]==$taged["idTag"]){
                                    array_push($tagsf,$tag);
                                }
                            }
                        }
                    }
                    
                    if(!empty($tagsf)) $res[$iGroup]["tags"] = $tagsf;
                    
                }
            }
        }
        
        
        return $res;
    }
  
    public function getFieldsAliases()
    {
        return[

            'id'=>['id'],
            'name' => ['name'],
            'address'=>['address'],
            'createdAt '=>['createdAt']
        ];
    }
}