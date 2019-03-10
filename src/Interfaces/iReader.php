<?php
namespace Poirot\Config;


interface iReader
    extends \Traversable
{
    /**
     * Constructor.
     *
     * @param resource $resource
     */
    function __construct($resource);
}
