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
        // 'Accept-Language' => 'en-us,en;q=0.5',
        // 'Accept-Charset' => 'utf-8;q=0.5',
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
    foreach ( $html->find ('div.contents-frame table.contents-table1 tr td img.character-icon') as $CharList ) {
        $CharListObj = $CharList->parent()->parent()->parent()->parent()->parent()->parent()->removeNodes('tr',1);
        break;    
    }

    // Loop through each character in list
    foreach ( $CharListObj->find('table tr') as $Char ) {
      
      // Get Character ID and setup Results array.
      $CharID = $Char->find ('a[href^=/rc/character/top]');
      $CharName = $CharID[0]->plaintext;
      $CharID = substr ( $CharID[0]->href, 25, strlen ($CharID[0]->href) );
      
      // Start getting other data.
      $CharImage = $Char->find ('img.character-icon');
      $CharImage = $CharImage[0]->src;
      
      $CharMainSkill = $Char->parent()->parent()->parent()->children(1);
      $CharWorld = $Char->parent()->parent()->parent()->children(2);
      
      $Results[] = array (
        'CharacterID' => $CharID,
        'CharacterName' => $CharName,
        'CharacterIcon' => $CharImage,
        'CharacterMainSkill' => $CharMainSkill->plaintext,
        'CharacterWorld' => $CharWorld->plaintext
      );
    }
    
    return $Results;    
  }

  public function GetCharacterData ( $CharacterID ) {
    
    
  }
}
?>