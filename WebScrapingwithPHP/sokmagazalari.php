<?php

class dbsetting {
       public $host     = "localhost";
       public $dbname   = "soka";
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
                    
                   
                   $this->sok();
                   
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
       
       
       function sok() {
        $ch = curl_init();
        $url = "https://kurumsal.sokmarket.com.tr/ajax/cache/sehirler";
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
              if($e = curl_error($ch)) {
                     echo $e;
              }
              else{
                     $decoded = json_decode($resp, true);
                    // print_r($decoded);
                   
            //print_r($url);
              }
     
              $this->illeri_kaydet ( $decoded );
         
       }
       
       public function illeri_kaydet($decoded) {
              foreach($decoded as $item) {
                     $deneme= $item["list"];
                     $elementCount  = count($deneme);
                     //$id =$deneme->id;
                     //print_r($elementCount); 
                     for ($i = 0; $i <= $elementCount-1; $i++) {
                            $array =$deneme[$i];
                
                            $city_value = $array["Code"];
                            $city = $array['Value'];
                            //$this->il_kaydet($city_value,$city);
                            $this->ilceleri_al ($city); 
                     }
              }
              
       }
   /*    public function il_kaydet($city_value,$city) {
              $query = "insert into il (il,ilvalue) values ('$city','$city_value')";
              $result = @pg_query ( $this->linkid, $query );
              if ($result) {
                      print "İL: $city_value:$city inserted\n";
                     $this->ilceleri_al ($city); 
                    
              } else {
                     print "İL:  $city_value:$city \n";
              }
              
       }
*/
       public function ilceleri_al($city) {
           
            $ch = curl_init();
            $url = "https://kurumsal.sokmarket.com.tr/ajax/servis/ilceler?city={$city}";
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            $resp = curl_exec($ch);
            if($e = curl_error($ch)) {
                echo $e;
            }
            else{
                $decoded = json_decode($resp, true);
               // print_r($decoded);
                //print_r($decoded);
                //$array_data = $decoded["response"];
                //print_r($url);
                $this->ilceleri_kaydet ( $city,$decoded );
             }
       }


       public function ilceleri_kaydet($city,$decoded) {
              foreach($decoded as $item) {

                    // $deneme= $item["districts"];
                     $elementCount  = count($item);
                     //$id =$deneme->id;
                   //  print_r($elementCount); 
                            for ($i = 0; $i <= $elementCount-1; $i++) {
                                   $ilce =$item[$i];
                                   
                                   $this->magazalari_al ( $city,$ilce ); 
                
                             }
                     //$this->ilce_kaydet($city,$ilce);
                    
              }
              
       }             
     /*  public function ilce_kaydet($city,$ilce) {
              $query = "insert into ilce (il, ilce) values ('$city', '$ilce')";
              $result = @pg_query ( $this->linkid, $query );
              if ($result) {
                     print "İL: $city  İLCE:  $ilce inserted\n";
                     $this->magazalari_al ( $city,$ilce ); 
              } else {
                     print "İL: $city  İLCE:  $ilce inserted\n";
              }
       }
*/

       public function magazalari_al ( $city,$ilce ) {
           
              $ch = curl_init();
              $url = "https://kurumsal.sokmarket.com.tr/ajax/servis/magazalarimiz?city={$city}&district={$ilce}";
              curl_setopt($ch,CURLOPT_URL, $url);
              curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
              $resp = curl_exec($ch);
              if($e = curl_error($ch)) {
                echo $e;
             }
             else{
                $decoded = json_decode($resp, true);
                
              }
         
               $this->magazalari_kaydet ( $decoded );
               
       }
       public function magazalari_kaydet ($decoded ) {
              foreach($decoded as $item) {
                     $deneme= $item["subeler"];
                     $elementCount  = count($deneme);
                     //print_r($elementCount); 
                     for ($i = 0; $i <= $elementCount-1; $i++) {
                             $array =$deneme[$i];
                            $name = $array["name"];
                           // print_r($name);
                             $phone = $array['phone'];
                             //print_r($phone);
                             $address = $array['address'];
                            // print_r($address);
                            $districtCity =  $array['districtCity'];
                            $city1= $array['city'];
                            $lng =$array['lng'];
                            $longitude = str_replace(',', '.', $lng);
                           
                           
                            $ltd = $array['ltd']; 
                            $latitude = str_replace(',', '.', $ltd);
                           
                            $this->magaza_kaydet($name,$phone,$address, $districtCity,$city1,$longitude,$latitude);
                     }
              }
       }     

       public function magaza_kaydet ($name,$phone, $address,$districtCity,$city1,$longitude,$latitude) {
               $query = "insert into magaza (\"name\",\"phone\",\"address\",\"districtCity\",\"city1\",geog) values ('$name','$phone','$address','$districtCity','$city1', ST_SetSRID(ST_MakePoint($latitude,$longitude),4326))";
               $result = @pg_query ( $this->linkid, $query );
               if ($result) {
                      print "İL: $city1  İLCE:  $districtCity, $name inserted\n";
               } else {
                      print "İL: $city1  İLCE:   $districtCity, $name inserted\n";
               }
         }
 
}
$timeout = 5;
$old = ini_set('default_socket_timeout', $timeout);
$sys = new capture();
$sys->run();
ini_set('default_socket_timeout', $old);
       
?>
       