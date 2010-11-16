<?php
/* Final Fantasy XIV Lodestone API
 * http://rysas.net/ffxiv/
 * Damian Miller (damian@offthewallmedia.com)
 * Updated: 11/6/2010
 */

include_once ('simple_html_dom.php');
 
class ffxivLodestoneAPI {
  
  // Version Number
  private static $ffxivLodestoneAPIVersion = '0.1 Alpha';
  
  // Singleton instance.
  private static $instance;
  
  // Final Fantasy Config Vars
  private $LodestoneURL = "http://lodestone.finalfantasyxiv.com";
  public $ServerList = array(
    2 => 'Cornelia',
    3 => 'Kashuan',
    4 => 'Gysahl',
    5 => 'Mysidia',
    6 => 'Istory',
    7 => 'Figaro',
    8 => 'Wutai',
    9 => 'Trabia',
    10 => 'Lindblum',
    11 => 'Besaid',
    12 => 'Selbina',
    13 => 'Rabanastre',
    14 => 'Bodhum',
    15 => 'Melmond',
    16 => 'Palamecia',
    17 => 'Saronia',
    18 => 'Fabul',
    19 => 'Karnak'
  );

  public $ClassList = array(
    2 => 'Hand-to-Hand',
    3 => 'Sword',
    4 => 'Axe',
    7 => 'Archery',
    8 => 'Polearm',
    22 => 'Thaumaturgy',
    23 => 'Conjury',
    29 => 'Woodworking',
    30 => 'Smithing',
    31 => 'Armorcraft',
    32 => 'Goldsmithing',
    33 => 'Leatherworking',
    34 => 'Clothcraft',
    35 => 'Alchemy',
    36 => 'Cooking',
    39 => 'Mining',
    40 => 'Botany',
    41 => 'Fishing',
  );  
  
  public static function GetInstance ( ) 
  {
    if ( !isset ( self::$instance ) ) {
      $c = __CLASS__;
      self::$instance = new $c;
    }
    return self::$instance;
  }
  
  public function GetHTMLObject ( $url ) {

    $context = array(
      'http' => array (
        'header' => 'Accept-Language: en-us,en;q=0.5\r\nAccept-Charset: utf-8;q=0.5\r\n',
        'user_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.08) Gecko/20100914 Firefox/3.6.10'
      )
    );
    
    $context = stream_context_create ( $context );
    
    return file_get_html( $this->LodestoneURL . $url, false, $context );
  }

  // Search 
  public function SearchCharacterList ( $CharacterName, $Server = false, $Class = false ) {
    $CharListObj = null;
    $Results = array ();
    
    $html = $this->GetHTMLObject ( '/rc/search/search?tgt=77&q=' . urlencode($CharacterName) . (($Class)?'&cms='.$Class:false) . (($Server)?'&cw='.$Server:false)  );
    
    // Find the character list... kind of blah but the DOM Library has limitations, so work around them!  
    $CharListObj = $html->find ('div.contents-frame table.contents-table1 tr td img.character-icon', 0)->parent()->parent()->parent()->parent()->parent()->parent()->removeNodes('tr',1);

    // Loop through each character in list
    foreach ( $CharListObj->find('table tr') as $Char ) {
      
      $Result = new SimpleXMLElement("<Character></Character>");
      
      // Get Character ID and setup Results array.
      $CharID = $Char->find ('a[href^=/rc/character/top]', 0);
      $Result->CharName = $CharID->plaintext;
      $Result->CharacterID = substr ( $CharID->href, 25, strlen ($CharID->href) );
      
      // Start getting other data.
      $Result->CharacterImage = $Char->find ('img.character-icon', 0)->src;
      
      $Result->CharacterMainSkill = $Char->parent()->parent()->parent()->children(1)->plaintext;
      $Result->CharacterWorld = $Char->parent()->parent()->parent()->children(2)->plaintext;

      $Results[] = $Result;
    }
    
    return $Results;    
  }

  public function GetCharacterData ( $CharacterID ) {
    $Result = new SimpleXMLElement("<Character></Character>");
    
    $html = $this->GetHTMLObject ( '/rc/character/status?cicuid=' . $CharacterID )->find('div.contents-frame-inner', 0);
    
    $Result->CharacterName = $html->find('div#charname div', 0)->plaintext;
    
    $ProfileTable = $html->find('table#profile-table tbody', 0);
    
    $Result->CharacterRace = rtrim($ProfileTable->children(0)->plaintext);
    $Result->CharacterCurrentSkill = rtrim($ProfileTable->children(1)->children(1)->plaintext);
    $Result->CharacterNamesday = rtrim($ProfileTable->children(2)->children(2)->plaintext);
    $Result->CharacterGuardian = rtrim($ProfileTable->children(3)->children(1)->plaintext);
    $Result->CharacterStartingCity = rtrim($ProfileTable->children(4)->children(2)->plaintext);
    $Result->CharacterPhysicalLevel = rtrim($ProfileTable->children(5)->children(2)->plaintext);
    $Result->CharacterExperiencePoints = rtrim($ProfileTable->children(6)->children(2)->plaintext);
    $Result->CharacterHP = rtrim($ProfileTable->children(7)->children(2)->plaintext);
    $Result->CharacterMP = rtrim($ProfileTable->children(8)->children(2)->plaintext);
    $Result->CharacterTP = rtrim($ProfileTable->children(9)->children(2)->plaintext);
    
    $AttributesTable = $html->find('div.floatLeft table tbody', 0);
    
    $Result->CharacterAttributes->Strength = rtrim($AttributesTable->children(0)->children(1)->plaintext);
    $Result->CharacterAttributes->Vitality = rtrim($AttributesTable->children(1)->children(1)->plaintext);
    $Result->CharacterAttributes->Dexterity = rtrim($AttributesTable->children(2)->children(1)->plaintext);
    $Result->CharacterAttributes->Intelligence = rtrim($AttributesTable->children(3)->children(1)->plaintext);
    $Result->CharacterAttributes->Mind = rtrim($AttributesTable->children(4)->children(1)->plaintext);
    $Result->CharacterAttributes->Piety = rtrim($AttributesTable->children(5)->children(1)->plaintext);

    $ElementsTable = $html->find('div.floatRight table tbody', 0);
    
    $Result->CharacterElements->Fire = rtrim($ElementsTable->children(0)->children(1)->plaintext);
    $Result->CharacterElements->Water = rtrim($ElementsTable->children(1)->children(1)->plaintext);
    $Result->CharacterElements->Lightning = rtrim($ElementsTable->children(2)->children(1)->plaintext);
    $Result->CharacterElements->Wind = rtrim($ElementsTable->children(3)->children(1)->plaintext);
    $Result->CharacterElements->Earth = rtrim($ElementsTable->children(4)->children(1)->plaintext);
    $Result->CharacterElements->Ice = rtrim($ElementsTable->children(5)->children(1)->plaintext);
    
    foreach ( $html->find('th.mainskill-label', 0)->parent()->parent()->children() as $Class ) {
    	$Result->CharacterClass->{$Class->children(0)->plaintext}->Rank = (int) $Class->children(1)->children(0)->children(0)->children(2)->plaintext;
		  $Result->CharacterClass->{$Class->children(0)->plaintext}->SkillPoints = str_replace('-','0',$Class->children(2)->children(0)->children(0)->children(2)->plaintext);
    }
    
    return $Result;
  }

  public function GetCharacterBiography ( $CharacterID ) {
    $Result = new SimpleXMLElement("<Character></Character>");
    
    $html = $this->GetHTMLObject ( '/rc/character/top?cicuid=' . $CharacterID )->find('div.floatRight table.contents-table1', 0)->removeNodes('tr',1);
    $Result->Biography = $html->find('td',0)->plaintext;  
    return $Result;
  }

  public function GetCharacterRecentBlogEntries ( $CharacterID ) {
    $Results = array();
    
    $html = $this->GetHTMLObject ( '/rc/character/top?cicuid=' . $CharacterID )->find('div.floatRight table.contents-table1', 1)->removeNodes('tr',2);
    
    if( $html->find('td',0)->plaintext == "There are currently no entries to display.")
      return $Results;
      
    foreach ( $html->find('td') as $BlogPost ) {
      $Result = new SimpleXMLElement("<BlogPost></BlogPost>");
      $Result->PostName = substr($BlogPost->find('a',0)->plaintext, 0, strpos($BlogPost->find('a',0)->plaintext, '&nbsp;'));
      $Result->PostCommentCount = (int) substr(substr($BlogPost->find('a',0)->plaintext,strpos($BlogPost->find('a',0)->plaintext, '&nbsp;')+7),0,-1);
      $Result->PostHref = $this->LodestoneURL . $BlogPost->find('a',0)->href;
       
      $Results[] = $Result;
    }
      
    return $Results;
  }
  
  public function GetCharacterFollowingCount ( $CharacterID ) {
    $Result = new SimpleXMLElement("<Character></Character>");
    $Result->FollowingCount = (int) substr($this->GetHTMLObject ( '/rc/character/top?cicuid=' . $CharacterID )->find('div.ministatus-inner', 0)->find('tr',2)->plaintext,9); 
    return $Result;
  }
  
  public function GetCharacterFollowerCount ( $CharacterID ) {
    $Result = new SimpleXMLElement("<Character></Character>");
    $Result->FollowerCount = (int) substr($this->GetHTMLObject ( '/rc/character/top?cicuid=' . $CharacterID )->find('div.ministatus-inner', 0)->find('tr',3)->plaintext,9); 
    return $Result;
  }
  
  public function GetCharacterHistory ( $CharacterID, $page = 1 ) {
    $Results = array();
    
    $html = $this->GetHTMLObject ( '/rc/character/playlog?num=100&cicuid=' . $CharacterID . '&p=' . $page )->find('div.community-inner div.contents-headline');
    
    if($page > 1)
      $i = $page * 100;
    else
      $i = 0;

    foreach ( $html as $History ) {
      $Result = new SimpleXMLElement("<HistoryItem></HistoryItem>");
      $Result->Title = $History->plaintext;
      $Result->Type = substr($History->class,0, -18);      
      $Results[$i] = $Result;
      $i++;
    }
    
    $html = $this->GetHTMLObject ( '/rc/character/playlog?num=100&cicuid=' . $CharacterID . '&p=' . $page )->find('div.community-inner div.contents-frame');
    
    if($page > 1)
      $i = $page * 100;
    else
      $i = 0;
      
    foreach ( $html as $History ) {
      $Results[$i]->Description = $History->children(0)->children(0)->children(0)->children(0)->plaintext;
      $Results[$i]->Time = $History->children(0)->children(0)->children(0)->children(1)->plaintext;
      $i++;
    }    
    
    $html = $this->GetHTMLObject ( '/rc/character/playlog?num=100&cicuid=' . $CharacterID . '&p=' . $page )->find('td.common-pager-index');
    
    if($page <= 1) { 
      $count = count($html);
      if($count>1) {
        for($i = $page+1; $i <= $count; $i++) {
          array_merge($Results,$this->GetCharacterHistory ( $CharacterID, $i) );
        }
      }
    }
    return $Results;    
  }
}

class ffxivLodestoneAPIError extends Exception {
  
  public function apiError($resp) {
    $xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= "<Response status=\"error\"></Response>\n";
    return $xml;
  }
}
?>