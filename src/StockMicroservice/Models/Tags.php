<?php
namespace StockMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use StockMicroservice\Stock;
use AuthenticationMicroservice\Authentication;

class Tags extends ArrayObject
{
    protected $id;
    protected $offset;
    protected $limit;
    protected $user;

    protected $lastId;
    protected $tags;

    public $name;
    public $address;
    public $expires;
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
        $q = Stock::getInstance()->db->insert("tags",
            [
                "name"=>$this->name,
                "address"=>$this->address,
                "expires"=>$this->expires,
                "createdAt"=>time()
            ]
        );
        if(empty($q)) return false;
        else return true;
        

    }
    public function edit()
    {
        $q = Stock::getInstance()->db->update("tags",
            [
                "name"=>$this->name,
                "address"=>$this->address,
                "expires"=>$this->expires,
                "createdAt"=>time()
            ],
            [
                "id[=]"=>$this->getId()
            ]
        );
        if($q->errorInfo()[0]>0) return false;
        else return true;
    }
    public function delete()
    {
        $q = Stock::getInstance()->db->delete("tags",[
            "AND"=>[
                "id[=]"=>$this->getId()
            ]
        ]);
        $taged = new TagedObject();

        if(empty($q)) return false;
        else 
        {
            $where = array("AND"=>[
                "idTag[=]"=>$this->getId()
            ]);
            if($taged->delete($where))
                return true;
            else
                return false;
        }   
    }
    public function get()
    {
        $q  = "";
        if(!empty($this->id))
            $q = Stock::getInstance()->db->select("tags","*",
                [
                    "id[=]"=>$this->id,
                    "LIMIT"=>[$this->offset,$this->limit]
                ]
            );
        else{
            $q = Stock::getInstance()->db->select("tags","*",
                [
                    "LIMIT"=>[$this->offset,$this->limit]
                ]
            );
        }
        if(empty($q)) return false;
        else {
            $this->tags = $q;
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
    public function getTags()
    {
        return $this->tags;
        
    }
    public function getFieldsAliases()
    {
        return[

            'id'=>['id'],
            'name' => ['name'],
            'address'=>['address'],
            'expires'=>['expires'],
            'createdAt '=>['createdAt']
        ];
    }
}