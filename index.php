<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

include_once('ffxiv-lodestone-api.php');

$ffxiv = ffxivLodestoneAPI::GetInstance();

$result = $ffxiv->SearchCharacterList('Undine');

echo "<pre>";
print_r($result);
echo "</pre>";
?>