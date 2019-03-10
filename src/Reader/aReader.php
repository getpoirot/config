<?php
namespace Poirot\Config\Reader;

use Poirot\Config\Interfaces\iReader;
use Poirot\Config\Exceptions\exParseConfig;
use Poirot\Config\Exceptions\exReadFromResource;


abstract class aReader
    implements \IteratorAggregate
    , iReader
{
    /** @var resource */
    protected $resource;


    /**
     * Constructor.
     *
     * @param resource $resource
     */
    function __construct($resource)
    {
        if (! is_resource($resource) )
            throw new \InvalidArgumentException(sprintf(
                '(%s) given instead of stream resource.',
                \Poirot\Std\flatten($resource)
            ));

        
        $this->resource = $resource;
    }


    /**
     * Is Stream Alive?
     *
     * - resource availability
     *
     * @return boolean
     */
    function isAlive()
    {
        return is_resource($this->resource);
    }


    // Implement Iterator Aggregate

    /**
     * Read Through Given Stream and Parse Config 
     * 
     * @inheritdoc
     * @throws exParseConfig|exReadFromResource
     */
    abstract function getIterator();


    // ..

    /**
     * Read Data From Stream
     *
     * - if $inByte argument not set, read entire stream
     *
     * @param int  $inByte Read Data in byte
     *
     * @throws \Exception Error On Read Data
     * @return string
     */
    protected function _read($inByte = null)
    {
        $inByte = ($inByte === null) ? -1 : (int) $inByte;

        $stream = $this->resource;
        $data   = stream_get_contents($stream, $inByte);
        if (false === $data)
            throw new exReadFromResource('Cannot read stream.');

        return $data;
    }

    /**
     * Gets line from stream resource up to a given delimiter
     *
     * Reading ends when length bytes have been read,
     * when the string specified by ending is found
     * (which is not included in the return value),
     * or on EOF (whichever comes first)
     *
     * ! does not return the ending delimiter itself
     *
     * @param string $ending
     * @param int    $inByte
     *
     * @return string|null
     */
    protected function _readLine($ending = "\n", $inByte = null)
    {
        $inByte = ($inByte === null) ? 1024 : $inByte;

        $stream = $this->resource;
        if ($ending == "\r" || $ending == "\n" || $ending == "\r\n") {
            // php7 stream_get_line is too slow!!!! so i use default fgets instead in this case
            $data = fgets($stream, $inByte);
            if (false !== $i = strpos($data, $ending))
                ## found ending in string
                $data = substr($data, 0, $i);
        } else {
            // does not return the delimiter itself
            $data = stream_get_line($stream, $inByte, $ending);
        }

        if (false === $data)
            return null;

        return $data;
    }

    /**
     * Is Resource Assciated To a Uri Resource like File?
     *
     * @return bool
     */
    protected function _hasUriResource()
    {
        if (! $this->isAlive() )
            return false;

        $meta = stream_get_meta_data($this->resource);
        if ( in_array($meta['uri'], ['php://memory', 'php://temp']) )
            return false;

        
        return $meta['uri'] ?: false;
    }
    
    /**
     * Is Stream Positioned At The End?
     *
     * @return boolean
     */
    protected function _isEOF()
    {
        return feof($this->resource);
    }
}
