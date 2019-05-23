<?php
namespace Poirot\Config\Reader;

use Poirot\Config\ResourceFactory;
use Poirot\Config\Exceptions\exParseConfig;


class YmlLib
    extends aReader
{
    protected $directory;


    /**
     * @inheritdoc
     */
    function getIterator()
    {
        $data = $this->_parseYmlFromResource();
        foreach ($data as $k => $v)
            yield $k => $v;

        return $data;
    }


    // ..

    protected function _parseYmlFromResource()
    {
        $this->directory = ( $uri = $this->_hasUriResource() ) ? dirname($uri) : false;

        $config = $this->decode($this->_read());
        return $this->process($config);
    }

    protected function decode($data)
    {
        $config = yaml_parse($data);

        if (null !== $config && ! is_array($config)) {
            throw new exParseConfig(
                'Invalid YML configuration; did not return an array or object'
            );
        }

        return $config;
    }

    /**
     * Process the array for @include
     *
     * @param  array $data
     * @return array
     * @throws \RuntimeException
     */
    protected function process(array $data)
    {
        foreach ($data as $key => $value)
        {
            if (is_array($value))
                $data[$key] = $this->process($value);

            if (trim($key) === '@include') {
                if ($this->directory === null)
                    throw new \RuntimeException('Cannot process @include statement for a YML string');

                $reader = new self( ResourceFactory::createFromUri($this->directory.'/'.$value) );
                unset($data[$key]);
                $data = array_replace_recursive($data, iterator_to_array($reader));
            }
        }


        return $data;
    }
}
