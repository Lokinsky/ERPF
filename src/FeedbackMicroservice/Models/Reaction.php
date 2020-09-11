<?php


namespace FeedbackMicroservice\Models;


use Microservices\DataObjects\Model;

class Reaction extends Model
{
    public $userId;
    public $objectId;
    public $emojiId;
    public $type;
    public $createdAt;

    public function __construct($fields=[])
    {
        if(!empty($fields)) $this->pull($fields);
    }
    public function createReaction()
    {
        $isReact = $this->exists([
            "AND"=>[
                "objectId"=>$this->objectId,
                "userId"=>$this->userId
            ]
        ]);
        if($isReact) return false;
        else{
            $create = $this->create();
            return $create;
        }
    }
    public function editReaction()
    {
        $createdTime = $this->get([
            "AND"=>[
                "userId "=>$this->userId,
                "objectId"=>$this->objectId
            ]
            ],"createdAt");

        if((time()-$createdTime)<=86400){
            return $this->update();
        }
    }
    

}