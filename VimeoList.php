<?php
  if (!defined('MEDIAWIKI')) die();
 
  $wgExtensionFunctions[] = 'wfVimeoList';
  $wgExtensionCredits['parserhook'][] = array(
    'path'            => __FILE__,
    'name'            => 'VimeoList',
    'version'         => '1.0',
    'author'          => 'Schinken',
    'url'             => 'http://www.hackerspace-bamberg.de',
    'description'     => 'Generates a vimeo list'  );
 

 // Register parser-hook
 function wfVimeoList() {
    new VimeoList();
 }
 
 
class VimeoList {

    private $cache_time = 12800;

    // Default configuration
    private $settings = array(
       'sort' => true
    );

    public function __construct() {
        global $wgParser;
        $wgParser->setHook('vimeo', array(&$this, 'hookVimeo'));
    }

    /**
    * Parser hook for Vimeo Tag
    *
    * @var string $text Text between <vimeo>-Tag
    * @var array $argv Array of attributes on the vimeo-Tag
    * @var $parser Praserobject of mediawiki
    */

    public function hookVimeo($text, $argv, $parser) {

        $parser->disableCache();

        $width = 200;
        $height = 110;

        if( isset( $argv['width'] ) ) {
            $width = (int) $argv['width'];    
        }

        if( isset( $argv['height'] ) ) {
            $height = (int) $argv['height'];    
        }

        $output = '<ul class="vimeo-list clearfix">';

        // Explode videoid's by newline - thats how its done in the 
        // included gallery-tag in mediawiki

        $lines = StringUtils::explode( "\n", $text );
        foreach ( $lines as $line ) {

            $id = (int) $line;

            // if id is empty or null
            if( !$id ) {
                continue;    
            }

            $video = $this->retrieve_vimeo_data( $id );
            if( !$video ) {
                $video = $this->get_video_error();
            }

            $title = htmlspecialchars($video['title']);
            $thumbnail = $video['thumbnail_medium'];

            $output .= '<li class="vimeo-item">';
            $output .= sprintf('<a href="//vimeo.com/%d" target="_blank" title="%s" style="background-image:url(%s); width:%dpx; height:%dpx"><span></span></a>', $id, $title, $thumbnail, $width, $height );
            $output .= '</li>';
        }


        $output .= '</ul>';

        global $wgOut, $wgScriptPath;
        $wgOut->addExtensionStyle("{$wgScriptPath}/extensions/VimeoList/VimeoList.css");

        return $output;

    }

    /**
    * Retrieve Data from Vimeo
    *
    * @var int $id Video-ID
    */

    private function retrieve_vimeo_data( $id ) {

        // Check if the video is present in cache
        $cache = $this->get_cached_video( $id );
        if( $cache !== false ) {
            return $cache;    
        }

        // Retrieve json object from vimeo api
        $url = sprintf("http://vimeo.com/api/v2/video/%d.json", $id );

        $result = @file_get_contents( $url );
        if( !$result ) {
            return false;
        }


        $result = @json_decode( $result, true );
        if( $result === null ) {
            return false;
        }

        $video = array_shift( $result );

        // Check if every needed attribute exists in array
        foreach( array('title', 'description', 'thumbnail_medium') as $key ) {
            if( !isset( $video[ $key ] ) ) {
                return false;    
            }    
        }

        // if everything worked, write that stuff to apc_cache
        $this->write_cache( $id, $video );

        return $video;

    }

    /**
    * Returns an fallback array if API doesnt respond
    */

    private function get_video_error()  {
        return array(
            'title' => 'Vimeo API Error',
            'description' => '',
            'thumbnail_medium' => "{$wgScriptPath}/extensions/VimeoList/error.jpg"
        );    
    }

    /**
    * Check if video is already cached in APC
    *
    * @var int $id Video ID
    * @return bool|array
    */

    private function get_cached_video( $id ) {

        if( !function_exists('apc_fetch') ) {
            return false;    
        }    

        $key = $this->get_cache_key( $id );
        return apc_fetch( $key );
    } 

    /**
    * Returns apc cache key
    *
    * @var int $id Video-ID
    * @return string
    */

    private function get_cache_key( $id ) {
        return sprintf('vimeo_video_%d', $id );    
    }

    /**
    * Writes video data to APC
    *
    * @var int $id Video-ID
    * @var array $data Video-Data
    */

    private function write_cache( $id, $data ) {
        
        $key = $this->get_cache_key( $id );
    
        if( function_exists('apc_store') ) {
            apc_store( $key, $data, $this->cache_time );
        }
    }
}


