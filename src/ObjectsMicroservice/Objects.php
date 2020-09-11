<?php


namespace ObjectsMicroservice;


use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;
use ObjectsMicroservice\SaleObject\Sale;

/**
 * Управляет взаимодействием между ОКП и базой данных
 * @package ObjectsMicroservice
 */
class Objects extends Microservice
{

    /**
     * Метод создания компонента, в том числе и ОКП, за тип отвечает параметр "cot"
     * @param Question $question
     * @return Answer
     */
    public function apiCreateComponent($question){
        $answer = new Answer();

        if(!Validator::validate($question,'createComponent')) return $answer->genError('Error: validation failed');

        if(!is_null($question->cot)){
            $component = Sale::newComponentOf($question->cot);
            if(!empty($component)){
                $fields = $question->getFields();
                $component->pull($fields);
                if($id = $component->create()){
                    $answer->id = $id;
                }else{
                    $answer->genError('Error: failed to save component');
                }
            }else{
                $answer->genError('Error: failed to create component');
            }
        }else{
            $answer->genError('Error: empty component type');
        }

        return $answer;
    }

    /**
     * Метод получения компонента по его id и типу cot
     * @param Question $question
     * @return Answer
     */
    public function apiGetComponent($question){
        $answer = new Answer();

        if(!Validator::validate($question,'getComponent')) return $answer->genError('Error: validation failed');

        if(!is_null($question->cot)){
            $component = Sale::newComponentOf($question->cot);
            if(!empty($component)){
                $fields = $question->getFields();
                $component->pull($fields);
                $answer->component = $component->get();
            }else{
                $answer->genError('Error: failed to create component');
            }
        }else{
            $answer->genError('Error: empty component type');
        }


        return $answer;
    }

    /**
     * Метод изменения компонента по его id и типу cot
     * @param Question $question
     * @return Answer
     */
    public function apiEditComponent($question){
        $answer = new Answer();

        if(!Validator::validate($question,'editComponent')) return $answer->genError('Error: validation failed');

        if(!is_null($question->cot)){
            $component = Sale::newComponentOf($question->cot);
            if(!empty($component)){
                $fields = $question->getFields();
                $component->pull($fields);
                if($component->update()){
                    $answer->success = true;
                }else{
                    $answer->genError('Error: failed to edit component');
                }
            }else{
                $answer->genError('Error: failed to create component');
            }
        }else{
            $answer->genError('Error: empty component type');
        }

        return $answer;
    }

    /**
     * Метод удаления компонента по его id и cot
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteComponent($question){
        $answer = new Answer();

        if(!Validator::validate($question,'getComponent')) return $answer->genError('Error: validation failed');

        if(!is_null($question->cot)){
            $component = Sale::newComponentOf($question->cot);
            if(!empty($component)){
                $fields = $question->getFields();
                $component->pull($fields);
                $answer->deleted = $component->delete();
            }else{
                $answer->genError('Error: failed to create component');
            }
        }else{
            $answer->genError('Error: empty component type');
        }


        return $answer;
    }


    /**
     * Метод получения ОКП по id в формате массива вложенности
     * @param Question $question
     * @return Answer
     */
    public function apiGetSaleObject($question){
        $answer = new Answer();

        if(empty($question->id) or !Validator::ruleInt($question->id)) return $answer->genError('Error: failed validation');

        $sale = new Sale();
        $fields = $sale->get($question->id);
        if(empty($fields)) return $answer->genError('Error: sale not found');

        $sale->pull($fields);
        $sale->unserialize($sale->getSerialized());

        $answer->sale = $sale->getFullAsArray();

        return $answer;
    }

    /**
     * Метод создания ОКП по массиву его структуры
     * @param Question $question
     * @return Answer
     */
    public function apiCreateSaleObject($question){
        $answer = new Answer();

        if(!Validator::validate($question,'createSaleObject')) return $answer->genError('Error: failed validation');

        $sale = new Sale();
        $sale->pullFullFromArray($question->getFields());
        $sale->serialize();
        if($id = $sale->create()){
            $answer->id = $id;
        }else{
            $answer->genError('Error: failed to create SaleObject');
        }

        return $answer;
    }

    /**
     * Метода изменения ОКП по его структуре в виде массива
     * @param Question $question
     * @return Answer
     */
    public function apiUpdateSaleObject($question){
        $answer = new Answer();

        if(!Validator::validate($question,'updateSaleObject')) return $answer->genError('Error: failed validation');

        $sale = new Sale();
        $sale->pullFullFromArray($question->getFields());
        $sale->serialize();
        if($sale->update()){
            $answer->success = true;
        }else{
            $answer->genError('Error: failed to update SaleObject');
        }

        return $answer;
    }

    /**
     * Метод удаления ОКП по его id
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteSaleObject($question){
        $answer = new Answer();

        if(empty($question->id) or !Validator::ruleInt($question->id)) return $answer->genError('Error: failed validation');

        $sale = new Sale();
        $sale->id = $question->id;
        $answer->deleted = $sale->delete();

        return $answer;
    }

}