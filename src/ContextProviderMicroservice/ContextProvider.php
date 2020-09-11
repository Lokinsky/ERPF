<?php

namespace ContextProviderMicroservice;


use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;
use ContextProviderMicroservice\Models\Stage;
use ContextProviderMicroservice\Models\Context;
use ContextProviderMicroservice\Models\BA;
/**
 * Объект для предоставление этапов, БА и контекстов.
 * 
 */
class ContextProvider extends Microservice
{
    /**
     * @param Question $question
     * @return Answer
     */
    
    public function apiAddStage($question){

        $answer = new Answer();

        if(!Validator::validate($question,'createStage')) return $answer->genError('Error: validation failed');

        $stage = new Stage($question->getFields());
        
        if($stage->create()){
            $answer = new Answer($stage->getFields());
            $answer->service = $this->getServiceName();
        }
        else{
            $answer->genError("Error: failed to edit stage");
        }
        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditStage($question){
        $answer = new Answer();
        if(!Validator::validate($question,'editStage')) return $answer->genError('Error: validation failed');
        $stage = new Stage($question->getFields());
        
        if($stage->edit()){
            $answer = new Answer($stage->getFields());
            $answer->service = $this->getServiceName();
        }
        else{
            $answer->genError("Error: failed to edit stage");
        }
    

        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteStage($question){
        $answer = new Answer();
        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');
        $stage = new Stage($question->getFields());
        
        if($stage->delete()){
            $answer = new Answer($stage->getFields());
            $answer->service = $this->getServiceName();
        }
        else{
            $answer->genError("Error: failed to delete stage");
        }
        

        return $answer;
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetStages($question){
        $answer = new Answer();
        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
        $stage = new Stage($question->getFields());
        $stage->fetchStages();
        if(!empty($stage->getStages())){
            $answer = new Answer($stage->getStages());
            $answer->service = $this->getServiceName();
        }
        else{
            $answer->genError("Error: failed to get stages");

        }

        return $answer;
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCreateContext($question){
        $answer = new Answer();
        if(!Validator::validate($question,'create')) return $answer->genError('Error: validation failed');
        $context = new Context($question->getFields());
        
        if($context->create()){
            $answer = new Answer($context->getFields());
            $answer->service = $this->getServiceName();
        }
        else{
            $answer->genError("Error: failed to get contexts");
        }

        return $answer;
    }
    
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditContext($question){
        $answer = new Answer();
        if(empty($question->name) || !Validator::validate($question,'edit')) return $answer->genError('Error: validation failed');
        
        $context = new Context($question->getFields());
        
        if($context->edit()){
            $answer = new Answer($context->getFields());
            $answer->service = $this->getServiceName();
        }
        else{
            $answer->genError("Error: failed to get contexts");
        }
        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteContext($question){
        $answer = new Answer();
        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');
        $context = new Context($question->getFields());
        if($context->delete()){
            $answer = new Answer($context->getFields());
            $answer->service = $this->getServiceName();
        }
        else{
            $answer->genError("Error: failed to delete contexts");

        }

        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetContexts($question){
        $answer = new Answer();
        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
        $context = new Context($question->getFields());
        $context->fetchContexts();
        if(!empty($context->getContexts())){
            $answer = new Answer($context->getContexts());
            $answer->service = $this->getServiceName();
        }
        else{
            $answer->genError("Error: failed to get contexts");

        }

        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCreateBa($question){
        $answer = new Answer();
        if(!Validator::validate($question,'create')) return $answer->genError('Error: validation failed');
        $ba = new BA($question->getFields());
        if($ba->create()){
            $answer = new Answer($ba->getFields());
            $answer->service = $this->getServiceName();
        }else{
            $answer->genError("Error: failed to Create ba");

        }

        return $answer;
    }
    
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditBa($question){
        $answer = new Answer();
        if(!Validator::validate($question,'edit')) return $answer->genError('Error: validation failed');
        $ba = new BA($question->getFields());
        if($ba->edit()){
            $answer = new Answer($ba->getFields());
            $answer->service = $this->getServiceName();
        }else{
            $answer->genError("Error: failed to edit ba");

        }

        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteBa($question){
        $answer = new Answer();
        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');
        $ba = new BA($question->getFields());
        if($ba->delete()&&$ba->fetchBas()){
            $answer = new Answer($ba->getBas());
            $answer->service = $this->getServiceName();
            $ba = new BA($question->getFields());
        }else{
            $answer->genError("Error: failed to delete");
        }
        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetBa($question){
        $answer = new Answer();
        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
        $ba = new BA($question->getFields());
        if($ba->fetchBas()){
            $answer = new Answer($ba->getBas());
            $answer->service = $this->getServiceName();
        }else{
            $answer->genError("Error: failed to get ba`s");
        }

        return $answer;
    }

}
