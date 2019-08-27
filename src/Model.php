<?php

namespace OtherCode\Eidetic;

use ArrayAccess;
use JsonSerializable;
use OtherCode\Eidetic\Exceptions\ImmutabilityIntegrityException;

/**
 * Class VersionedDataModel
 * @package OtherCode\Eidetic
 */
class Model implements ArrayAccess, JsonSerializable
{
    /** @var VersionableValue[] */
    protected $attributes = [];

    /**
     * Model constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
    }

    /**
     * Populate the object recursively
     * @param array|object $attributes
     * @return $this
     */
    public function hydrate($attributes)
    {
        foreach ($attributes as $key => $attribute) {
            $this->setAttribute($key, $attribute);
        }
        return $this;
    }

    /**
     * Get the required attribute value.
     * @param string $key
     * @param int|null $version
     * @return mixed|null
     */
    public function getAttribute(string $key, ?int $version = null)
    {
        if (array_key_exists($key, $this->attributes)) {

            /** @var Version $snapshot */
            $snapshot = $this->attributes[$key]->getVersion($version);

            if (is_null($snapshot)) {
                return null;
            }

            if ($snapshot->getValue() === null && $this->hasProxyMethod('compute', $key)) {
                $this->setAttribute($key, call_user_func_array(
                    [$this, $this->getProxyMethod('compute', $key)], []
                ));
            }

            if ($this->hasProxyMethod('get', $key)) {
                return call_user_func_array(
                    [$this, $this->getProxyMethod('get', $key)], [$this->attributes[$key]]
                );
            }

            return $snapshot->getValue();
        }
        return null;
    }

    /**
     * Set the required attribute value.
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute(string $key, $value)
    {
        if ($this->hasProxyMethod('set', $key)) {
            $value = call_user_func_array(
                [$this, $this->getProxyMethod('set', $key)], [$value]
            );
        }

        $this->attributes[$key]->setValue($value);

        return $this;
    }

    /**
     * Determine if a proxy method exists for an attribute.
     * @param string $prefix
     * @param string $key
     * @return bool
     */
    public function hasProxyMethod(string $prefix, string $key): bool
    {
        return is_callable([$this, $this->getProxyMethod($prefix, $key)]);
    }

    /**
     * Get the proxy method name.
     * @param string $prefix
     * @param string $key
     * @return string
     */
    public function getProxyMethod(string $prefix, string $key): string
    {
        return trim($prefix . str_replace(['_', '-'], '', ucwords($key, "-_ \t\r\n\f\v")) . 'Attribute');
    }

    /**
     * Transform the given structure into array
     * @param mixed $value
     * @return array
     */
    public static function arrayize($value)
    {
        $array = [];
        foreach ($value as $key => $item) {

            switch (gettype($item)) {
                case 'object':
                case 'array':
                    $buffer = self::arrayize($item);
                    if (!empty($buffer)) {
                        $array[trim($key)] = $buffer;
                    }
                    break;
                default:
                    if (isset($item)) {
                        $array[trim($key)] = $item;
                    }
            }

        }
        return $array;
    }

    /**
     * Transform the \Connect\Model into array
     * @return array
     */
    public function toArray()
    {
        return self::arrayize($this->attributes);
    }

    /**
     * Convert the object into something JSON serializable.
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Return the object in json format
     * @param boolean $pretty
     * @return string
     */
    public function toJSON($pretty = false)
    {
        return json_encode($this->toArray(), $pretty ? JSON_PRETTY_PRINT : null);
    }

    /**
     * Dynamically retrieve attributes on the model.
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return !is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->attributes[$offset]->setValue(null);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Convert the model to its string representation.
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * When a model is being unserialized, check if it needs to be booted.
     * @throws ImmutabilityIntegrityException
     */
    public function __wakeup()
    {
        foreach ($this->attributes as $key => $attribute) {
            if (!$attribute->checkIntegrity()) {
                throw new ImmutabilityIntegrityException("'$key' property values has been illegal changed.");
            }
        }
    }
}