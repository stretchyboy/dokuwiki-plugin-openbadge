<?php
/**
 * OPENBADGE Plugin: Display a Wiki page as OPENBADGE slideshow presentation
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_openbadge extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 152;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('----+ *openbadge(?: [ a-zA-Z0-9_]*)?-+\n.*?\n----+',$mode,'plugin_openbadge');
    }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        // get lines and additional class
        $lines = explode("\n",$match);
        array_pop($lines);
        $class = array_shift($lines);
        $class = preg_replace('/^----+ *openbadge/','',$class);
        $class = trim($class,'- ');

        $data = array();
        $data['classes'] = $class;
        //$data['limit'] = 1000;
        
        
        $data["salt"] = "somethingclever";
        $aOrigin = parse_url(getBaseURL(true));
        $data["origin"] = $aOrigin['scheme']."://".$aOrigin['host'];
        $data['issuer_name'] = $conf['title'];
        $data['contact'] = $conf['contact'];
        $data['earning'] = false;
        $data['eventtype'] = 'course';
        
        // parse info
        foreach ( $lines as $line ) {
            // ignore comments
            $line = preg_replace('/(?<![&\\\\])#.*$/','',$line);
            $line = str_replace('\\#','#',$line);
            $line = trim($line);
            if(empty($line)) continue;
            $aLine = preg_split('/\s*:\s*/',$line);
            $field = strtolower(array_shift($aLine));
            $value = join(':',$aLine);
            
            
            $logic = 'OR';
            // handle line commands (we allow various aliases here)
            switch($field){                
                    
                case 'recipients':
                        $emails = explode(',',$value);
                        foreach($emails as $email){
                            $email = trim($email);
                            if(!$email) continue;
                            $data['emails'][] = $email;
                        }
                    break;
                case "name":
                case "description":
                case "issuer_name":
                case "org":
                case "contact":
                case "eventtype":
                    $data[$field] = $value;
                    break;
                case "evidence":
                case "criteria":
                case "origin":
                    $sLink = $value;
                    $aLink = parse_url($sLink);
                    
                    if(isset($aLink['scheme']) && isset($aLink['host']))
                    {
                      $sURL = $sLink;
                    }
                    else
                    {
                      $sURL = wl($sLink);
                    }
                    $data[$field] = $sURL;
                    break;
                case "image":
                    $sLink = $value;
                    $aLink = parse_url($sLink);
                    
                    if(isset($aLink['scheme']) && isset($aLink['host']))
                    {
                      $sURL = $sLink;
                    }
                    else
                    {
                      $sURL = ml($sLink);
                    }
                    $data[$field] = $sURL;
                    break;
                case "earning":
                    $data[$field] = ($value == 'true');
                    break;
                    
                case "expires":
                case "issued_on":
                    $data[$field] = strtotime($value);
                    break;
            }
        }   
        
        $iErrors = 0;
        
        if(!isset($data['recipients']) && !isset($data['issued_on']) && !$data['earning'])
        {
          //badge is being earnt by being here
          //and if think that can only be a logged in thing?
           msg($this->getLang('no_issued_on_earnt'),-1);
           
           $iErrors ++;    
           
          //should we add a javascript hook for a on page task being completed?
        }
        
        $aRequired = array("name", "salt", "image", "description","criteria",
          "issuer_name", "origin", "contact");
        foreach($aRequired as $sRequired)
        {
          if(!isset($data[$sRequired]) || empty($data[$sRequired]))
          {
            msg(sprintf($this->getLang('required_missing'),$sRequired),-1);
            $iErrors ++;    
            
          }
        }
    
    
    
    
        if($iErrors > 0)
        {
          return array();
          }
      return $data;
      
    }
    
    

    /**
     * Create output
     */
    function render($format, &$renderer, $data) {
        global $ID;
        if (count($data) === 0)
        {
          return true;
        }
        
        $renderer->nocache();
        
        if($format == 'metadata')
        {
          //$R->meta['']] = true;
          return true;
        }
        
        
        if($format == 'xhtml')
        {
          $renderer->doc .= '<div class="openbadge">';
               
          $sUserEmail = $_REQUEST['recipient_email'];
          
          //if earning
          if($sUserEmail && in_array($sUserEmail, $data['emails']))
          {
            $renderer->doc .= '<div class="openbadge_holder">
            <img src="'.$data['image'].'" alt="'.$data['name'].'" width="150" height="150" />
            </div>';
            
            $renderer->doc .= '<div class="openbadge_result">';
            $renderer->doc .= $this->getLang('connecting').'</div>';
            
            //exportlink($id='',$format='raw',$more='',$abs=false,$sep='&amp;')
            $instance = md5($data['name'].$sUserEmail);
            //OpenBadges.issue(assertions, callback)
            $sAssertion = exportlink($ID, "openbadge","b=".$instance, true);
            $renderer->doc .= '<script src="http://beta.openbadges.org/issuer.js"></script>';
            
            $renderer->doc .= '<input id="openbadgeassertion" type="hidden" value="'.$sAssertion.'" />';
           
          }
          else
          {
            if($sUserEmail)
            {
              msg($this->getLang('emailmissing'),-1);
            
            }
            
             $renderer->doc .= '<div class="openbadge_text"><p>';
             $renderer->doc .= sprintf($this->getLang('invite'),$data['eventtype'], $data['name'] ).'</p></div>';
             
            
             
             
             $renderer->doc .= '<div class="openbadge_holder">
             <img src="'.$data['image'].'" alt="'.$data['name'].'" width="150" height="150" />
             </div>';
             
             $renderer->doc .= '<div class="openbadge_form"><form method="POST"><p><span class="openbadge_name">'.
             '</span><br/>
             '.$this->getLang('email_prompt').' <input name="recipient_email" /><br/>
             <input type="submit" value="'.$this->getLang('claim').'" />
             </p></form></div>';
             
             
          }
          $renderer->doc .= '</div>';
          $renderer->doc .= '<div class="clearer"></div>';     
          return true;
        }
        
        if($format == 'openbadge')
        {
          foreach($data['emails'] as $sEmail)
          {
          
            $instance = md5($data['name'].$sEmail);
            if($renderer->hash == $instance)
            {
              //<algorithm>$<hash(email + salt)>
              $recipienthash = 'md5$'.md5($sEmail.$data['salt']);
              //$recipienthash = crypt();
              //echo "\n<br><pre>\nrecipienthash  =" .var_export($recipienthash , TRUE)."</pre>";
              
              $renderer->badge = array(
                "recipient"=> $recipienthash,
                "salt"=> $data["salt"],
                "evidence"=> $data["evidence"],
                "expires"=> $data["expires"],
                "issued_on"=> $data["issued_on"],
                "badge"=> array(
                  "version"=> "0.5.0",
                  "name"=> $data['name'],
                  "image"=> $data['image'],
                  "description"=> $data['description'],
                  "criteria"=> $data['criteria'],
                  "issuer"=> array(
                    "origin"=> $data["origin"],
                    "name"=> $data['issuer_name'],
                    "org"=> $data['org'],
                    "contact"=> $data['contact']
                  )
                )
              );
            }
          }
          return true;
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
