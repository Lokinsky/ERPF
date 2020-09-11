<?php
namespace ContextProviderMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use ContextProviderMicroservice\ContextProvider;


class BA extends ArrayObject
{
    protected $id;
    protected $limit;
    protected $offset;

    public $name;
    public $stages;
    public $description;
    protected $bas;

    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);
        $this->limit = $from["limit"];
        $this->offset = $from["offset"];
        
    }

    public function create(){
        $q = ContextProvider::getInstance()->db->insert("ba",[
            "name"=>$this->getFields()["name"],
            "stages"=>json_encode($this->getFields()["stages"],true),
            "description"=>$this->getFields()["description"]
        ]);

        if($q->errorInfo()[0]>0)
        return  false;
        else return true;
    }

    public function edit(){
        
        $q = ContextProvider::getInstance()->db->update("ba",[
            "name"=>$this->getFields()["name"],
            "stages"=>json_encode($this->getFields()["stages"],true),
            "description"=>$this->getFields()["description"]
        ],[
            "id"=>$this->getId()
        ]);
        if($q->errorInfo()[0]>0)
        return  false;
        else return true;
    }
    public function delete(){
        $q = ContextProvider::getInstance()->db->delete("ba",[
            'AND'=>
            [
                "id[=]"=>$this->getId()
            ]
        ]);
        if($q->errorInfo()[0]>0)
        return  false;
        else return true;
    }
    /**
     * Получение полей БА вместе с этапами.
     * @return BA
     */
    public function fetchBas(){
        $this->bas = ContextProvider::getInstance()->db->select("ba","*",[
            "LIMIT"=>[$this->offset,$this->limit]
        ]);
        $stages = new Stage();
        if($stages->fetchStages())
            return false;
        
        foreach ($this->bas as $key => $value) {
              foreach (json_decode($value["stages"]) as $i =>$num) {
                foreach ($stages->getStages() as $j => $stage) {
                    if($stage["id"]==$num)
                        $this->bas[$key]["stage"][$i] = $stage;
                }
               
              }

        }
        
        
        return true;
    }
    public function getBas(){
        return $this->bas;
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
            'stages'=>['stages'],
            'description' => ['description'],
        ];
    }
}
