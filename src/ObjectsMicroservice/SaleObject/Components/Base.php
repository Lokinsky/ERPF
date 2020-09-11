<?php


namespace ObjectsMicroservice\SaleObject\Components;


use Microservices\DataObjects\ArrayObject;
use Microservices\DataObjects\Model;
use ObjectsMicroservice\Objects;
use ObjectsMicroservice\SaleObject\AddressedI;

class Base extends Model implements AddressedI
{
    public $id;

//    public $name;
//    public $description;
//
//    public $createdAt;
//    public $deletedAt;
//    public $version;

    public function __construct($compressed = true)
    {
        if (!$compressed) {
            $this->decompress();
        }
    }

//    public function create(){
//        $this->createdAt = time();
//        $data = $this->getFields();
//        $table = static::getTableName();
//
//        $create = Objects::getInstance()->db->insert($table,$data);
//        if($create->rowCount()>0) return Objects::getInstance()->db->id();
//
//        return false;
//    }
//
//    public function update(){
//        $data = $this->getFields();
//        $table = static::getTableName();
//
//        $update = Objects::getInstance()->db->update($table,$data);
//        if($update->rowCount()>0) return true;
//
//        return false;
//    }
//
//    public function get($id=false){
//        $table = static::getTableName();
//        if($id===false){
//            if(!empty($this->id)){
//                $id = $this->id;
//            }
//        }
//
//        if(!empty($id)){
//            return Objects::getInstance()->db->get($table,'*',['id'=>$id]);
//        }
//
//        return false;
//    }
//
//    public function delete($id=false){
//        $table = static::getTableName();
//        if($id===false){
//            if(!empty($this->id)){
//                $id = $this->id;
//            }
//        }
//
//        if(!empty($id)){
//            $delete = Objects::getInstance()->db->delete($table,['id'=>$id]);
//            if($delete->rowCount()>0) return true;
//        }
//
//        return false;
//    }

    public function selfLoad(){
        $fields = $this->get();
        $this->pull($fields);
    }

    public function getFields($clearNullFlag=null)
    {
        $fieldNames = $this->getFieldNames();
        $data = [];

        foreach ($fieldNames as $fieldName){
            if(isset($this->$fieldName)){
                $data[$fieldName] = $this->$fieldName;
            }
        }

        return $data;
    }

    public function decompress()
    {
        $fields = $this->getFieldNames();
        foreach ($fields as $field) {
            if (!isset($this->$field)) $this->$field = null;
        }
    }

    public function getFieldNames()
    {
        return [
            'id',
            'name',
            'description',
            'createdAt',
            'deletedAt',
            'version',
        ];
    }


    public function compress()
    {
        $fields = $this->getFieldNames();
        foreach ($fields as $field) {
            if (isset($this->$field) and is_null($this->$field)) unset($this->$field);
        }
    }

    public function getName(){
        if(empty($this->name)) return 'Unnamed';

        return $this->name;
    }


    public function getAddress()
    {
        if (is_null($this->id)) {
            $this->id = 0;
        }
        return $this->id . $this->getTypeNumber();
    }

//    public static function getTableName(){
//    }

    public function getTypeNumber()
    {
    }
}