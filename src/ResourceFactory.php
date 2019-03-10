<?php
namespace Poirot\Config;


class ResourceFactory
{
    /** @see http://php.net/manual/en/wrappers.php.php */
    const PHP_MEMORY = 'memory';
    const PHP_TEMP   = 'temp';


    /**
     * Create Resource From String
     *
     * @param string $content
     * @param string $sockUri
     *
     * @return resource
     * @throws \RuntimeException
     */
    function createFromString(string $content, $sockUri = self::PHP_MEMORY)
    {
        switch ($sockUri) {
            case self::PHP_MEMORY:
                $sockUri = 'php://'.self::PHP_MEMORY;
                break;
            case self::PHP_TEMP:
                $sockUri = 'php://'.self::PHP_TEMP;
                break;
            default:
                throw new \InvalidArgumentException('Invalid Stream Provided, Must memory Or temp Provided.');
        }


        if ( false === $resource = fopen($sockUri, 'br+') )
            throw new \RuntimeException('Cannot Open Stream.');
        
        if ( false === fwrite($stream, $content) )
            throw new \RuntimeException('Cannot write on stream.');

        return $resource;
    }

    /**
     * Create Resource From Uri
     *
     * @param string $sockUri
     *
     * @return resource
     * @throws \RuntimeException
     */
    function createFromUri(string $sockUri)
    {
        // knowing transport/wrapper:
        $scheme = parse_url($sockUri, PHP_URL_SCHEME);
        if (! $scheme ) {
            # /path/to/file.ext
            $scheme  = 'file';
            $sockUri = "{$scheme}://{$sockUri}";
        }


        ## Check Whether Given Scheme Wrapper Exists Or Not
        #
        $wrappers = stream_get_wrappers();
        if (! in_array($scheme, $wrappers) )
            throw new \RuntimeException(sprintf(
                'Scheme Wrapper (%s) Is Unkonw In Given (%s).'
                    , $scheme , $sockUri
            ));

        if ( false === $resource = fopen($sockUri, 'br+') )
            throw new \RuntimeException('Cannot Open Stream.');

        
        return $resource;
    }
}