<?php
namespace Poirot\Config\Interfaces;


interface iReader
    extends \Traversable
{
    /**
     * Constructor.
     *
     * @param resource $resource
     */
    function __construct($resource);

    
    /**
     * Is Stream Alive?
     *
     * - resource availability
     *
     * @return boolean
     */
    function isAlive();
}
