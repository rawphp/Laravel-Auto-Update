<?php

namespace RawPHP\AutoUpdate;

use Log;
use SamIT\AutoUpdater\Diff\Git;
use SamIT\AutoUpdater\Generator\PreUpdate as GeneratorPreUpdate;
use SamIT\AutoUpdater\Executor\PreUpdate as ExecutorPreUpdate;
use SamIT\AutoUpdater\Generator\Update as GeneratorUpdate;
use SamIT\AutoUpdater\Executor\Update as ExecutorUpdate;

/**
 * Class Service
 *
 * @package RawPHP\AutoUpdate
 */
class Service
{
    /** @var  string */
    protected $privateKey;
    /** @var  array */
    protected $config;
    /** @var  Git */
    protected $diff;
    /** @var  string */
    protected $preUpdateTmpFile;
    /** @var  string */
    protected $updateFile;

    /**
     * Create Auto-Update service.
     *
     * @param array $config
     */
    public function __construct( array $config )
    {
        $this->config = $config;

        $this->preUpdateTmpFile = $this->config[ 'storage' ][ 'tmp-dir' ] . $this->config[ 'storage' ] [ 'tmp-file' ];
        $this->updateFile       = $this->config[ 'storage' ][ 'update-dir' ] . $this->config[ 'storage' ] [ 'update-file' ];

        $this->initGit();
        $this->initPrivateKey();
    }

    /**
     * Initialise Git.
     */
    protected function initGit()
    {
        $this->diff = new Git(
            [
                'basePath'  => '',
                'source'    => 'HEAD~50',
                'target'    => 'HEAD',
                'formatter' => function ( $hash, $author, $message )
                {
                    $keywords = [ ];

                    return array_filter(
                        array_map(
                            'trim', array_map( function ( $line ) use ( $keywords, $hash, $author )
                                  {
                                      // check if message starts with a keyword
                                      foreach ( $keywords as $keyword )
                                      {
                                          if ( substr_compare( $keyword, $line, 0, strlen( $keywords ), TRUE ) === 0 )
                                          {
                                              if ( $keyword == 'Update translation' )
                                              {
                                                  return '#' . $line;
                                              }
                                              else
                                              {
                                                  return '-' . strlen( $line, [ '#0' => '#' ] ) . " ($author)";
                                              }
                                          }
                                      }
                                  }, explode( '\n', $message )
                                  )
                        )
                    );
                }
            ]
        );
    }

    /**
     * Initialise the private key.
     */
    protected function initPrivateKey()
    {
        if ( !isset( $config[ 'private-key' ][ 'file' ] ) || !file_exists( $config[ 'private-key' ][ 'file' ] ) )
        {
            $this->privateKey = openssl_pkey_new();
            openssl_pkey_export_to_file( $this->privateKey, $config[ 'private-key' ][ 'file' ] );
        }
        else
        {
            $this->privateKey = openssl_pkey_new( $config[ 'private-key' ][ 'file' ] );
        }
    }

    /**
     * Create Update.
     */
    public function createUpdate()
    {
        $pre = new GeneratorPreUpdate( $this->diff );
        $pre->sign( $this->privateKey );
        $pre->saveToFile( $this->preUpdateTmpFile );

        //$data = $pre->getDataForSigning();

        $update = new GeneratorUpdate( $this->diff );
        $update->sign( $this->privateKey );
        $update->saveToFile( $this->updateFile );
    }

    /**
     *
     */
    public function runUpdate()
    {
        $pre = new ExecutorPreUpdate( [ 'base_path' => $this->config[ 'base_path' ] ] );

        $pre->loadFromFile( $this->preUpdateTmpFile, openssl_pkey_get_details( $this->privateKey[ 'key' ] ) );

        if ( $pre->run() )
        {
            Log::info( 'Pre-Update Successful' );
            Log::info( $pre->getMessages() );
        }

        $update = new ExecutorUpdate( [ 'basePath' => $this->config[ 'base_path' ] ] );
        $update->loadFromFile( $this->updateFile, openssl_pkey_get_details( $this->privateKey[ 'key' ] ) );

        if ( $update->run() )
        {
            Log::info( 'Update Successful' );
            Log::info( $update->getMessages() );
        }
    }

    public function downgrade()
    {

    }
}