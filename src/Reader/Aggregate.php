<?php
namespace Poirot\Config\Reader;

use Poirot\Config\Interfaces\iReader;
use Poirot\Std\Interfaces\Pact\ipFactory;


class Aggregate
    extends aReader
{
    protected $readers = [];


    /**
     * Create Aggregate Reader From Given Readers
     *
     * @param iReader[] $readers
     *
     * @return self
     */
    function __construct($readers)
    {
        $this->setReaders($readers);
    }


    /**
     * @inheritdoc
     */
    function getIterator()
    {
        $result = [];
        foreach ($this->readers as $reader)
        {
            $r = [];
            foreach ($reader as $k => $v) {
                $r[$k] = $v;
            }

            $result = array_replace_recursive($result, $r);
        }


        foreach ($result as $k => $v)
            yield $k => $v;


        return $result;
    }

    // specific methods:

    /**
     * Set Readers Clear Current Values
     *
     * @param iReader[] $readers
     *
     * @return $this
     */
    function setReaders(array $readers)
    {
        $this->readers = [];

        foreach ($readers as $r)
            $agReader->addReader($r);


        return $this;
    }

    /**
     * Add Reader
     *
     * @param iReader $reader
     *
     * @return $this
     */
    function addReader(iReader $reader)
    {
        $this->readers[] = $reader;

        return $this;
    }
}
