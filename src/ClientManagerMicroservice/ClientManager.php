<?php
namespace ClientManagerMicroservice;


use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;
use ClientManagerMicroservice\Models\Customer;
use ClientManagerMicroservice\Models\Tags;
/**
 * Объект по ведению финансового учета
 */
class ClientManager extends Microservice
{
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCreateCustomer($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'create')) return $answer->genError('Error: validation failed');
        $customer = new Customer($question->getFields());
        if($customer->create()){
            return new Answer($customer->getFields());
        }else{
            return $answer->genError("Error: cannot create customer");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditCustomer($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'edit')) return $answer->genError('Error: validation failed');
        $customer = new Customer($question->getFields());
        if($customer->edit()){
            return new Answer($customer->getFields());
        }else{
            return $answer->genError("Error: cannot edit _Customer in Customer");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteCustomer($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');
        $customer = new Customer($question->getFields());
        if($customer->delete()){
            return new Answer($customer->getFields());
        }else{
            return $answer->genError("Error: cannot delete Customer");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetCustomers($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
        $customer = new Customer($question->getFields());
        if($customer->fetchCustomers()){
            return new Answer($customer->getCustomer());
        }else{
            return $answer->genError("Error: cannot get customer");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     * 
     * example tag = 1
     */
    public function apiGetTagedCustomers($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
        $customer = new Customer($question->getFields());
        if($customer->fetchCustomers()){
            return new Answer($customer->getCustomer());
        }else{
            return $answer->genError("Error: cannot get taged customer");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCreateTag($question)
    {   
        $answer = new Answer();
        
        if(!Validator::validate($question,'createTag')) return $answer->genError('Error: validation failed');

        $tags = new Tags($question->getFields());

        if($tags->create()){
            return new Answer($tags->getFields());
        }else{

            return $answer->genError("Error: cannot create customer");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditTag($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'editTag')) return $answer->genError('Error: validation failed');
        $tags = new Tags($question->getFields());
        if($tags->edit()){
            return new Answer($tags->getFields());
        }else{
            return $answer->genError("Error: cannot edit _Customer in Customer");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteTag($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');
        $tags = new Tags($question->getFields());
        if($tags->delete()){
            return new Answer($tags->getFields());
        }else{
            return $answer->genError("Error: cannot delete Customer");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiAddTag($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'addTag')) return $answer->genError('Error: validation failed');
        
        $tags = new Tags($question->getFields());
        if($tags->setTag()){
            return new Answer($tags->getFields());
        }else{
            return $answer->genError("Error: cannot add tag to a customer");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetTags($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
       
        $tags = new Tags($question->getFields());
        if($tags->fetchTags()){
            return new Answer($tags->getTag());
        }else{
            return $answer->genError("Error: cannot get tags");
        }
        
    }
}