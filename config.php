<?php
class Config{
    public $host = '127.0.0.1';
    public $user = 'root';
    public $password = '';
    public $database = 'plascon';


    public function getNumbers(){
       $con = new mysqli($this->host, $this->user, $this->password, $this->database);
    $numbers= array();
    $nums="";
        $pastWinners = $this->getExclude();
       $con = new mysqli($this->host, $this->user, $this->password, $this->database);
       $q = "select inMessageId from codes where inMessageId NOT LIKE '%9%' AND status='used' order by rand() limit 500";
       $result = mysqli_query($con, $q);
       if (!$result)
           echo(mysqli_error($con));
       if (mysqli_num_rows($result) > 0) {
           while ($row = mysqli_fetch_array($result)) {
               array_push($numbers,$row['inMessageId']);
           }
       }
       //Remove Past winners
       array_diff($numbers, $pastWinners);

       //Loop through the numbers again
       foreach ($numbers as $number){
           $nums .= trim($number) . ",";
       }
       $phones = str_replace("'", "", $nums);
       return rtrim($phones, ',');
   }

   public function getAreaNumbers($area){
    global $numbers;
    $con = new mysqli($this->host, $this->user, $this->password, $this->database);

       $numbers= array();
       $phones=''; $nums="";
       $pastWinners = $this->getExclude();
       $con = new mysqli($this->host, $this->user, $this->password, $this->database);
       $q = "select inMessageId from codes where area='$area' AND status='used' AND inMessageId NOT LIKE '%9%' order by rand() limit 100";
       $result = mysqli_query($con, $q);
       if (!$result)
           echo(mysqli_error($con));
       if (mysqli_num_rows($result) > 0) {
           while ($row = mysqli_fetch_array($result)) {
               array_push($numbers,$row['inMessageId']);
           }
       }
       //Remove Past winners
       array_diff($numbers, $pastWinners);

       //Loop through the numbers again
       foreach ($numbers as $number){
           $nums .= trim($number) . ",";
       }
       $phones = str_replace("'", "", $nums);
       return rtrim($phones, ',');
    }

    public function getExclude(){
        $numbers= array();
        $con = new mysqli($this->host, $this->user, $this->password, $this->database);
        /* check connection */
        if ($con->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit();
        }
        $q = "SELECT `msisdn` FROM `past_winners` WHERE 1";
        $result = mysqli_query($con, $q);
        if (!$result)
            echo(mysqli_error($con));
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result)) {
                array_push($numbers,$row['msisdn']);
            }
        }
        return $numbers;
    }
    
   public function getTestNumbers(){
    $numbers = "256788106120,256700402006,256772860277,256774276726,256781477172,256787035753,256784310451,256782835736,256777025670,256757871664,256774471028,256785623138,256782564613,256782722142,256775354046,256782644814,256782854231,256782378185,256750072626,256701005383,256782356234,256775561306,256772468773,256700825258,256772878780,256700806873,256776320775,256781138043,256772120413,256774344016,256772688234,256776320775,256702683355,256778317767,256774081368,256700825258,256788106120,256788735411,256783283114,256777807661,256783687487,256773478235,256776240376,256782425014,256702830645,256702627446,256758518358,256784116045,256774854635,256774004311,256703751844,256778877105,256703602045256,256754168573,256777807661,256700825258,256782356234,256702627446,256784574245,256782265268,256772637820,256705811524,256776320775,256783552777,256774414524,256782580541,256702627446,256782451322,256782887364,256788106120,256774823581,256782835736,256782773377256,256782647174,256782017676,256773728050,256781477172,256776320775,256756128184,256784860717,256705270652,256705477301,256772667103,256776320775,256706200003,256782835330,256783288882,256772616488,256702667001,256772382077,256772510362,256772630684,256776320775,256777322214,256702475137,256704421561,256773046173,256783441080,256772644716,256756128184,256702830645,256785623138,256776320775,256752460714,256785623138,256772555057,256774308286,256700806873,256788106120,2.56777E11,256784566352,256783300555,256778720100,256788735411,256777751076,256705664651,256783552777,256777807661,256784773266,256776320775,256785380766,256705811524,256782580541,256778388066,256700825258,256772681077,256772378475,256772555057,256788106120,256755172265,256782747676,256772511737,256775453857,256752455525,256772122162,256780227427,256703204825,256774565423,256772826477,256700806873,256778412065,256772588207,256776360175,256758502043,256777132817,256781048857,256772573774,256752581181,256780846575,256782378185,256772608503,256778877105,256778571414,256782043788,256774854635,256702026073256,256700537415,256772444143,256702627446,256777161128,256751833464,256753464745,256783234222,256776320775,256772104404,256772511737,256772644716,256782302616,256750854856,256787480724,256700806873,256786203040,256701450014,256700825258,256706200003,256788227141,256701028750,256776360175,256785623138,256784836626,256701102646,256755172265,256782523285,256783234222,256772878780256,256774308286,256701006546,256752000810,256752581181,256701028750,256772436056,256772681077,256774677662,256751101514,256788806083, 256799999999";
    return $numbers;
    }

    
}
