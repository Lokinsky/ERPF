<?php


namespace ObjectsMicroservice\SaleObject;


use ObjectsMicroservice\SaleObject\Components\Base;
use ObjectsMicroservice\SaleObject\Components\Item;
use ObjectsMicroservice\SaleObject\Components\Material;
use ObjectsMicroservice\SaleObject\Components\Operation;
use ObjectsMicroservice\SaleObject\Components\PriceModifier;
use ObjectsMicroservice\SaleObject\Components\Product;
use ObjectsMicroservice\SaleObject\Components\Property;
use ObjectsMicroservice\SaleObject\Components\Service;

/**
 * Класс, описывающий ОКП, его составляющие и методы обработки данных внутри него
 */
class Sale extends Base implements SaleI
{
    /**
     * Массив массивов данных компонентов в ассоциации с их адресами
     * @var array
     */
    protected $componentsMap = [];

    protected $componentsCounters = [];

    /**
     * Массив вложенных ОКП
     * @var Sale[]
     */
    protected $sales;

    /**
     * Массив вложенных услуг
     * @var Service[]
     */
    protected $services;

    /**
     * Массив вложенных операций
     * @var Operation[]
     */
    protected $operations;

    /**
     * Массив вложенных продуктов
     * @var Product[]
     */
    protected $products;

    /**
     * Массив вложенных товаров
     * @var Item[]
     */
    protected $items;

    /**
     * Массив вложенных материалов
     * @var Material[]
     */
    protected $materials;


    /**
     * Массив вложенных свойств
     * @var Property[]
     */
    protected $properties;

    /**
     * Массив вложенных модификаторов цен
     * @var PriceModifier[]
     */
    protected $priceModifiers;

    /**
     * Строка сериализованного формата объекта
     * @var string
     */
    public $serialized;

    /**
     * Страховочная переменная в будущем предполагающая фантомную суть Sale
     * @var int
     */
    private $typeId;

    /**
     * Публичные поля ОКП
     * @return array|string[]
     */
    public function getFieldNames()
    {
        $parentFields = parent::getFieldNames();
        $childFields = [
            'price',
            'cost',
            'amount',
            'serialized',
        ];

        return array_merge($parentFields, $childFields);
    }


    /**
     * Обновляет картку компонентов добавляя отсутствующие адреса
     * @param null|Sale $parent
     */
    public function refreshComponentsMap($parent = null)
    {
        if (empty($parent)) {
            $this->componentsMap = [];
            $parent = $this;
        }
        $reflected = Sale::getReflected();
        $saleable = Sale::getSaleable();

        foreach ($reflected as $group) {
            if (!empty($this->$group)) {
                foreach ($this->$group as $component) {
                    $this->map($parent, $component);
                    if (in_array($group, $saleable) !== false) $component->refreshComponentsMap($parent);
                }
            }
        }
    }

    /**
     * Наполнение карты компонентов данными из БД
     */
    public function pullComponentsMap()
    {
        foreach ($this->componentsMap as $address => &$componentFields) {
            $componentId = $this->getIdBy($address);
            $componentType = $this->getTypeNumberBy($address);

            $componentFields = (static::newComponentOf($componentType))->get($componentId);
        }
    }

    /**
     * Наполнение вложенных компонентов данными из карты компонентов
     */
    public function pullFromComponentsMap()
    {
        if (empty($parent)) {
            $parent = $this;
        }
        $reflected = Sale::getReflected();
        $saleable = Sale::getSaleable();

        foreach ($reflected as $group) {
            if (!empty($this->$group)) {
                foreach ($this->$group as $component) {
                    $this->pullFromMap($parent, $component);
                    if (in_array($group, $saleable) !== false) $component->pullFromComponentsMap($parent);
                }
            }
        }
    }

    /**
     * Наполнение компонента из карты родителя
     * @param Sale $parent
     * @param Sale $component
     */
    public function pullFromMap($parent, &$component)
    {
        $address = $component->getAddress();
        if (isset($parent->componentsMap[$address])) {
            $component->pull($parent->componentsMap[$address]);
        }
    }

    /**
     * Возвращает свою структуру вложенности в виде массива (вместе с полями компонентов)
     * @return array
     */
    public function getFullAsArray()
    {
        $self = $this->getFields();

        $reflected = Sale::getReflected();
        $saleable = Sale::getSaleable();

        foreach ($reflected as $group) {
            if (!empty($this->$group)) {
                if (!isset($self[$group])) $self[$group] = [];

                foreach ($this->$group as $component) {
                    if (in_array($group, $saleable) !== false) {
                        $self[$group][] = $component->getFullAsArray();
                    } else {
                        $self[$group][] = $component->getFields();
                    }
                }
            }
        }

        return $self;
    }

    public function countAllComponents()
    {
        $before = [$this, 'countComponent'];
        $this->countComponent($this, 0);
        $this->walkSales($before, []);
    }

    public function countComponent($sale, $lvl)
    {
        $address = $sale->getAddress();
        $amount = $sale->getAmount();
        $this->countAddressed($address, $amount, $lvl);

        $unsalable = array_diff(static::getReflected(), static::getSaleable());

        foreach ($unsalable as $group) {
            if (!empty($sale->$group)) {
                foreach ($sale->$group as $component) {
                    $address = $component->getAddress();
                    $amount = 1;
                    $this->countAddressed($address, $amount, $lvl);
                }
            }
        }
    }

    public function countAddressed($address, $amount, $lvl)
    {
        if (!isset($this->componentsCounters[$lvl])) $this->componentsCounters[$lvl] = [];
        if (!isset($this->componentsCounters[$lvl][$address])) $this->componentsCounters[$lvl][$address] = 0;

        $this->componentsCounters[$lvl][$address] += $amount;
    }

    public function getComponentsCounters()
    {
        return $this->componentsCounters;
    }


    /**
     * Наполняет себя из массива с учётом структуры
     * @param array $from
     * @return $this
     */
    public function pullFullFromArray($from)
    {
        $reflected = Sale::getReflected();
        $saleable = Sale::getSaleable();
        $selfFieldsNames = $this->getFieldNames();

        foreach ($from as $fieldName => $value) {
            if (($type = array_search($fieldName, $reflected)) !== false) {
                $group = $reflected[$type];
                foreach ($value as $newComponentArray) {
                    $component = Sale::newComponentOf($type);
                    if (is_null($this->$group)) $this->$group = [];

                    if (in_array($group, $saleable)) {
                        $component->pullFullFromArray($newComponentArray);
                    } else {
                        $component->pull($newComponentArray);
                    }

                    if (!empty($component)) {
                        $this->$group[] = $component;
                    }
                }
            } elseif (in_array($fieldName, $selfFieldsNames)) {
                $this->$fieldName = $value;
            }
        }

        return $this;
    }


    /**
     * Названия защищённых полей вложенных массивов с компонентами
     * @return string[]
     */
    public static function getReflected()
    {
        return [
            0 => 'sales',
            1 => 'services',
            2 => 'operations',
            3 => 'products',
            4 => 'items',
            5 => 'materials',
            6 => 'properties',
            7 => 'priceModifiers',
        ];
    }

    /**
     * Возвращает поля, которые могут иметь вложенность
     * @return string[]
     */
    public static function getSaleable()
    {
        return [
            'sales',
            'services',
            'operations',
            'products',
            'items',
            'materials',
        ];
    }

    /**
     * Добавляет компонент в карту родителя
     * @param Sale $parent
     * @param Sale $component
     */
    public function map($parent, $component)
    {
        $parent->addComponentToMap($component->getAddress());
    }

    /**
     * @param Base $component
     */
    public function loadComponentToMap($component)
    {
        $address = $component->getAddress();
        $this->componentsMap[$address] = $component->getFields();
    }

    public function getComponentFromMap($address)
    {
        if (empty($this->componentsMap[$address])) return [];
        return $this->componentsMap[$address];
    }

    /**
     * Геттер для карты компонентов
     * @return array
     */
    public function getComponentsMap()
    {
        return $this->componentsMap;
    }

    /**
     * Добавляет адрес в карту компонентов
     * @param string $address
     */
    public function addComponentToMap($address)
    {
        if (isset($this->componentsMap[$address]) === false) $this->componentsMap[$address] = null;
    }

    /**
     * Возвращает массив всех существующих внутри ОКП
     * @return array
     */
    public function getAllSales()
    {
        $sales = [];
        $saleable = Sale::getSaleable();

        foreach ($saleable as $group) {
            if (empty($this->$group)) continue;

            foreach ($this->$group as $sale) {
                $sales[] = $sale;
                $sales = array_merge($sales, $sale->getAllSales());
            }
        }

        return $sales;
    }

    /**
     * @return Sale[]
     */
    public function getLocalSales()
    {
        $saleable = Sale::getSaleable();
        $sales = [];
        foreach ($saleable as $group) {
            if (!empty($group)) {
                $sales = array_merge($sales, $group);
            }
        }

        return $sales;
    }

    /**
     * @return array|PriceModifier[]
     */
    public function getPriceModifiers()
    {
        if (empty($this->priceModifiers)) return [];

        return $this->priceModifiers;
    }

    /**
     *
     * @return string
     */
    public function serialize()
    {
        $this->serialized = '';
        $this->walkSales([$this, 'beforeRecurse'], [$this, 'afterRecurse']);
        return $this->serialized;
    }

    public function walkSales($beforeCallback, $afterCallback, $lvl = 0)
    {
        $saleable = static::getSaleable();

        foreach ($saleable as $group) {
            if (empty($this->$group)) continue;

            foreach ($this->$group as $sale) {
                if ($beforeCallback !== false) call_user_func($beforeCallback, $sale, $lvl);
                $sale->walkSales($beforeCallback, $afterCallback, $lvl + 1);
                if ($afterCallback !== false) call_user_func($afterCallback, $sale, $lvl);
            }
        }
    }


    public function beforeRecurse($sale)
    {
        $this->serialized .= '(';
        $this->serialized .= $sale->getAddress();
    }

    public function afterRecurse($sale)
    {
        $this->serializeUnsalable($sale);
        $this->serialized .= ')';
    }

    public function serializeUnsalable($sale)
    {
        if (!empty($sale->properties)) {
            foreach ($sale->properties as $property) {
                $this->serialized .= '(';
                $this->serialized .= $property->getAddress();
                $this->serialized .= ')';
            }
        }
        if (!empty($sale->priceModifiers)) {
            foreach ($sale->priceModifiers as $priceModifier) {
                $this->serialized .= '(';
                $this->serialized .= $priceModifier->getAddress();
                $this->serialized .= ')';
            }
        }
    }

    public function getSerialized()
    {
        if (!empty($this->serialized)) return $this->serialized;

        return false;
    }

    public function unserialize($string)
    {
        if (empty($string) or mb_strlen($string) < 2) return;

        $chars = mb_str_split($string);
        $opening = '(';
        $ending = ')';
        $begins = 0;
        $ends = 0;
        $buffer = '';

        foreach ($chars as $char) {
            if ($char == $opening) {
                $begins++;
            }
            if ($char == $ending) {
                $ends++;
            }
            $buffer .= $char;
            if ($begins == $ends) {
                if (mb_substr($buffer, 0, 1) == '(') $buffer = mb_substr($buffer, 1);
                if (mb_substr($buffer, mb_strlen($buffer) - 1) == ')') $buffer = mb_substr($buffer, 0, mb_strlen($buffer) - 1);
                $childAddressLength = strspn($buffer, "1234567890");
                $childAddress = mb_substr($buffer, 0, $childAddressLength);
                $childChildren = mb_substr($buffer, $childAddressLength);

                $child = $this->addNewComponent($childAddress);
                if (is_object($child) and (mb_strlen($childChildren) >= 2)) $child->unserialize($childChildren);

                $begins = 0;
                $ends = 0;
                $buffer = '';
            }
        }
    }

    public function addNewComponent($address)
    {
        $component = $this->newComponent($address);
        $this->addComponent($component);

        return $component;
    }

    public function newComponent($address)
    {
        if (empty($address) or strlen($address) < 2) return false;

        $type = $this->getTypeNumberBy($address);
        $component = Sale::newComponentOf($type);
        $component->id = $this->getIdBy($address);
        return $component;
    }

    public function getTypeNumberBy($address)
    {
        return mb_substr($address, strlen($address) - 1, 1);
    }

    public static function newComponentOf($type)
    {
        switch ($type) {
            case 0:
                return (new Sale());
                break;
            case 1:
                return (new Service());
                break;
            case 2:
                return (new Operation());
                break;
            case 3:
                return (new Product());
                break;
            case 4:
                return (new Item());
                break;
            case 5:
                return (new Material());
                break;
            case 6:
                return (new Property());
                break;
            case 7:
                return (new PriceModifier());
                break;
        }

        return false;
    }

    public function getId()
    {
        if (isset($this->id)) {
            return $this->id;
        }

        return 0;
    }

    public function getFieldsAliases()
    {
        return array_combine($this->getFieldNames(), $this->getFieldNames());
    }

    public function getIdBy($address)
    {
        return mb_substr($address, 0, mb_strlen($address) - 1);
    }

    protected function addComponent($component)
    {
        $reflected = Sale::getReflected();
        $type = $component->getTypeNumber();
        if (isset($reflected[$type])) {
            $this->{$reflected[$type]}[] = $component;
            return $component;
        }

        return false;
    }

    public static function getTableName()
    {
        return 'sales';
    }

    public function getProperties()
    {
        if (empty($this->properties)) return [];
        return $this->properties;
    }

    public function getTypeNumber()
    {
        if (!is_null($this->typeId)) {
            return $this->typeId;
        }
        return 0;
    }

    public function getCost()
    {
        if (isset($this->cost)) {
            return $this->cost;
        }

        return 0;
    }

    public function getPrice()
    {
        if (isset($this->price)) {
            return $this->price;
        }

        return 0;
    }

    public function getAmount()
    {
        if (isset($this->amount)) {
            return $this->amount;
        }

        return 1;
    }

}