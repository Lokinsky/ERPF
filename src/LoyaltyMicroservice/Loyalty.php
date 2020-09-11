<?php



namespace LoyaltyMicroservice;


use LoyaltyMicroservice\Entities\Modified;
use LoyaltyMicroservice\Entities\Special;
use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;
use AuthenticationMicroservice\Authentication;
use LoyaltyMicroservice\Models\Operation;
use LoyaltyMicroservice\Models\Summary;
use LoyaltyMicroservice\Models\History;
use LoyaltyMicroservice\Models\Condition;
use ObjectsMicroservice\SaleObject\Sale;

/**
 * Объект по ведению финансового учета
 */

class Loyalty extends Microservice
{
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiAddModifier($question){
        $answer = new Answer();

        $modified = new Modified($question->getFields());
        if(!Validator::validate($modified,'addModified')) return $answer->genError('Error: failed validation');

        $create = $modified->create();

        if(empty($create)) return $answer->genError('Error: to create Modified');

        $answer->modified = $create;


        return $answer;
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetModifiers($question){
        $answer = new Answer();

        if(!Validator::validate($question,'checkSale')) return $answer->genError('Error: failed validation');
        $special = new Special();
        $special->id = $question->specialId;
//        $clientId = $question->clientId;
        $specialFields = $special->get();
        $special->pull($specialFields);

        $sale = new Sale();
        $sale->pullFullFromArray($question->sale);

        $conditionIds = $special->getConditions();
        $condition = new Condition();

        $context = [];
        foreach ($conditionIds as $conditionId){
            $condition->id = $conditionId;
            $conditionFields = $condition->get();
            $condition->pull($conditionFields);
            $context[$conditionId] = $condition->check($sale);
        }

        $special->setContext($context);
        $specialResult = $special->evaluate();

        $modified = new Modified();
        $modifiers = $modified->getFor($special->getId(),$specialResult);

        $answer->modifiers = $modifiers;


        return $answer;
    }


    public function apiCreateRecord($question)
    {
        $answer = new Answer();
        

        if(!Validator::validate($question,'create')) return $answer->genError('Error: validation failed');

        $summary = new Summary($question->getFields());
        $userId = Authentication::getInstance()->getCurrentUser()->getId(); 
        $summary->setDb($this->db);
        $create = $summary->create();

        if($create==false){
            return $answer->genError('Error: cannot create record');
            
        }
       
        $this->createOperation("createSum",$userId,$create);
        
        $answer->record = $summary->getFields();

        return $answer;
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiUpdateRecord($question)
    {
        
        $answer = new Answer();
        $userId = Authentication::getInstance()->getCurrentUser()->getId(); 

        if(!Validator::validate($question,'edit')) return $answer->genError('Error: failed validation');

        $summary = new Summary($question);
        $summary->setDb($this->db);
        $edit = $summary->update();

        if(!$edit) return $answer->genError('Error: failed to update summaries');

        $this->createOperation("updateSum",$userId,$summary->getId());

        $answer->success = $edit;

        return $answer;
        
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditModified($question){
        $answer = new Answer();

        if(!Validator::validate($question,'editModified')) return $answer->genError('Error: failed validation');

        $modified = new Modified($question->getFields());

        $answer->modified = $modified->update();

        return $answer;
    }


    public function apiDeleteRecord($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');

        $userId = Authentication::getInstance()->getCurrentUser()->getId();
        $summary = new Summary($question->getFields());
        $summary->setDb($this->db);
        $delete = $summary->delete();

        if(!$delete) return $answer->genError('Error: failed to delete summary record');

        $answer->success = $delete;
        $this->createOperation("deleteSum",$userId,$summary->getId());

        return $answer;
    }


    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetModified($question){
        $answer = new Answer();

        if(!Validator::validate($question,'getModified')) return $answer->genError('Error: failed validation');

        $modified = new Modified($question->getFields());

        $answer->modified = $modified->get();

        return $answer;
    }


    public function apiGetRecord($question)
    {
        
        $answer = new Answer();
        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');

        $summary = new Summary($question);
        $summary->setDb($this->db);

        $answer->record = $summary->get();

        return $answer;
        
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetRecords($question)
    {
        
        $answer = new Answer();

        $summary = new Summary($question);
        $summary->setDb($this->db);
        $answer->records = $summary->find([
            'LIMIT' => Summary::createLimit($question),
        ]);

        return $answer;
        
    }



    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetSpecialModifiers($question){
        $answer = new Answer();

        if(!Validator::validate($question,'getSpecialModifiers')) return  $answer->genError('Error: failed validation');
        $modified = new Modified();
        $modified->setDb($this->db);

        $modifiers = $modified->getFor($question->specialId,$question->result);

        $answer->modifiers = $modifiers;

        return $answer;
    }

    public function apiCreateHistoryRecord($question)
    {
        $answer = new Answer();
        

        if(!Validator::validate($question,'create')) return $answer->genError('Error: validation failed');

        $history = new History($question->getFields());
        $userId = Authentication::getInstance()->getCurrentUser()->getId(); 
        $history->setDb($this->db);
        $create = $history->create();

        if($create==false){
            return $answer->genError('Error: cannot create history record');
            
        }

        $this->createOperation("createHRec",$userId,$create);
        
        $answer->history = $history->getFields();

        return $answer;
        
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteHistory($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');

        $history = new History($question->getFields());
        $history->setDb($this->db);
        $delete = $history->delete();

        if(!$delete) return $answer->genError('Error: failed to delete history record');

        $answer->success = $delete;

        return $answer;
        
    }

    /**
     * @param Question $question
     * @return Answer
     */

    public function apiGetAllSpecialModifiers($question)
    {
        $answer = new Answer();

        if (!Validator::validate($question, 'getAllSpecialModifiers')) return $answer->genError('Error: failed validation');
        $modified = new Modified();
        $modified->setDb($this->db);

        $modifiers['true'] = $modified->getFor($question->specialId, 1);
        $modifiers['false'] = $modified->getFor($question->specialId, 0);

        $answer->modifiers = $modifiers;

        return $answer;

    }


    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetHistory($question)
    {
        
        $answer = new Answer();

        $history = new History($question);
        $history->setDb($this->db);
        $answer->records = $history->find([
            'LIMIT' => History::createLimit($question),
        ]);

        return $answer;
        
    }

    
    public function createOperation($operation,$idStaff,$aimId){
        $operation = new Operation([
            'operation' => $operation,
            'idStaff' => $idStaff,
            'aimId' => $aimId,
            'createdAt' => time(),
        ]);

        $operation->setDb($this->db);

        $create = $operation->create();

        return $create;
    }
}