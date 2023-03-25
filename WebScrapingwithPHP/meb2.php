<?php

class dbsetting {
       public $host     = "localhost";
       public $dbname   = "test";
       public $user     = "postgres";
       public $password = "ikinokta";
}

class capture extends dbsetting{

       private $linkid   = 0;
       private $cookie  = array();
       
       public function __construct(){

             $this->connect_db();
             
       }
       
       public function __destruct(){
             $this->close_db();
       }
       
       public function run() {
             try{
                    
                    //print " ILLERI ALIYORUM\n";
                   $this->illeri_al();
                    //print "OKUL KOORD ALIYORUM";
                    //$this->koord2();
                    //$this->okul_koord_al2();
                    //print " ILCERI ALIYORUM\n";
                    //$this->koord2();
                    //print " ISLEM TAMAM\n";
                    
                    //print " OKULLARI ALIYORUM\n";
                    //$this->tum_illlerin_okullarini_al();
                    //print " OKULLAR TAMAM\n";
                   // print " KOORDINATLRI ALIYORUM\n";
                    //$this->tum_okul_koord_al();
                    //print " KOORDINATLAR TAMAM\n";
                    //
             } catch (Exception $e) {
                    die($e->getMessage());
             }
       }

       public function connect_db(){
             try{
                    $this->linkid = @pg_connect("host=$this->host port=5432 dbname=$this->dbname user=$this->user password=$this->password");
                    if (! $this->linkid)
                    throw new Exception("Could not connect to PostgreSQL server.");
             } catch (Exception $e) {
                    die($e->getMessage());
             }
       }
       
       public function close_db(){
             if ($this->linkid) @pg_close($this->linkid);
       }
       
       public function getValue($xpath,$query){
             $myarr = array();
             $result = $xpath->query($query);
             for($i=0;$i<$result->length;$i++){
                    $myarr[$i] = $result->item($i)->nodeValue;
             }
             return $myarr;
       }
       
       public function getPath($nodepath){
             $doc      = new DOMDocument;
             $items = $this->dom->getElementsByTagName($nodepath);
             for ($i = 0; $i < $items->length; $i++) {
                    $domNode = $doc->importNode($items->item($i), true);
                    $doc->appendChild($domNode);
             }
             $source = $doc->saveXML($domNode);
             //iconv("WINDOWS-1254","UTF-8",$source);
             return $source;
       }      

       function chunk_content($http_response) {
             print "$http_response";
             $content = explode("\r\n\r\n",$http_response,2);
             $header_arr = explode ("\r\n",$content[0]);
             $respencoding = '';
             $cnt_type     = '';
             $charset      = '';
       
             if (!(key_exists(0, $header_arr))){
                    return null;
             }
             $statusarr    = explode (" ",$header_arr[0]);
             if (!(key_exists(0, $statusarr) and key_exists(1, $statusarr))){
                    return null;
             }
             if (!(($statusarr[0] == "HTTP/1.0" or $statusarr[0] == "HTTP/1.1") and $statusarr[1] == "200")){
                    print "RESPONSE ERROR!!!";
                    return null;
             }
       
             foreach ($header_arr as $val){
                    $rcnt = explode (":",$val,2);
                    if (trim($rcnt[0]) == 'Set-Cookie'){
                           //           print "Set-Cookie: ".trim($rcnt[1])."\n";
                           $cookiearr = explode(";",$rcnt[1]);
                           foreach ($cookiearr as $cookvar) {
                                  $rrr = explode("=",$cookvar);
                                  $cookvarkey = $rrr[0];
                                  $cookvarval = $rrr[1];
                                  $this->cookie["$cookvarkey"] = "$cookvarkey=$cookvarval";
                           }
                    }
                    if (trim($rcnt[0]) == 'Content-Encoding'){
                           //           print "Content-Encoding: ".trim($rcnt[1])."\n";
                           $respencoding = trim($rcnt[1]);
                    }
                    if (trim($rcnt[0]) == 'Content-Type'){
                           //           print "Content-Type: ".trim($rcnt[1])."\n";
                           $rcntt = explode (";",$rcnt[1],2);
                           //(Content-Type: text/html; charset=ISO-8859-9)
                           $cnt_type = trim($rcntt[0]);
                           //$rcnttt = explode ("=",$rcntt[1],2);
                           //if (trim($rcnttt[0]) == 'charset'){
                                  //$charset = trim($rcnttt[1]);
                           $charset = "UTF8";
                           //}
                    }
             }
             if ($respencoding == 'gzip'){
                    $data = $this->gzdecode($content[1]);
             }else{
                    $data = $content[1];
             }
       
             if ($charset != "UTF8"){
                    $edata = $data; //iconv($charset,"UTF8",$data);
             }else{
                    $edata = $data;
             }
             return $edata;
       }
       
       function gzdecode($string) {
             return file_get_contents('compress.zlib://data:who/cares;base64,'. base64_encode($string));
       }      
       
       function illeri_al() {
             $link = "http://www.meb.gov.tr/baglantilar/okullar/";
             
             $http_response = "";
             $url = parse_url($link);
             $fp = fsockopen($url['host'], 80, $err_num, $err_msg, 5) or print("Socket-open       failed--error: ".$err_num." ".$err_msg);
             if(!$fp) return;
             
             $head   ="GET ".$url['path']." HTTP/1.1\r\n";
             $head  .="Host: www.meb.gov.tr\r\n";
             $head  .="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
             $head  .="Accept-Encoding: gzip, deflate\r\n";
             $head  .="Cache-Control: max-age=0\r\n";
             $head  .="User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0\r\n";
             $head  .="Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.5,en;q=0.3\r\n";
             if ($this->cookie){
                    $head  .="Cookie: ".implode("; ",$this->cookie)."\r\n";
             }
             $head  .="Connection: close\r\n";
             $head  .="\r\n";
             
             fputs($fp, $head);
             
             usleep(250000);
             
             while(!feof($fp)) {
                    $http_response .= fread($fp, 128);
             }
             fclose($fp);
             
             $content = $this->chunk_content($http_response);
             
            // print $content;
             $this->illeri_kaydet($content);
             return 0;
       }
       
       public function illeri_kaydet($content) {
             $dom = new DOMDocument ();
             @$dom->loadHTML ( $content );
             usleep ( 200000 );
             $xpath = new DOMXPath ( $dom );
             print_r($dom);
             usleep ( 200000 );
             


             $entries = $xpath->query ( "//select[@id='jumpMenu5']/option" );
             //print_r($entries);
             
             /*if (! $entries->length > 0) {
                    echo "il kaydi bulunamadi\n";
                    echo "--$head--\n $content\n";
             } else {
                 */
                    foreach ( $entries as $entry ) {
                           $il_value = $entry->getAttribute ( 'value' );
                           $il_adi = $entry->nodeValue;
                           if ($il_value != '' && $il_adi != '') {
                                  $this->il_kaydet ( $il_adi, $il_value );
                           }
                    }
                    /*
             }*/
       }
       
       public function il_kaydet($il_adi, $il_value) {
             // print "İL: $il_value : $il_adi\n";
             $query = "insert into meb_il (il_adi,il_value) values ('$il_adi','$il_value')";
             $result = @pg_query ( $this->linkid, $query );
             //if ($result) {
                     print "İL: $il_value : $il_adi inserted\n";
                   $this->ilceleri_al ( $il_adi, $il_value ); 
                   $this->ilin_okullarini_al2($il_adi,$il_value);
                   
             //} else {
                    //print "İL: $il_value : $il_adi\n";
             //}
             
       }
       public function ilin_okullarini_al2($il_adi,$il_value) {
              print "Okulları ALIYORUM: $il_value : $il_adi\n";
            $link = "http://www.meb.gov.tr/baglantilar/okullar/$il_value";
             print_r($link);
             $http_response = "";
             $url = parse_url ( $link );
             $fp = fsockopen ( $url ['host'], 80, $err_num, $err_msg, 5 ) or print ("Socket-open       failed--error: " . $err_num . " " . $err_msg) ;
             if (! $fp)
                    return;
             
             $head = "GET " . $url ['path'] . "?" . $url ['query'] . " HTTP/1.1\r\n";
             $head .= "Host: www.meb.gov.tr\r\n";
             $head .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
             $head .= "Accept-Encoding: gzip, deflate\r\n";
             $head .= "Cache-Control: max-age=0\r\n";
             $head .= "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0\r\n";
             $head .= "Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.5,en;q=0.3\r\n";
             if ($this->cookie) {
                    $head .= "Cookie: " . implode ( "; ", $this->cookie ) . "\r\n";
             }
             $head .= "Connection: close\r\n";
             $head .= "\r\n";
             
             fputs ( $fp, $head );
             
             usleep ( 250000 );
             
             //print $head;
             
             while ( ! feof ( $fp ) ) {
                    $http_response .= fread ( $fp, 128 );
             }
             fclose ( $fp );
             //print $http_response;
             $content = $this->chunk_content ( $http_response );
             $this->okullari_kaydet2 ( $il_adi,$content );
             return 0;
       }

       public function ilceleri_al($il_adi,$il_value) {
              print "İLCELERI ALIYORUM: $il_value : $il_adi\n";
            $link = "http://www.meb.gov.tr/baglantilar/okullar/$il_value";
             
             $http_response = "";
             $url = parse_url ( $link );
             $fp = fsockopen ( $url ['host'], 80, $err_num, $err_msg, 5 ) or print ("Socket-open       failed--error: " . $err_num . " " . $err_msg) ;
             if (! $fp)
                    return;
             
             $head = "GET " . $url ['path'] . "?" . $url ['query'] . " HTTP/1.1\r\n";
             $head .= "Host: www.meb.gov.tr\r\n";
             $head .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
             $head .= "Accept-Encoding: gzip, deflate\r\n";
             $head .= "Cache-Control: max-age=0\r\n";
             $head .= "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0\r\n";
             $head .= "Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.5,en;q=0.3\r\n";
             if ($this->cookie) {
                    $head .= "Cookie: " . implode ( "; ", $this->cookie ) . "\r\n";
             }
             $head .= "Connection: close\r\n";
             $head .= "\r\n";
             
             fputs ( $fp, $head );
             
             usleep ( 250000 );
             
             //print $head;
             
             while ( ! feof ( $fp ) ) {
                    $http_response .= fread ( $fp, 128 );
             }
             fclose ( $fp );
             //print $http_response;
             $content = $this->chunk_content ( $http_response );
             $this->ilceleri_kaydet ( $il_adi,$content );
             $this->okullari_kaydet2 ( $il_adi,$content );
             return 0;
       }
       
       public function ilceleri_kaydet($il_adi,$content) {
             $dom = new DOMDocument ();
             @$dom->loadHTML ( $content );
             usleep ( 200000 );
             $xpath = new DOMXPath ( $dom );
             usleep ( 200000 );
             
             $entries = $xpath->query ( "//*[@id='jumpMenu6']/option" );
             if (! $entries->length > 0) {
                    echo "ilce kaydi bulunamadi\n";
                    echo "--$head--\n $content\n";
             } else
                    $ilce_sayisi = $entries->length - 1;
                   foreach ( $entries as $entry ) {
                           $ilce_value = $entry->getAttribute ( 'value' );
                           $ilce_adi = $entry->nodeValue;
                           if ($ilce_value != '' and $ilce_adi != '' and $ilce_adi != 'Tüm ilçeler') {
                                 $this->ilce_kaydet ( $il_adi, $ilce_adi, $ilce_value);
                                  
                           }
                    }
                    
       }             
       

       public function ilce_kaydet($il_adi,$ilce_adi,$ilce_value) {
             print "İL: $il_adi  İLCE:  $ilce_adi value: $ilce_value inserted\n";
             $query = "insert into meb_ilce (il_adi,ilce_adi,ilce_value) values ('$il_adi','$ilce_adi','$ilce_value')";
             $result = @pg_query ( $this->linkid, $query );
            /* if ($result) {
                    print "İL: $il_adi  İLCE:  $ilce_adi inserted\n";
             } else {
                    print "İL: $il_adi  İLCE:  $ilce_adi inserted\n";
             }*/
       }
       
      
       function okullari_kaydet2($il_adi, $content) {
              echo "$il_adi okul listesine bakiyorum\n";
              $dom = new DOMDocument ();
              @$dom->loadHTML ( $content );
              usleep ( 200000 );
              $xpath = new DOMXPath ( $dom );
              usleep ( 200000 );
              //*[@id="icerik-listesi"]/tbody

              $entries = $xpath->query ( "//*[@id='icerik-listesi']/tbody/tr" );
              //print_r($entries);
              //exit;
              if (! $entries->length > 0) {
                     echo "$il_adi okul kaydi bulunamadi\n";
                     echo "--$head--\n $content\n";
              } else {
                     $a=0;
                     foreach ( $entries as $entry ) {
                            $a++;

                            //print_r($entry->textContent);

                            if ($a == 1){
                                   continue;
                            }
                            
                            $okul_link  = "";
                            $okul_kod  = "";
                            $okul_adi   = "";
                            $okul_url   = "";
                            $bilgi_url  = "";
                            $harita_url = "";
                            
                            $okul_node = $entry->getElementsByTagName ( "td" );
                            if (! $okul_node->length > 0){
                                   continue;
                            }
                            
                            //print_r($okul_node);
                            $link1 = $okul_node->item(0)->getElementsByTagName ( "a" );
                            if ($link1->length > 0){
                                   $okul_link = $link1->item(0)->getAttribute ( 'href' );
                                   $okul_adi  = $link1->item(0)->nodeValue;
                                   print_r($okul_link);
                            }else{
                                   print "ERROR NODE:";
                                   print $okul_node->nodeValue;
                                   exit;
                                   continue;
                            }
                            
                            
                                                       
                            $link3 = $okul_node->item(1)->getElementsByTagName ( "a" );
                            if ($link3->length > 0){
                                   $bilgi_url = $link3->item(0)->getAttribute ( 'href' );
                            }
                            
                            //$parts = parse_url($bilgi_url);
                            $output_array = preg_split("#/#",$bilgi_url);
                            $okul_kod = $output_array[6];
                            

                            //print_r($okul_kod);

                            
                            $link4 = $okul_node->item(2)->getElementsByTagName ( "a" );
                            if ($link4->length > 0){
                                   $harita_url = $link4->item(0)->getAttribute ( 'data-src' );
                                   //print_r($harita_url);
                            }
                           
                            
                            print "OKULKODU:{$okul_kod}|OKULADI:{$okul_adi}|OKULLINK:{$okul_link}|BILGIURL:{$bilgi_url}|HARITAURL:{$harita_url}";
                            print "\n";
                            
                            $this->okul_kaydet2($okul_kod, $okul_adi, $okul_link, $bilgi_url, $harita_url);
                            $this->okulkoord_al($okul_link);
                     }
                            
              }

              
        }
        public function okul_kaydet2($okul_kod, $okul_adi, $okul_link, $bilgi_url, $harita_url) {
              $query = "insert into meb_okul (okul_kod,okul_adi,okul_link,bilgi_url,harita_url) values ('{$okul_kod}','{$okul_adi}','{$okul_link}','{$bilgi_url}','{$harita_url}')";
              $result = @pg_query ( $this->linkid, $query );
              //if ($result) {
                     print "OKUL: $okul_adi inserted\n";
             // } else {
               //      print "OKUL: $okul_kod - $okul_adi  not inserted\n";
              //}
       }
       public function okulkoord_al($okul_link){
              $html = file_get_contents("$okul_link/tema/harita.php");
              $html = tidy_repair_string($html);
              
              $doc = new DomDocument();
              $doc->loadHtml($html);
              //print_r($html);
              $xpath = new DomXPath($doc);
              $entries = $xpath->query ( "//iframe" );
              
             
              if (! $entries->length > 0) {
                     echo "okul kord bulunamadi\n";
                     
              } else {
                     
                     foreach ( $entries as $entry ) {
                            
                            $denemee  = "";
                            
                                   $denemee = $entries->item(0)->getAttribute ( 'src' );
                                   preg_match ( "/q=([^\&]*).*[\&]{0,1}/", $denemee, $output_array );
			
			              $koordinat_yx = $output_array [1];
                                   print_r($koordinat_yx);
                                   $latlng = explode(',',$koordinat_yx);
                                   $lat = $latlng[0];
                                   $lng = $latlng[1];
                                   $way_wkt = "POINT({$lng} {$lat})";
                                   print_r($way_wkt);
                                   //print ("--- $lat , $lng ---\n");
                                   $this->koordinat_kaydet($koordinat_yx,$way_wkt,$okul_link) ;
                            break;
                     }
              }
       }

       public function koordinat_kaydet($koordinat_yx,$way_wkt,$okul_link) {
              //$query = "INSERT INTO meb_okul (geog) VALUES (ST_GeographyFromText('$way_wkt'))";
              $query = "update meb_okul set geog = ST_SetSRID(ST_MakePoint($koordinat_yx),4326) ,way =ST_GeographyFromText('$way_wkt') where okul_link = '{$okul_link}'";
              $result = @pg_query ( $this->linkid, $query );
              //if ($result) {
                    print "OKUL updated\n";
                 //    return true;
              //} else {
                     //print "OKUL: $okul_kod  not updated\n";
                     //return false;
              //
        }

        //public function ilce_kaydet($il_adi,$ilce_adi,$ilce_value) {
              //print "İL: $il_adi  İLCE:  $ilce_adi value: $ilce_value inserted\n";
           //   $query = "insert into meb_ilce (il_adi,ilce_adi,ilce_value) values ('$il_adi','$ilce_adi','$ilce_value')";
            //  $result = @pg_query ( $this->linkid, $query );
             /* if ($result) {
                     print "İL: $il_adi  İLCE:  $ilce_adi inserted\n";
              } else {
                     print "İL: $il_adi  İLCE:  $ilce_adi inserted\n";
              }*/
        //}
        
}
$timeout = 5;
$old = ini_set('default_socket_timeout', $timeout);
$sys = new capture();
$sys->run();
ini_set('default_socket_timeout', $old);
       
?>
       