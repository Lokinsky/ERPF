<?php


namespace StockMicroservice;


use AuthenticationMicroservice\Authentication;
use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;
use StockMicroservice\Entities\Operation;
use StockMicroservice\Entities\Reason;
use StockMicroservice\Entities\Store;
use StockMicroservice\Models\Tags;
use StockMicroservice\Models\TagedObject;
use StockMicroservice\Models\Group;
use StockMicroservice\Models\GroupContent;

/**
 * Микросервис для управления складом
 * @package StockMicroservice
 */
class Stock extends Microservice
{

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiAddNote($question){
        $answer = new Answer();

        $store = new Store($question->getFields());
        $store->initiatorId = Authentication::getInstance()->getCurrentUser()->getId();
        if(!isset($store->ownerId)) $store->ownerId = $store->initiatorId;
        if(!Validator::validate($store,'addNote')) return $answer->genError('Error: validation failed');

        $store->setDb($this->db);
        $create = $store->create();

        if($create===false) return $answer->genError('Error: failed to add Object');

        // создание соответствующей операции
        if(!isset($question->reasonId) or !Validator::ruleInt($question->reasonId)) return $answer->genError('Error: bad Reason');
        $initiator = Authentication::getInstance()->getCurrentUser()->getId();
        $this->createOperation($question->reasonId,$initiator,$create,$question->objectId,0);

        $answer->note = $create;

        return $answer;
    }


    public function apiCreateGroup($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'createGroup')) return $answer->genError('Error: validation failed');
        
        
        $group = new Group($question->getFields());

        
        if($group->create()){
            return new Answer($group->getFields());
        }
        else{
            return $answer->genError("Error: cannot create group");
        }
        
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiPickUpNote($question){
        $answer = new Answer();
        if(!Validator::validate($question,'getNote')) return $answer->genError('Error: validation failed');

        $store = new Store($question);
        $store->setDb($this->db);

        $find = $store->get();

        if(empty($find)) return $answer->genError('Error: store note not found');

        $answer->note = $find;
        if(!$store->delete()) return $answer->genError('Error: failed to delete from store');

        if(!isset($question->reasonId) or !Validator::ruleInt($question->reasonId)) return $answer->genError('Error: bad Reason');
        $initiator = Authentication::getInstance()->getCurrentUser()->getId();
        $this->createOperation($question->reasonId,$initiator,$store->getId(),$find['objectId'],1);


        return $answer;
    }

    public function apiEditGroup($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'editGroup')) return $answer->genError('Error: validation failed');
        
        
        $group = new Group($question->getFields());

        
        if($group->edit()){
            return new Answer($group->getFields());
        }
        else{
            return $answer->genError("Error: cannot edit group");
        }
        
    }


    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditNote($question){
        $answer = new Answer();

        if(!Validator::validate($question,'editNote')) return $answer->genError('Error: failed validation');

        $store = new Store($question);
        $store->setDb($this->db);
        $edit = $store->update();

        if(!$edit) return $answer->genError('Error: failed to edit object');

        if(isset($question->ownerId)){
            if(!isset($question->reasonId) or !Validator::ruleInt($question->reasonId)) $answer->genError('Error: bad Reason');
            $initiator = Authentication::getInstance()->getCurrentUser()->getId();
            $this->createOperation($question->reasonId,$initiator,$store->getId(),$question->ownerId,2);
        }

        $answer->success = $edit;

        return $answer;
    }

    public function apiDeleteGroup($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');
        
        
        $group = new Group($question->getFields());

        
        if($group->delete()){
            return new Answer($group->getFields());
        }
        else{
            return $answer->genError("Error: cannot delete group");
        }
        
    }


    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGroupAddTag($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'addTag')) return $answer->genError('Error: validation failed');

        $group = new Group(array(
            "id"=>$question->getFields()["id"]
        ));

        $taged = new TagedObject(array(
            "idObj"=>$group->getId(),
            "idTag"=>$question->getFields()["idTag"],
            "type"=>$group->getType()
        ));

        
        if($taged->create()){
            return new Answer($group->getFields());
        }
        else{
            return $answer->genError("Error: cannot add tag to group");
        }
        
    }


    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetNote($question){
        $answer = new Answer();
        if(!Validator::validate($question,'getNote')) return $answer->genError('Error: validation failed');

        $store = new Store($question);
        $store->setDb($this->db);

        $answer->note = $store->get();

        return $answer;
    }


    public function apiGroupDeleteTag($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'deleteTag')) return $answer->genError('Error: validation failed');

        $group = new Group(array(
            "id"=>$question->getFields()["id"]
        ));

        $taged = new TagedObject(array(
            "idObj"=>$group->getId(),
            "idTag"=>$question->getFields()["idTag"],
            "type"=>$group->getType()
        ));

        
        if($taged->delete()){
            return new Answer($group->getFields());
        }
        else{
            return $answer->genError("Error: cannot add tag to group");
        }
        
    }


    /**
     * @param Question $question
     * @return Answer
     */

    public function apiGetNotes($question){
        $answer = new Answer();

        $store = new Store();
        $store->setDb($this->db);
        $answer->notes = $store->find([
            'LIMIT' => Store::createLimit($question),
        ]);

        return $answer;
    }

    public function createOperation($reasonId,$initiatorId,$storeId,$aimId,$type=0){
        $operation = new Operation([
            'reasonId' => $reasonId,
            'initiatorId' => $initiatorId,
            'storeId' => $storeId,
            'aimId' => $aimId,
            'type' => $type,
            'createdAt' => time(),
        ]);
        $operation->setDb($this->db);

        $create = $operation->create();

        return $create;
    }


    public function apiGetGroups($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
        
        
        $group = new Group($question->getFields());

        
        if($group->get()){

            return new Answer($group->getGroups());
        }
        else{
            return $answer->genError("Error: cannot get groups");
        }
        
    }


    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetGroupsByTag($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'getByTag')) return $answer->genError('Error: validation failed');
        
        
        $group = new Group();
       
        $taged = new TagedObject(array(
            "idTag"=>!empty($question->getFields()["idTag"])?$question->getFields()["idTag"]:-1,
            "type"=>$group->getType()
        ));

        if($taged->get()){
            if($group->getMulti($taged->getTagedObjects())){
                return new Answer($group->getGroups());
            }
            else{
                return $answer->genError("Error: cannot get groups by tag");
            }
        }
        else{
            return $answer->genError("Error: cannot get groups by tag (tag is not existing)");
        }
        
    }


    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetOperations($question){
        $answer = new Answer();
        $where = [
            'LIMIT' => Operation::createLimit($question),
        ];

        $operation = new Operation();
        $operation->setDb($this->db);
        $answer->operations = $operation->find($where);

        return $answer;
    }


    public function apiCreateGroupContent($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'createGroupContent')) return $answer->genError('Error: validation failed');
        
        
        $groupContent = new GroupContent($question->getFields());

        
        if($groupContent->create()){
            return new Answer($groupContent->getFields());
        }
        else{
            return $answer->genError("Error: cannot create GroupContent");
        }
    }


    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCreateReason($question){
        $answer = new Answer();

        if(!Validator::validate($question,'createReason')) return $answer->genError('Error: validation failed');

        $reason = new Reason($question->getFields());
        $reason->setDb($this->db);
        $create = $reason->create();
        if(empty($create)) return $answer->genError('Error: failed to create Reason');

        $answer->id = $create;

        return $answer;
    }


    public function apiEditGroupContent($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'editGroupContent')) return $answer->genError('Error: validation failed');
        
        
        $groupContent = new GroupContent($question->getFields());

        
        if($groupContent->edit()){
            return new Answer($groupContent->getFields());
        }
        else{
            return $answer->genError("Error: cannot edit GroupContent");
        }
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditReason($question){
        $answer = new Answer();

        if(!Validator::validate($question,'editReason')) return $answer->genError('Error: validation failed');

        $reason = new Reason($question->getFields());
        $reason->setDb($this->db);
        $update = $reason->update();

        if(!$update) return $answer->genError('Error: failed to edit reason');
        $answer->success = $update;

        return $answer;
    }


    public function apiDeleteGroupContent($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'createGroup')) return $answer->genError('Error: validation failed');
        
        
        $groupContent = new GroupContent($question->getFields());

        
        if($groupContent->delete()){
            return new Answer($groupContent->getFields());
        }
        else{
            return $answer->genError("Error: cannot delete GroupContent");
        }
        
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteReason($question){
        $answer = new Answer();

        if(!Validator::validate($question,'deleteReason')) return $answer->genError('Error: validation failed');

        $reason = new Reason($question->getFields());
        $reason->setDb($this->db);
        $delete = $reason->delete();

        if(!$delete) return $answer->genError('Error: failed to delete Reason');

        $answer->success = $delete;

        return $answer;
    }

    public function apiGetGroupContent($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
        
        
        $groupContent = new GroupContent($question->getFields());

        
        if($groupContent->get()){

            return new Answer($groupContent->getGroupContents());
        }
        else{
            return $answer->genError("Error: cannot get groupContent");
        }
        
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetReason($question){
        $answer = new Answer();

        if(!Validator::validate($question,'getReason')) return $answer->genError('Error: validation failed');
        $reason = new Reason($question->getFields());
        $reason->setDb($this->db);

        $found = $reason->get();

        if(empty($found)) return $answer->genError('Error: failed to find Reason');

        $answer->reason = $found;

        return $answer;
    }

    public function apiCreateTag($question){
        $answer = new Answer();

        if(!Validator::validate($question,'create')) return $answer->genError('Error: validation failed');
        $tags = new Tags($question->getFields());
        if($tags->delete()){
            return new Answer($tags->getFields());
        }else{
            return $answer->genError("Error: cannot create tag");
        }
        
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetReasons($question)
    {
        $answer = new Answer();

        $where = [
            'LIMIT' => Reason::createLimit($question),
        ];

        $reason = new Reason();
        $reason->setDb($this->db);
        $get = $reason->find($where);

        $answer->reasons = $get;

        return $answer;
    }

    public function apiEditTag($question){
        $answer = new Answer();
        if(!Validator::validate($question,'editTag')) return $answer->genError('Error: validation failed');
        
        
        $tags = new Tags($question->getFields());

        
        if($tags->edit()){
            return new Answer($tags->getFields());
        }
        else{
            return $answer->genError("Error: fallen trying to edit");
        }
        
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteTag($question){
        $answer = new Answer();
  
        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');
        
        $tags = new Tags($question->getFields());

        
        if($tags->delete()){
            return new Answer($tags->getFields());
        }
        else{
            return $answer->genError("Error: oops, u cant or i cant delete this tag");
        }
        
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetTags($question){
        $answer = new Answer();
  
        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
        
        $tags = new Tags($question->getFields());

        
        if($tags->get()){
            return new Answer($tags->getTags());
        }
        else{
            return $answer->genError("Error: take some drink if you didnt fetched data)");
        }

    }
}