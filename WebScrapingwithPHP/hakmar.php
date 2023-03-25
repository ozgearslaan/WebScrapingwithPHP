<?php

class dbsetting {
       public $host     = "localhost";
       public $dbname   = "hakmar";
       public $user     = "postgres";
       public $password = "***";
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
     
       
       function illeri_al() {
                $ch = curl_init();
                $url = 'https://www.cagdasmarketler.com/tr/magaza/1/1';
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);
                curl_close($ch);

                $html = file_get_contents('https://www.cagdasmarketler.com/tr/magaza/1/1');
                
                
                $start = stripos($html, 'class="panel-group"');

                $end = stripos($html, '</div></div>', $offset = $start);
                $length = $end - $start;
                $htmlSection = substr($html, $start, $length);
                preg_match_all('<span[^>]*>(.*?)<\/span>\si', $htmlSection, $matches);

                $listItems = $matches[1];
               
            // $this->illeri_kaydet($den2);
             //print_r($den2[7]);
            
       }
       
       public function illeri_kaydet($den2) {
          $s = count($den2);
          //print_r($s);
          $a = 0;
          $il_adi = '';
          while ($a < $s){
            $il_adi = $den2[$a];
            //print_r($a." - ". $il_adi."\n");
            $this->il_kaydet ( $il_adi);
            $a++;
          }
          
          
        }
       public function il_kaydet($il_adi) {
             print "İL: $il_adi\n";
             $query = "insert into il_adi (il) values ('$il_adi')";
             $result = @pg_query ( $this->linkid, $query );
             if ($result) {
                    print "İL: $il_adi inserted\n";
                    $this->ilceleri_al ($il_adi);
                   
              } else {
                     print "İL: $il_adi\n";
             }
       }
      
       public function ilceleri_al($il_adi) {
              print "İLCELERI ALIYORUM: $il_adi\n";
              $ch = curl_init();
              $url = "https://kurumsal.sokmarket.com.tr/ajax/servis/ilceler?city=$il_adi";
              curl_setopt($ch,CURLOPT_URL, $url);
              curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
             $resp = curl_exec($ch);
             if($e = curl_error($ch)) {
               echo $e;
             }
             else{
               $decoded = json_decode($resp, true);
               $array_data = $decoded["districts"];
             }
             $this->ilceleri_kaydet($il_adi, $array_data);
             return 0;
       }
       
       public function ilceleri_kaydet($il_adi, $array_data) {
              $s = count($array_data);
              //print_r($s);
              $a = 0;
              $ilce_adi = '';
              while ($a < $s){
                $ilce_adi = $array_data[$a];
                //print_r($a." - ". $ilce_adi."\n");
                $this->ilce_kaydet ($il_adi, $ilce_adi);
                $this->subeleri_al($il_adi, $ilce_adi);
                $a++;
              }
                    
       }             
       

       public function ilce_kaydet($il_adi, $ilce_adi) {
              print "İL: $il_adi  İLCE:  $ilce_adi inserted\n";
              $query = "insert into ilce_adi (il,ilce) values ('$il_adi','$ilce_adi')";
              $result = @pg_query ( $this->linkid, $query );
              if ($result) {
                     print "İL: $il_adi  İLCE:  $ilce_adi inserted\n";
              } else {
                     print "İL: $il_adi  İLCE:  $ilce_adi inserted\n";
              }
       }
       
      
       function subeleri_al($il_adi, $ilce_adi) {
              //print "SUBELERİ ALIYORUM: $il_adi\n";
              $ch = curl_init();
              $url = "https://kurumsal.sokmarket.com.tr/ajax/servis/magazalarimiz?city={$il_adi}&district={$ilce_adi}";
              curl_setopt($ch,CURLOPT_URL, $url);
              curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
             $resp = curl_exec($ch);
             if($e = curl_error($ch)) {
               echo $e;
             }
             else{
              $decoded = json_decode($resp, true);
              $array_data = $decoded["response"];
             // print_r($array_data);
             }
             $this->subeleri_kaydet($array_data);
             return 0;
                     
       }
       public function subeleri_kaydet($array_data) {
              //$s = count($array_data);
              print "SUBELERİ KAYDEDİYORUM\n";

              /*$id = '';
              $name = '';
              $phone = '';
              $address = '';
              $districtCity = '';
              $city = '';
              $ltd = '';
              $lng = '';*/
              foreach($array_data['subeler'] as $item) {
                     
                     $id = $item['id'];
                     $name = $item['name'];
                     $phone = $item['phone'];
                     $address = $item['address'];
                     $districtCity = $item['districtCity'];
                     $city = $item['city'];
                     $ltd1 = $item['ltd'];
                     $lng1 = $item['lng'];
                     $ltd = str_replace(",",".",$ltd1);
                     $lng = str_replace(",",".",$lng1);
                     //print_r($id. "\n");
                     //print_r($name. "\n");
                     //print_r($phone. "\n");
                     //print_r($address. "\n");
                     //print_r($districtCity. "\n");
                     print_r($ltd. "\n");
       
                     $this->sube_kaydet($id,$name,$phone,$address,$districtCity,$city, $ltd, $lng);
              }
              
             
              
              
                    
       }    
       public function sube_kaydet($id,$name,$phone,$address,$districtCity,$city,$ltd, $lng) {
              $query = "insert into sube (id,name,phone,address,\"districtCity\",city,geog) values ('$id','$name','$phone','$address','$districtCity','$city',ST_SetSRID(ST_MakePoint($lng,$ltd),4326))";
              print ($query."\n");
              $result = @pg_query ( $this->linkid, $query );
              if ($result) {
                     print "SUBE: $id inserted\n";
              } else {
                     print "SUBE: $id   not inserted\n";
              }
       }
       
        
}
$timeout = 5;
$old = ini_set('default_socket_timeout', $timeout);
$sys = new capture();
$sys->run();
ini_set('default_socket_timeout', $old);
       
?>
       
