<?php
namespace Poirot\Config;

use Poirot\Std\Exceptions\exImmutable;
use Poirot\Std\Struct\DataEntity;
use Poirot\Std\Type\StdArray;
use Poirot\Std\Type\StdTravers;


class Config
    extends DataEntity
    implements \ArrayAccess
{
    protected $isImmutable;


    /**
     * Config constructor.
     *
     * @param \Traversable|null $data
     * @param bool              $immutable Allow modification on config
     */
    function __construct($data = null, bool $immutable = false)
    {
        $this->setImmutable($immutable);

        parent::__construct($data);
    }


    /**
     * @inheritdoc
     */
    function set($key, $value = null)
    {
        $this->_assertImmutable();


        if (is_array($value) || $value instanceof StdArray || $value instanceof Config)
            $value = new static($value, $this->isImmutable());

        if (null !== $key) {
            parent::set($key, $value);
        } else {
            // when we just add an item in array ways $i[] = 'v';
            $prop = &$this->_referDataArrayReference();
            $prop[] = $value;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    function del($key)
    {
        $this->_assertImmutable();


        parent::del($key);
        return $this;
    }

    /**
     * Is Immutable Config?
     *
     * @return bool
     */
    function isImmutable()
    {
        return $this->isImmutable;
    }

    /**
     * Set Config To Immutable
     *
     * @param bool $deep Include all nested Config
     *
     * @return $this
     */
    function setImmutable(bool $deep = true)
    {
        $this->isImmutable = $deep;
        return $this;
    }

    /**
     * Merge Config with Given Data
     *
     * @param \Traversable $data
     *
     * @return $this
     */
    function mergeRecursive($data)
    {
        $this->_assertImmutable();


        if ($data instanceof \Traversable)
            $data = StdTravers::of($data)->toArray();

        if (! is_array($data) )
            throw new \InvalidArgumentException(sprintf(
                'Invalid Config Provided, Array or Traversable Expected; given: (%s).'
                , \Poirot\Std\flatten($data)
            ));


        $selfArray = new StdArray( $this->toArray() );
        $this->import(
            $selfArray->withMergeRecursive($data, false)
        );

        return $this;
    }

    /**
     * Return an associative array of the stored data.
     *
     * @return array
     */
    function toArray()
    {
        $array = [];
        foreach ($this as $key => $value) {
            if ($value instanceof self)
                $array[$key] = $value->toArray();
            else
                $array[$key] = $value;
        }

        return $array;
    }


    // Implement MagicMethods

    function __isset($key)
    {
        return $this->has($key);
    }

    function __set($key, $value)
    {
        $this->set($key, $value);
        return $this;
    }

    function __get($key)
    {
        return $this->get($key);
    }

    function __unset($key)
    {
        $this->del($key);
    }

    function __clone()
    {
        $properties = &$this->_referDataArrayReference();

        foreach ($properties as $key => $value) {
            if ($value instanceof self)
                $properties[$key] = clone $value;
            else
                $properties[$key] = $value;
        }
    }


    // Implement ArrayAccess

    /**
     * @inheritdoc
     */
    function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * @inheritdoc
     */
    function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * @inheritdoc
     */
    function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * @inheritdoc
     */
    function offsetUnset($offset)
    {
        $this->__unset($offset);
    }


    // ..

    private function _assertImmutable()
    {
        if ( $this->isImmutable() )
            throw new exImmutable('Config is Immutable and Modification not Allowed.');
    }
}
