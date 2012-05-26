<?php
/**
 * Renderer for Open Badge Assertion output
 *
 * @author martyn@access-space.org
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

// we inherit from the XHTML renderer instead directly of the base renderer
require_once DOKU_INC.'inc/parser/xhtml.php';

/**
 * The Renderer
 */
class renderer_plugin_openbadge extends Doku_Renderer {
    var $slideopen = false;
    var $base='';
    var $tpl='';

    /**
     * the format we produce
     */
    function getFormat(){
        return 'openbadge';
    }


    /**
     * Initialize the rendering
     */
    function document_start() {
        global $ID;

        // call the parent
        parent::document_start();
        $this->hash = $_REQUEST['b'];
        
        // store the content type headers in metadata
        $headers = array(
            'Content-Type' => 'application/json',
            'instance'     => $this->instance
        );
        p_set_metadata($ID,array('format' => array('openbadge' => $headers) ));
        $this->badge = array();
    }


    /**
     * Closes the document
     */
    function document_end(){
      
         $this->doc = json_encode($this->badge);
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
