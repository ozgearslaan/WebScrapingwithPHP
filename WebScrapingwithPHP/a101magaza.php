<?php

class dbsetting {
       public $host     = "localhost";
       public $dbname   = "ayuzbir";
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
                   //$this->Adres_al();
                   $this->magaza_al();
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
       
       
       function magaza_al() {
              print "ADRESLERİ ALIYORUM";
            
              
              $curl = curl_init();
              $url ="https://www.okatalog.com/zonguldak/a101/magaza";
              curl_setopt($curl,CURLOPT_URL, $url);
              curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
              $resp = curl_exec($curl);
              
              $dom = new DOMDocument();
              @ $dom->loadHTML($resp);
              $xpath = new DOMXPath($dom);
              
              
              $magaza = $xpath->query('//*[@id="dtMagazalar"]/tbody/tr/td[2]/p[1]/a/b');
              

              foreach ($magaza as $tage) {
                    $magazi = $tage->textContent;
                   $this->ilce_kaydet($magazi);
              }
              return;

              
              
       }
       

       public function ilce_kaydet($magazi) {
              $query = "insert into magaza(magaza) values ('$magazi')";
              print ($query."\n");
              $result = @pg_query ( $this->linkid, $query );
              if ($result) {
                     print "Mağaza: $magazi inserted\n";
              } else {
                     print "Mağaza: $magazi not inserted\n";
              }
       }
      
}
$timeout = 5;
$old = ini_set('default_socket_timeout', $timeout);
$sys = new capture();
$sys->run();
ini_set('default_socket_timeout', $old);
       
?>
       