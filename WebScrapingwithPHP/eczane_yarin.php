<?php

class dbsetting {
       public $host     = "localhost";
       public $dbname   = "eczane";
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
                   $this->subeleri_al();
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
       
       
       function subeleri_al() {
              //print "SUBELERİ ALIYORUM: $il_adi\n";
             // $il_adi_value = ["14785"],["125478"]//,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81];
              //$x = implode(',',$il_adi_value);
              //$array =  ["14785","125478"];
              //$str = join(",",$array);
              
              $metin= "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81"; 
              $str= explode(',',$metin);
              print_r($str);
              $ch = curl_init();
              foreach($str as $x){
                     $url = "https://enabiz.gov.tr/Account/NobetciEczaneList?ilKodu={$x}&ilceKodu=Tumu&nobetGunu=1";
                     //print_r($url);
              
                     curl_setopt($ch,CURLOPT_URL, $url);
                     curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
                     $resp = curl_exec($ch);
                     if($e = curl_error($ch)) {
                            echo $e;
                     }
                     else{
                            $decoded = json_decode($resp, true);
                            //$array_data = $decoded["response"];
                            //print_r($url);
                     }
                     
                     $this->subeleri_kaydet($decoded);
                     
              }
              return;
       }
       public function subeleri_kaydet($decoded) {
              //$s = count($array_data);
              //print "SUBELERİ KAYDEDİYORUM\n";

              /*$id = '';
              $name = '';
              $phone = '';
              $address = '';
              $districtCity = '';
              $city = '';
              $ltd = '';
              $lng = '';*/
              foreach($decoded as $item) {
                     
                     $eczaneadi = $item['eczaneAdi'];
                     $eczaneiladi = $item['eczaneIlAdi'];
                     $eczaneilceadi = $item['eczaneIlceAdi'];
                     $eczaneadres = $item['eczaneAdres'];
                     $enlem = $item['enlem'];
                     $boylam = $item['boylam'];
                     $baslangic = $item['baslangic'];
                     $bitis = $item['bitis'];
                     $glnno = $item['glnNo'];
                     $id = $item['id'];
                     print_r($eczaneadi. "\n");
                     print_r($eczaneiladi. "\n");
                     print_r($eczaneilceadi. "\n");
                     print_r($eczaneadres. "\n");
                     print_r($enlem. "\n");
                     print_r($boylam. "\n");
       
                     $this->sube_kaydet($eczaneadi,$eczaneiladi,$eczaneilceadi,$eczaneadres,$enlem,$boylam, $baslangic, $bitis, $glnno, $id);
              }
              
             
              
              
                    
       }    
       public function sube_kaydet($eczaneadi,$eczaneiladi,$eczaneilceadi,$eczaneadres,$enlem,$boylam, $baslangic, $bitis, $glnno, $id) {
              $query = "insert into yarin_eczane (eczaneadi,eczaneiladi,eczaneilceadi,eczaneadres,baslangic,bitis,glnno,id,geog) values ('$eczaneadi','$eczaneiladi','$eczaneilceadi','$eczaneadres','$baslangic','$bitis', $glnno, $id ,ST_SetSRID(ST_MakePoint($boylam,$enlem),4326))";
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
       