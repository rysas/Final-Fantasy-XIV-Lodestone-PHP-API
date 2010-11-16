<?php

error_reporting(E_ALL);
ini_set('display_errors',1);
ini_set('memory_limit', '1024M');

include_once('ffxiv-lodestone-api.php');

function SimpleXMLElement_append($key, $value) {
    // check class
    if ((get_class($key) == 'SimpleXMLElement') && (get_class($value) == 'SimpleXMLElement')) {
        // check if the value is string value / data
        if (trim((string) $value) == '') {
            // add element and attributes
            $element = $key->addChild($value->getName());
            foreach ($value->attributes() as $attKey => $attValue) {
                $element->addAttribute($attKey, $attValue);
            }
            // add children
            foreach ($value->children() as $child) {
                SimpleXMLElement_append($element, $child);
            }
        } else {
            // set the value of this item
            $element = $key->addChild($value->getName(), trim((string) $value));
        }
    } else {
        // throw an error
        throw new Exception('Wrong type of input parameters, expected SimpleXMLElement');
    }
}

if(isset($_GET)&&isset($_GET['request'])) {
  $ffxiv = ffxivLodestoneAPI::GetInstance();
  
  switch($_GET['request']) {
    case 'search':
      if(!isset($_GET['characterName'])) {
        die('please specify a character name.');  
      }
      $result = $ffxiv->SearchCharacterList($_GET['characterName'], (isset($_GET['server'])?$_GET['server']:false), (isset($_GET['class'])?$_GET['class']:false));
      switch((isset($_GET['responseType'])?$_GET['responseType']:'xml')) {
        case 'json': echo json_encode($result); break;
        case 'xml': default: $xmlResp = new SimpleXMLElement('<Response></Response>'); foreach($result as $res) SimpleXMLElement_append($xmlResp,$res); echo $xmlResp->asXML(); break;       
      }
      break;

    case 'characterData':
      if(!isset($_GET['characterID'])) {
        die('please specify a character id.');  
      }
      $result = $ffxiv->GetCharacterData($_GET['characterID']);
      switch((isset($_GET['responseType'])?$_GET['responseType']:'xml')) {
        case 'json': echo json_encode($result); break;
        case 'xml': default: $xmlResp = new SimpleXMLElement('<Response></Response>'); foreach($result as $res) SimpleXMLElement_append($xmlResp,$res); echo $xmlResp->asXML(); break;       
      }
      break;
      
    case 'characterBiography':
      if(!isset($_GET['characterID'])) {
        die('please specify a character id.');  
      }
      $result = $ffxiv->GetCharacterBiography($_GET['characterID']);
      switch((isset($_GET['responseType'])?$_GET['responseType']:'xml')) {
        case 'json': echo json_encode($result); break;
        case 'xml': default: $xmlResp = new SimpleXMLElement('<Response></Response>'); SimpleXMLElement_append($xmlResp,$result); echo $xmlResp->asXML(); break;       
      }
      break;
        
    case 'characterRecentBlogEntries':
      if(!isset($_GET['characterID'])) {
        die('please specify a character id.');  
      }
      $result = $ffxiv->GetCharacterRecentBlogEntries($_GET['characterID']);
      switch((isset($_GET['responseType'])?$_GET['responseType']:'xml')) {
        case 'json': echo json_encode($result); break;
        case 'xml': default: $xmlResp = new SimpleXMLElement('<Response></Response>'); foreach($result as $res) SimpleXMLElement_append($xmlResp,$res); echo $xmlResp->asXML(); break;       
      }
      break;  
      
    case 'characterFollowingCount':
      if(!isset($_GET['characterID'])) {
        die('please specify a character id.');  
      }
      $result = $ffxiv->GetCharacterFollowingCount($_GET['characterID']);
            
      switch((isset($_GET['responseType'])?$_GET['responseType']:'xml')) {
        case 'json': echo json_encode($result); break;
        case 'xml': default: $xmlResp = new SimpleXMLElement('<Response></Response>'); SimpleXMLElement_append($xmlResp,$result); echo $xmlResp->asXML(); break;       
      }
      break;    
    
    case 'characterFollowerCount':
      if(!isset($_GET['characterID'])) {
        die('please specify a character id.');  
      }
      $result = $ffxiv->GetCharacterFollowerCount($_GET['characterID']);
      switch((isset($_GET['responseType'])?$_GET['responseType']:'xml')) {
        case 'json': echo json_encode($result); break;
        case 'xml': default: $xmlResp = new SimpleXMLElement('<Response></Response>'); SimpleXMLElement_append($xmlResp,$result); echo $xmlResp->asXML(); break;       
      }
      break;

    case 'characterHistory':
      if(!isset($_GET['characterID'])) {
        die('please specify a character id.');  
      }
      $result = $ffxiv->GetCharacterHistory($_GET['characterID']);
      switch((isset($_GET['responseType'])?$_GET['responseType']:'xml')) {
        case 'json': echo json_encode($result); break;
        case 'xml': default: $xmlResp = new SimpleXMLElement('<Response></Response>'); foreach($result as $res) SimpleXMLElement_append($xmlResp,$res); echo $xmlResp->asXML(); break;       
      }
      break;  
      
    default:
        die('request type not found');
        break;
  }
}
?>