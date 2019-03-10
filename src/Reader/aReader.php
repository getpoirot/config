<?php
namespace Poirot\Config\Reader;

use Poirot\Config\Exceptions\exParseConfig;
use Poirot\Config\Exceptions\exReadFromResource;


abstract class aReader
    implements iReader
    , \IteratorAggregate
{
    /** @var resource */
    protected $resource;


    /**
     * Constructor.
     *
     * @param resource $resource
     */
    function __construct(resource $resource)
    {
        if (! is_resource($resource) )
            throw new \InvalidArgumentException(sprintf(
                '(%s) given instead of stream resource.',
                \Poirot\Std\flatten($resource)
            ));

        
        $this->resource = $resource;
    }

    

    // Implement Iterator Aggregate

    /**
     * Read Through Given Stream and Parse Config 
     * 
     * @inheritdoc
     * @throws exParseConfig|exReadFromResource
     */
    abstract function getIterator();
}
