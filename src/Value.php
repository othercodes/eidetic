<?php

namespace OtherCode\Eidetic;

/**
 * Class ValueObject
 * @package OtherCode\Eidetic
 */
class Value
{
    /**
     * Object Value
     * @var mixed
     */
    private $value;

    /**
     * ValueObject constructor.
     * @param null $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * Get the object value.
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Compare two valuable objects.
     * @param Value $object
     * @return bool
     */
    public function equals(Value $object): bool
    {
        return json_encode($this->getValue()) === json_encode($object->getValue());
    }

}