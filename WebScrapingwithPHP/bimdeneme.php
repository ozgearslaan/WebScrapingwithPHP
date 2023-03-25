<?php

class dbsetting {
       public $host     = "localhost";
       public $dbname   = "bim";
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
                    
                   
                   $this->bim();
                   
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
       
       
       

       public function bim () {
           
              $curl = curl_init();
              $url = "https://www.bim.com.tr/Categories/104/magazalar.aspx?CityKey=67&CountyKey=1741";
              curl_setopt($curl,CURLOPT_URL, $url);
              curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
              $content = curl_exec($curl);
              
               $this->magazalari_kaydet ( $content );
               
       }
       public function magazalari_kaydet ( $content ) {
              $dom = new DOMDocument();
              @ $dom->loadHTML($content);
              $xpath = new DOMXPath($dom);
              $entries = $xpath->query ( "//*[@id='form1']/div/div[2]/div/div[2]/div/div/div/div" );
              foreach ( $entries as $entry ) {
                     $il_adi='Zonguldak';
                     $ilce_adi='Merkez';
                     $komple = $entry->childNodes;
                     $magazaadi = $komple[0]->textContent;
                     $adres = $komple[1]->textContent;
                     $this->magaza_kaydet ( $il_adi, $ilce_adi, $magazaadi, $adres);
                     
              }  
       }     
       public function magaza_kaydet ( $il_adi, $ilce_adi, $magazaadi, $adres) {
               $query = "insert into magaza (il_adi,ilce_adi,magazaadi,adres) values ('$il_adi','$ilce_adi','$magazaadi', '$adres')";
               $result = @pg_query ( $this->linkid, $query );
               if ($result) {
                      print "İL: $il_adi  İLCE:  $ilce_adi, $magazaadi, $adres inserted\n";
               } else {
                      print "İL: $il_adi  İLCE:   $ilce_adi, $magazaadi, $adres inserted\n";
               }
         }
 
}
$timeout = 5;
$old = ini_set('default_socket_timeout', $timeout);
$sys = new capture();
$sys->run();
ini_set('default_socket_timeout', $old);

?>
       