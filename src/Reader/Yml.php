<?php
namespace Poirot\Config\Reader;


class Yml
    extends aReader
{
    protected $directory;
    protected $ymlParser;


    /**
     * Constructor.
     *
     * @param resource $resource
     */
    function __construct($resource)
    {
        parent::__construct($resource);

        if ( function_exists('yaml_parse') )
            $this->ymlParser = new YmlLib($resource);

        // TODO other YML Parser Option
        throw new \RuntimeException('Unable to find suitable YML Parser.');
    }


    /**
     * @inheritdoc
     */
    function getIterator()
    {
        return $this->ymlParser->getIterator();
    }
}
