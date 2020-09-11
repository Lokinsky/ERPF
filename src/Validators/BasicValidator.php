<?php


namespace Validators;


use Microservices\DataObjects\ArrayObject;

class BasicValidator
{

    /**
     * Проверяет, состоит ли строка только из латинских букв обоих регистров
     *
     * @param string $input
     * @return bool
     */
    public static function ruleOnlyLatinChars($input)
    {
        if (is_string($input) and preg_match('/^[A-Za-z]+$/', $input) != false) {
            return true;
        }

        return false;
    }

    /**
     * Проверяет, состоит ли строка только из латинских букв обоих регистров и цифп
     *
     * @param string $input
     * @return bool
     */
    public static function ruleOnlyLatinCharsAndNumeric($input)
    {
        if (is_string($input) and preg_match("/^[A-Za-z0-9]+$/", $input) != false) {
            return true;
        }

        return false;
    }

    /**
     * Проверяет, является ли строка числовым значением
     *
     * @param string $input
     * @return bool
     */
    public static function ruleNumeric($input)
    {
        return is_numeric($input);
    }

    public static function ruleNull($input)
    {
        return is_null($input);
    }

    /**
     * Проверяет, является ли значение пустым
     *
     * @param mixed $input
     * @return bool
     */
    public static function ruleEmpty($input)
    {
        return empty($input);
    }

    /**
     * Возвращает приведённое к булевскому типу исходное значение
     *
     * @param $input
     * @return bool
     */
    public static function ruleBooleanTrue($input)
    {
        return (bool)$input;
    }

    /**
     * Проверяет, удовлетворяет ли длина строки заданному отрезку
     *
     * @param string $input
     * @param array $limits
     * @return bool
     */
    public static function ruleLength($input, $limits)
    {
        if (is_numeric($input)) {
            $input = (string)$input;
        }
        if (!is_string($input) and !is_null($input)) return false;

        $length = mb_strlen($input);

        return ($length >= $limits[0] and $length <= $limits[1]);
    }

    /**
     * Проверяет, является ли длина строки больше минимального значения
     *
     * @param string $input
     * @param float|int $min
     * @return bool
     */
    public static function ruleLmin($input, $min)
    {
        if (is_numeric($input)) {
            $input = (string)$input;
        }
        if (is_null($input)) $length = 0; elseif (is_string($input)) $length = mb_strlen($input);
        else return false;
        return $length >= $min;
    }

    /**
     * Проверяет, является ли длина строки меньше максимального значения
     *
     * @param string $input
     * @param float|int $max
     * @return bool
     */
    public static function ruleLmax($input, $max)
    {
        if (is_numeric($input)) {
            $input = (string)$input;
        }
        if (is_null($input)) {
            $length = 0;
        } elseif (is_string($input)) {
            $length = mb_strlen($input);
        } else {
            return false;
        }
        return $length <= $max;
    }

    /**
     * Проверяет, больше ли числовое значение указанного минимального
     *
     * @param string|float|int $input
     * @param float|int $min
     * @return bool
     */
    public static function ruleRmin($input, $min)
    {
        return $input >= $min;
    }

    /**
     * Проверяет, меньше ли числовое значение указанного максимального
     *
     * @param string|float|int $input
     * @param float|int $max
     * @return bool
     */
    public static function ruleRmax($input, $max)
    {
        return $input <= $max;
    }

    /**
     * Прверяет, удовлетовряет ли указанное значение числовому отрезку
     *
     * @param string|float|int $input
     * @param array $range
     * @return bool
     */
    public static function ruleRange($input, $range)
    {
        return ($input >= $range[0] and $input <= $range[1]);
    }

    /**
     * Проверяет, присутсвует ли переданное значение в массиве
     *
     * @param mixed $input
     * @param array $values
     * @return bool
     */
    public static function ruleIn($input, $values)
    {
        return in_array($input, $values);
    }

    /**
     * Проверяет, можно ли посчитать объект и входит ли подсчёт в указанные числовой промежуток
     *
     * @param $input
     * @param float|int $borders
     * @return bool
     */
    public static function ruleCount($input, $borders)
    {
        if (is_countable($input)) {
            $counted = count($input);
        } elseif (is_null($input)) {
            $counted = 0;
        } else {
            return false;
        }

        return ($counted >= $borders[0] and $counted <= $borders[1]);
    }

    /**
     * Проверяет, можно ли посчитать объект и больше ли количество его частей минимального значения
     *
     * @param $input
     * @param float|int $min
     * @return bool
     */
    public static function ruleCmin($input, $min)
    {
        if (is_countable($input)) {
            $counted = count($input);
        } elseif (is_null($input)) {
            $counted = 0;
        } else {
            return false;
        }
        return $counted >= $min;
    }

    /**
     * Проверяет, можно ли посчитать объект и меньше ли количество его частей максимального значения
     *
     * @param $input
     * @param float|int $max
     * @return bool
     */
    public static function ruleCmax($input, $max)
    {
        if (is_countable($input)) {
            $counted = count($input);
        } elseif (is_null($input)) {
            $counted = 0;
        } else {
            return false;
        }
        return $counted <= $max;
    }

    /**
     * Проверяет, является ли строка верным URL адресом
     *
     * @param string $input
     * @return bool
     */
    public static function ruleUrl($input)
    {
        return (filter_var($input, FILTER_VALIDATE_URL) !== false);
    }

    /**
     * Проверяет, является ли строка верным MAC-адресом
     *
     * @param string $input
     * @return bool
     */
    public static function ruleMac($input)
    {
        return (filter_var($input, FILTER_VALIDATE_MAC) !== false);
    }

    /**
     * Проверяет, является ли строка верным IP-адресом
     *
     * @param string $input
     * @return bool
     */
    public static function ruleIp($input)
    {
        return (filter_var($input, FILTER_VALIDATE_IP) !== false);
    }

    /**
     * Проверяет, является ли строка целым числовым значением
     *
     * @param string $input
     * @return bool
     */
    public static function ruleInt($input)
    {
        return (filter_var($input, FILTER_VALIDATE_INT) !== false);
    }

    /**
     * Проверяет, является ли строка действительным числом
     *
     * @param string $input
     * @return bool
     */
    public static function ruleFloat($input)
    {
        return (filter_var($input, FILTER_VALIDATE_FLOAT) !== false);
    }

    /**
     * Проверяет, является ли строка верным Email-адресом
     *
     * @param string $input
     * @return bool
     */
    public static function ruleEmail($input)
    {
        return (filter_var($input, FILTER_VALIDATE_EMAIL) !== false);
    }

    /**
     * Проверяет, является ли строка верным доменным именем
     *
     * @param string $input
     * @return bool
     */
    public static function ruleDomain($input)
    {
        return (filter_var($input, FILTER_VALIDATE_DOMAIN) !== false);
    }

    /**
     * Проверяет, является ли строка семантическим утвердительным значением
     *
     * @param string $input
     * @return bool
     */
    public static function ruleSemanticTrue($input)
    {
        return (filter_var($input, FILTER_VALIDATE_BOOLEAN) !== false);
    }

    /**
     * Производит валидацию данных объекта или массива по заданным параметрам
     * @param ArrayObject|array $object
     * @param array $context
     * @return bool
     */
    public static function validate($object, $contexts = ['default'],$rules=[])
    {
        if (is_string($contexts)) $contexts = [$contexts];
        if(empty($rules)) $rules = static::getRules();

        if (is_array($object)) {
            $objectFields = &$object;
        } else {
            if (method_exists($object, 'getFields')) {
                $objectFields = $object->getFields();
            } else {
                return false;
            }

        }


        $invertFlag = false;

        foreach ($rules as $context => $contextRules) {
            if (in_array($context, $contexts)) {
                foreach ($contextRules as $fieldsNames => $fieldsRules) {
                    $fieldsNames = explode(',', $fieldsNames);
                    foreach ($fieldsNames as $fieldsName) {
                        foreach ($objectFields as $objectFieldName => $objectFieldValue) {
                            if ($fieldsName == '*' or $fieldsName == $objectFieldName) {
                                foreach ($fieldsRules as $fieldsRule) {
                                    if (is_array($fieldsRule)) {
                                        foreach ($fieldsRule as $ruleName => $ruleValues) {
                                            if (mb_substr($ruleName, 0, 1) == '!') {
                                                $invertFlag = true;
                                                $ruleName = mb_substr($ruleName, 1);
                                            } else {
                                                $invertFlag = false;
                                            }
                                            $methodName = 'rule' . $ruleName;
//                                            var_dump($methodName);
                                            if (method_exists(static::class, $methodName)) {
                                                $res = static::$methodName($objectFieldValue, $ruleValues);
                                            }
                                        }
                                    } elseif (is_string($fieldsRule)) {
                                        if (mb_substr($fieldsRule, 0, 1) == '!') {
                                            $invertFlag = true;
                                            $fieldsRule = mb_substr($fieldsRule, 1);
                                        } else {
                                            $invertFlag = false;
                                        }
                                        $methodName = 'rule' . $fieldsRule;
//                                        var_dump($methodName);
                                        if (method_exists(static::class, $methodName)) {
                                            $res = static::$methodName($objectFieldValue);
                                        }
                                    } else {
                                        continue;
                                    }
                                    if ((!$invertFlag and !$res) or ($invertFlag and $res)) return false;
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Возвращает массив правил для записанных контекстов и полей
     *
     * @return array
     */
    public static function getRules()
    {
        return [
            'default' => [
                '*' => ['lmax' => 512],
            ]
        ];
    }
}