<?php
namespace Poirot\Config\Reader;

use Poirot\Config\ResourceFactory;
use Poirot\Config\Exceptions\exParseConfig;
use Poirot\Std\Type\StdString;


class Json
    extends aReader
{
    protected $directory;


    /**
     * @inheritdoc
     */
    function getIterator()
    {
        $data = $this->_parseJsonFromResource();
        foreach ($data as $k => $v)
            yield $k => $v;

        return $data;
    }


    // ..

    protected function _parseJsonFromResource()
    {
        $this->directory = ( $uri = $this->_hasUriResource() ) ? dirname($uri) : false;

        $config = $this->decode($this->_read());
        return $this->process($config);
    }

    /**
     * Decode JSON configuration.
     *
     * Determines if ext/json is present, and, if so, uses that to decode the
     * configuration. Otherwise, it uses zend-json, and, if that is missing,
     * raises an exception indicating inability to decode.
     *
     * @param string $data
     * @return array
     * @throws \RuntimeException for any decoding errors.
     */
    protected function decode($data)
    {
        $config = json_decode($data, true);

        if (null !== $config && ! is_array($config)) {
            throw new exParseConfig(
                'Invalid JSON configuration; did not return an array or object'
            );
        }

        if (null !== $config)
            return $config;

        if (JSON_ERROR_NONE === json_last_error())
            return $config;


        throw new exParseConfig(json_last_error_msg());
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

            elseif (trim($key) === '@include') {
                if ($this->directory === null)
                    throw new \RuntimeException('Cannot process @include statement for a JSON string');

                try {
                    $value   = StdString::of($value)->stripPrefix('./');
                    $include = (! $value->isStartWith('/') )
                        // relative path
                        ? $this->directory . '/' . $value
                        // absolute path
                        : $value;

                    $reader  = new self( ResourceFactory::createFromUri($include) );
                    unset($data[$key]);
                    $data = array_replace_recursive($data, iterator_to_array($reader));

                } catch (\Exception $e) {
                    throw new \RuntimeException(sprintf('Can`t include "%s".', $include));
                }
            }
        }


        return $data;
    }
}
