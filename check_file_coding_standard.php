<?php

	$non_utf8 = array();
	$non_unix = array();
	$has_mysql = array();
	$has_short_tag = array();
	
	foreach (find_all_php('./') as $file)
	{
		set_time_limit(30);
		$tmp = file_get_contents($file);
		
		if (is_utf_8($tmp) !== true)
			$non_utf8[] = $file;
			
		if (strpos($tmp,"\r") !== false)
			$non_unix[] = $file;
			
		if (use_mysql($tmp) !== false)
			$has_mysql[] = $file;
			
		if (use_short_tag($tmp) !== false)
			$has_short_tag[] = $file;
		
		//remplace les \r\n par \n
		//$tmp = str_replace("\r\n","\n",$tmp);
		
		// file_put_contents($file,$tmp);
	}
	
	echo "\n".'Liste des fichiers non UNIX (\r) :'."\n";
	foreach($non_unix as $file)
		echo "\t".$file."\n";
		
	echo "\n".'Liste des fichiers utilisant mysql et non mysqli :'."\n";
	foreach($has_mysql as $file)
		echo "\t".$file."\n";
		
	echo "\n".'Liste des fichiers non UTF-8 :'."\n";
	foreach($non_utf8 as $file)
		echo "\t".$file."\n";
		
	echo "\n".'Liste des fichiers avec short tag php :'."\n";
	foreach($has_short_tag as $file)
		echo "\t".$file."\n";

	function is_utf_8($str)
	{
		// Created with
		// http://www.ietf.org/rfc/rfc3629.txt
		// http://abcdrfc.free.fr/rfc-vf/pdf/rfc3629.pdf
		
		static $no_utf8_bytes = array(0xc0,0xc1,0xf5,0xf6,0xf7,0xf8,
								0xf9,0xfa,0xfb,0xfc,0xfd,0xfe,0xff);
								
		// WARNING, start file with a BOM != NON UTF-8
		// So a correct UTF-8 can start with a BOM but is this case is not a BOM
		// Is this case is a ZERO WIDTH NO-BREAK SPACE, aka a non breackable space
		// BUT if we found it at start of file, this MIGHT be a signature for UCS encoding
		// BUT keep in mind is MIGHT. KEEP ALSO in mind that start a file with
		// a non-breackable space is little space :-)
		
		// Let's check if he start with a BOM ( U+FEFF or 0xEF 0xBB 0xBF)
		if(ord($str[0]) == 0xef && ord($str[1]) == 0xbb && ord($str[2]) == 0xbf)
			return false; //return 'rejected, file start with BOM sequence';
		
		$i = 0;
		//foreach all bytes when they have more
		while(isset($str[$i]))
		{
			$tmp = ord($str[$i]);
			$o = $i;
			
			//unicode char in int val ( for exemple $u_val = 0x25F => U+25F)
			$u_val = 0;
			
			// lets check is not invalid utf8 !
			if (in_array($tmp, $no_utf8_bytes))
				return false; //return 'rejected, invalid bytes, forbiden is all file';
			
			
			// one bytes for one char
			$d = 1; // decalage or nb bytes
			if (( $tmp & bindec('10000000') ) == bindec('00000000')) {
				$u_val = $tmp & bindec('01111111');
			}
			else {
				// it's two, three or four bytes for one char ?
				do{
					$d ++;
					$c1 = $tmp >> (7-$d);
					$c2 = 0xff >> (8-$d) << 1;
				}while( $d < 4 && $c1 != $c2);
				
				if ($c1 == $c2) {
					$m = 0xff >> (7-$d) << (7-$d);
					$u_val = $tmp & $m;
				}
				else
					return false; //return 'rejected, is not expected to find other value or more than 4 bytes';
			}
			
			// move to the next new URF-8 char
			$i += $d;
			
			// Not lets check the other bytes if they don't respect format
			while( ++$o < $i) {
			
				// Is the end of file ???? We expected more bytes ... error !
				if (!isset($str[$o]))
					return false; //return 'End of file reach but we expected more chars';
					
				$tmp = ord($str[$o]);
				$u_val = $u_val << 1;
				$u_val += $tmp & bindec('00111111') ;
			
				// lets check is not invalid utf8 too !
				if (in_array($tmp, $no_utf8_bytes))
					return 'rejected, invalid bytes, forbiden is all file';
				
				// lets check is it's valid rfc3629
				if (( $tmp & bindec('11000000') ) != bindec('10000000') )
					return false; //return 'This byte don\'t repesct rcf format for 2,3 or 4 bytes';
			}
			
			// now check if it's not between U+D800 and U+DFFF
			if ($u_val >= 0xD800 && $u_val <= 0xDFFF)
				return false; //return 'rejected because between D800 and DFFF if forbiden (UTF-16)';
			
		}
		
		return true; // Yeah ! We don't found invalid bytes or sequence ... So it's declared valid !
	}

	function use_short_tag($str)
	{
		return (strstr($str,'\<? ') !== false);
	}

	function use_mysql($str)
	{
		static $mysql_words = array('mysql_affected_rows', 'mysql_client_encoding', 'mysql_close', 'mysql_connect',
								'mysql_create_db', 'mysql_data_seek', 'mysql_db_name', 'mysql_db_query',
								'mysql_drop_db', 'mysql_errno', 'mysql_error', 'mysql_escape_string',
								'mysql_fetch_array', 'mysql_fetch_assoc', 'mysql_fetch_field',
								'mysql_fetch_lengths', 'mysql_fetch_object', 'mysql_fetch_row',
								'mysql_field_flags', 'mysql_field_len', 'mysql_field_name', 'mysql_field_seek',
								'mysql_field_table', 'mysql_field_type', 'mysql_free_result',
								'mysql_get_client_info', 'mysql_get_host_info', 'mysql_get_proto_info',
								'mysql_get_server_info', 'mysql_info', 'mysql_insert_id', 'mysql_list_dbs',
								'mysql_list_fields', 'mysql_list_processes', 'mysql_list_tables',
								'mysql_num_fields', 'mysql_num_rows', 'mysql_pconnect', 'mysql_ping',
								'mysql_query', 'mysql_real_escape_string', 'mysql_result', 'mysql_select_db',
								'mysql_set_charset', 'mysql_stat', 'mysql_tablename', 'mysql_thread_id',
								'mysql_unbuffered_query','$mysql_link',);
		
		foreach ($mysql_words as $tmp)
		{
			if (strstr($str,$tmp) !== false)
				return true;
		}
		return false;
	}

	function find_all_php($dir, $i = 1)
	{
		if ($i == 20)
			return;
		$result = glob($dir.'*.php');
		foreach(glob($dir.'*', GLOB_ONLYDIR | GLOB_MARK ) as $sub_dir)
			$result = array_merge($result , (array)find_all_php($sub_dir , $i+1));
		return $result;
	}
	

