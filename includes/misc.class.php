<?php

/* misc class */


class HTTPLocale
{
  var $language;
  var $country;

  function HTTPLocale()
  {
    $data = array_map("trim", explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]));
    $data = array_map("trim", explode(";", $data[0]));
    $data = array_map("trim", explode("-", $data[0])); //get first pair of language-country
    
    $this->language = strtolower($data[0]);
	$this->country  = isset($data[1]) ? strtolower($data[1]) : $this->language;
  }
}



?>
