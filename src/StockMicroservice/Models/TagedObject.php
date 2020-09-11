<?php
namespace StockMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use StockMicroservice\Stock;
use AuthenticationMicroservice\Authentication;

class TagedObject extends ArrayObject
{
    protected $id;
    protected $offset;
    protected $limit;
    protected $user;

    protected $lastId;
    protected $tagedObjects;

    public $idObj;
    public $idTag;
    public $type;
    public $createdAt;
    

    


    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);

        
        $this->limit = $from["limit"];
        $this->offset = $from["offset"];
        
        $this->user = Authentication::getInstance()->getCurrentUser();
        //$this->tags = ClientManager::getInstance()->db->select("tags","*",".");
        
        
    }
    public function create()
    {
        
        $q = Stock::getInstance()->db->insert("tagedobject",
            [
                "idObj"=>$this->idObj,
                "idTag"=>$this->idTag,
                "type"=>$this->type,
                "createdAt"=>time()
            ]
        );
        
        if(empty($q)) return false;
        else return true;
        

    }

    public function delete($where = [])
    {   
        if(empty($where))
            $where = array("AND"=>[
                "idObj[=]"=>$this->idObj,
                "idTag[=]"=>$this->idTag,
                "type[=]"=>$this->type,

            ]);
        
        $q = Stock::getInstance()->db->delete("tagedobject",$where);
        if(empty($q)) return false;
        else return true;   
    }
    public function get($params="idObj")
    {
        
        if(!empty($this->idTag))
            $q = Stock::getInstance()->db->select("tagedobject",$params,
                    [
                        "AND"=>[
                            "idTag[=]"=>$this->idTag,
                            "type[=]"=>$this->type
                        ],
                        "LIMIT"=>[$this->offset,$this->limit]
                    ]
                );
        else
            $q = Stock::getInstance()->db->select("tagedobject",$params,
                [
                    "AND"=>[
                        "type[=]"=>$this->type
                    ],
                    "LIMIT"=>[$this->offset,$this->limit]
                ]
            );
                
        
        if(empty($q)) return false;
        else {
            $this->tagedObjects = $q;
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
    public function getTagedObjects()
    {
        
        return $this->tagedObjects;
        
    }
    public function getFieldsAliases()
    {
        return[

            'id'=>['id'],
            'idObj' => ['idObj'],
            'idTag' => ['idTag'],
            'type'=>['type'],
            'createdAt '=>['createdAt']
        ];
    }
}