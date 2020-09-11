<?php
namespace AccounterMicroservice;


use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;
use AccounterMicroservice\Models\Summary;
/**
 * Объект по ведению финансового учета
 */
class Accounter extends Microservice
{
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCreateRecord($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'create')) return $answer->genError('Error: validation failed');
        
        $summary = new Summary($question->getFields());

        if($summary->create()){
            return new Answer($summary->getFields());
        }else{
            return $answer->genError("Error: cannot create record in summary");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditRecord($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'edit')) return $answer->genError('Error: validation failed');
        $summary = new Summary($question->getFields());
        if($summary->edit()){
            return new Answer($summary->getFields());
        }else{
            return $answer->genError("Error: cannot edit record in summary");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteRecord($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');
        $summary = new Summary($question->getFields());
        if($summary->delete()){
            return new Answer($summary->getFields());
        }else{
            return $answer->genError("Error: cannot delete record in summary");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetRecords($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
        $summary = new Summary($question->getFields());
        if($summary->fetchSummaries()){
            return new Answer($summary->getSummaries());
        }else{
            return $answer->genError("Error: cannot delete record in summary");
        }
        
    }
}