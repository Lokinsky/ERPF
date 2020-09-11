<?php
namespace ContextProviderMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use ContextProviderMicroservice\ContextProvider;


class Context extends ArrayObject
{
    protected $id;
    protected $limit;
    protected $offset;

    public $name;
    public $stages;
    public $description;
    protected $contexts;

    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);
        $this->limit = $from["limit"];
        $this->offset = $from["offset"];
    }

    public function create(){
        
        $q=ContextProvider::getInstance()->db->insert("contexts",[
            "name"=>$this->getFields()["name"],
            "stages"=>json_encode($this->getFields()["stages"],true),
            "description"=>$this->getFields()["description"]
        ]);
        
        if($q->errorInfo()[0]>0)
        return  false;
        else return true;
    }

    public function edit(){
        
        $q=ContextProvider::getInstance()->db->update("contexts",[
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
        $q=ContextProvider::getInstance()->db->delete("contexts",[
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
     * Получение полей контекстов и слияние их вместе с этапами.
     * @return Context
     */
    public function fetchContexts(){
        $this->contexts = ContextProvider::getInstance()->db->select("contexts","*",[
            "LIMIT"=>[$this->offset,$this->limit]
        ]);

        $stages = new Stage();
        if($stages->fetchStages())
            return false;
        foreach ($this->contexts as $key => $value) {
            if(!empty(json_decode($value["stages"])))
              foreach (json_decode($value["stages"]) as $i =>$num) {
                  foreach ($stages->getStages() as $j => $stage) {
                      if($stage["id"]==$num)
                        $this->contexts[$key]["stage"][$i] = $stage;

                  }
              }

        }
        
        
        return $this;
    }
    public function getContexts(){
        return $this->contexts;
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
