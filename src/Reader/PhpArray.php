<?php
namespace Poirot\Config\Reader;

use Poirot\Config\Exceptions\exOpenResource;
use Poirot\Config\Exceptions\exParseConfig;


class PhpArray
    extends aReader
{
    protected $directory;


    /**
     * @inheritdoc
     */
    function getIterator()
    {
        $data = $this->_parseFromResource();

        return new \ArrayIterator($data);
    }


    // ..

    protected function _parseFromResource()
    {
        if ( $uri = $this->_hasUriResource() ) 
        {
            if (! is_file($uri) )
                throw new exOpenResource(sprintf(
                    'Resource (%s) not found as a File.'
                    , $uri
                ));

            // TODO, php5 and other extensions that are valid to include
            if ('php' !== $ext = pathinfo($uri, PATHINFO_EXTENSION) )
                throw new exOpenResource(sprintf(
                    'Invalid Resource (%s) provided.'
                    , $uri
                ));
                
            
            // TODO handle error 
            $data = include $uri;
        }
        else
        {
            // TODO implement this
            if (false === $data = eval($data) )
                throw new exParseConfig('Error While Trying to Parse Config Data');
        }
        
        
        if (! is_array($data) )
            throw new exParseConfig(sprintf(
                'Error While Read From (%s). Expected Array But Return (%s).'
                , $uri, gettype($data)
            ));

        
        return $data;
    }
}
