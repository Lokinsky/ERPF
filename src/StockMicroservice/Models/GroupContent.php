<?php
namespace StockMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use StockMicroservice\Stock;
use AuthenticationMicroservice\Authentication;

class GroupContent extends ArrayObject
{
    protected $id;
    protected $offset;
    protected $limit;
    protected $user;

    protected $lastId;
    protected $groupContents;
    protected $idTag;

    public $idGroup;
    public $idObject;
    public $counts;
    public $date;
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
        $q = Stock::getInstance()->db->insert("groupcontent",
            [
                "idGroup"=>$this->idGroup,
                "idObject"=>$this->idObject,
                "createdAt"=>time()
            ]
        );
        if(empty($q)) return false;
        else return true;
        

    }
    public function edit()
    {
        $q = Stock::getInstance()->db->update("groupcontent",
            [
                "idGroup"=>$this->idGroup,
                "idObject"=>$this->idObject,
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
        $q = Stock::getInstance()->db->delete("groupcontent",[
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
            $q = Stock::getInstance()->db->select("groupcontent","*",
                [
                    "id[=]"=>$this->id,
                    "LIMIT"=>[$this->offset,$this->limit]
                ]
            );
        else{
            $q = Stock::getInstance()->db->select("groupcontent","*",
                [
                    "LIMIT"=>[$this->offset,$this->limit]
                ]
            );
        }
        if(empty($q)) return false;
        else {
            $this->groupContents = $q;
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
    public function getGroupContents()
    {
        

        return $this->groupContents;
        
        
    }

    public function getFieldsAliases()
    {
        return[

            'id'=>['id'],
            'idGroup' => ['idGroup'],
            'idObject'=>['idObject'],
            'createdAt '=>['createdAt']
        ];
    }
}