<?php

define( '_PHP_CONGES' , 1 );

include 'lang_fr_francais.php';
// include 'lang_en_english.php';
// include 'lang_es_espanol.php';


foreach ($LANG as $key => $val) {
	echo "\n";
	echo 'msgid "'.str_replace('"','\"',$key).'"';
	echo "\n";
	echo 'msgstr "'.str_replace(array("\n","\r",'\\','"'),array('','','\\\\','\"'),$val).'"';
	echo "\n";
}