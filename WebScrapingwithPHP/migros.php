<?php

class dbsetting {
       public $host     = "localhost";
       public $dbname   = "migros";
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
                    
                   $this->illeri_al();
                    
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
        $url =  "https://api.migroskurumsal.com/api/StoreLocation/GetCityAndCounties";
        $qry_str = "x=10&y=20";
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        // Set request method to POST
        curl_setopt($ch, CURLOPT_POST, 1);
        // Set query data here with CURLOPT_POSTFIELDS
        curl_setopt($ch, CURLOPT_POSTFIELDS, $qry_str);

        $content = trim(curl_exec($ch));
        curl_close($ch);
        $decoded = json_decode($content,true);
        //print_r($decoded);
        $this->illeri_kaydet($decoded);
    }

    public function illeri_kaydet($decoded) {
        $deneme2= $decoded["data"];
        //$deneme3= $deneme2["children"];
        
        $elementCount  = count($deneme2);
        //print_r($elementCount); 
        for ($i = 0; $i <= $elementCount-1; $i++) {
            $array= $deneme2[$i];
         
            $cityname = $array["text"];
            $cityid = $array["value"];
            $cityname2 = $array["children"];
            $elementCount2  = count($cityname2);
            
           // print_r($elementCount2);
           // print_r($cityname);
            //print_r($cityid);
            $this->ilceleri_al($cityname,$cityid,$cityname2);
                //print_r($cityname);
           
        

    }
} 
public function ilceleri_al($cityname,$cityid,$cityname2) {

    $ch = curl_init();
    $url =  "https://api.migroskurumsal.com/api/StoreLocation/GetCityAndCounties";
    $qry_str = "x=10&y=20";
    curl_setopt($ch, CURLOPT_URL, $url);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);

    // Set request method to POST
    curl_setopt($ch, CURLOPT_POST, 1);
    // Set query data here with CURLOPT_POSTFIELDS
    curl_setopt($ch, CURLOPT_POSTFIELDS, $qry_str);

    $content = trim(curl_exec($ch));
    curl_close($ch);
    $decoded = json_decode($content,true);
    //print_r($decoded);
    $this->ilceleri_kaydet($decoded,$cityname,$cityid,$cityname2);
}

public function ilceleri_kaydet($decoded,$cityname,$cityid,$cityname2) {
    
   // $cityname2 = $deneme2["children"];
   //print_r($deneme6);
    $elementCount2  = count($cityname2);
    //print_r($deneme2);
    for ($i = 0; $i <= $elementCount2-1; $i++) {

            $deneme5 = $cityname2[$i];
            $county = $deneme5["text"];
            $countyId = $deneme5["value"];
          // print_r($county);
          //  print_r($countyId);
            $this->subeleri_al($county,$countyId,$cityname,$cityid,$cityname2);
            //print_r($cityname);
       
    }

}


  
       public function subeleri_al($county,$countyId,$cityname,$cityid,$cityname2) {
              
        $ch = curl_init();
        $url = "https://api.migroskurumsal.com/api/StoreLocation/GetStoresWithDetails?cityId={$cityid}&countyId={$countyId}&brandId=8";
        $qry_str = "x=10&y=20";
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        // Set request method to POST
        curl_setopt($ch, CURLOPT_POST, 1);
        // Set query data here with CURLOPT_POSTFIELDS
        curl_setopt($ch, CURLOPT_POSTFIELDS, $qry_str);

        $content = trim(curl_exec($ch));
        curl_close($ch);
        $decoded = json_decode($content,true);
        //print_r($decoded);
        $this->subeleri_kaydet($decoded);

       }
       
       public function subeleri_kaydet($decoded) {
            $deneme7= $decoded["data"];
            $elementCount  = count($deneme7);
            //print_r($deneme7); 
            for ($i = 0; $i <= $elementCount-1; $i++) {
                $array8= $deneme7[$i];
              //  print_r($deneme);
              //  $array =$deneme[$i];
                $id = $array8["id"];
                //print_r($id);
                $name = $array8['name'];
                //print_r($name);
                $address = $array8['address'];
               // print_r($address);
                    $city =  $array8['city'];
                 //   print_r($city);
                    $cityId= $array8['cityId'];
                    $countyId= $array8['countyId'];
                    $county= $array8['county'];
                    $phone= $array8['phone'];
                    $weekdaysHours= $array8['weekdaysHours'];
                    $saturadayHours= $array8['saturadayHours'];
                    $sundayHours= $array8['sundayHours'];
                    $brandId= $array8['brandId'];
                    $brand= $array8['brand'];
                    $lng =$array8['lng'];
                    $lat = $array8['lat']; 
                  /*  print_r($cityId);
                    echo $countyId;
                    echo $county;
                    echo $phone;
                    echo $weekdaysHours;
                    echo $saturadayHours;
                    echo $sundayHours;
                    echo $brandId;
                    echo $brand;
                    echo $lng;
                    echo $lat;*/
                   $this->magaza_kaydet($id,$name,$address, $city,$cityId,$countyId,$county,$phone,$weekdaysHours,$saturadayHours,$sundayHours,$brandId,$brand,$lat,$lng);
            }
     
              
              
              
              
                    
       }    
       public function magaza_kaydet($id,$name,$address, $city,$cityId,$countyId,$county,$phone,$weekdaysHours,$saturadayHours,$sundayHours,$brandId,$brand,$lat,$lng) {
              $query = "insert into magaza (\"id\",\"name\",\"address\",\"city\",\"cityId\",\"countyId\",\"county\",\"phone\",\"weekdaysHours\",\"saturdayHours\",\"sundayHours\",\"brandId\",\"brand\",geog) values ('$id','$name','$address', '$city','$cityId','$countyId','$county','$phone','$weekdaysHours','$saturadayHours','$sundayHours','$brandId','$brand' ,ST_SetSRID(ST_MakePoint($lng,$lat),4326))";
              
              $result = @pg_query ( $this->linkid, $query );
              if ($result) {
                     print "SUBE: $name inserted\n";
              } else {
                     print "SUBE: $name  not inserted\n";
              }
       }
      
      
}
$timeout = 5;
$old = ini_set('default_socket_timeout', $timeout);
$sys = new capture();
$sys->run();
ini_set('default_socket_timeout', $old);
       
?>
       
