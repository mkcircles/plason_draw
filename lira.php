<?php
/**
 * Created by PhpStorm.
 * User: maurice
 * Date: 8/24/2017
 * Time: 9:38 AM
 */
include('config.php');
//error_reporting(1);
$config = new Config();
$numbers = $config->getAreaNumbers('Lira');

function getWinners()
{
    global $numbers;
    $phones = explode(",", $numbers);
    $rwinners = getRandomNumbers(1, 80, 8);
    $winners = '';
    for ($x = 0; $x < count($rwinners); $x++) {
        $winners .= "'" . $phones[$rwinners[$x]] . "',";
    }

    return rtrim($winners, ',');
}

function getRandomNumbers($min, $max, $count)
{
    if ($count > (($max - $min) + 1)) { return false; }
    $values = range($min, $max);
    shuffle($values);
    return array_slice($values, 0, $count);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- saved from url=(0038)http://demo.cnanney.com/apple-counter/ -->
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="description" content="Apple-Style Counter Final Demo">
    <title>PLASCON DRAW</title>
    <script type="text/javascript" async="" src="./Apple-Style Counter Final_files/ga.js"></script>

    <script type="text/javascript" src="./Apple-Style Counter Final_files/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="./Apple-Style Counter Final_files/demostyles.css">
    <style type="text/css">
        <!--
        * {
            margin: 0;
            padding: 0
            cursor: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAZdEVYdFNvZnR3YXJlAFBhaW50Lk5FVCB2My41LjbQg61aAAAADUlEQVQYV2P4//8/IwAI/QL/+TZZdwAAAABJRU5ErkJggg=='),
                /*url(images/blank.cur),*/
            none !important;
        }
        body{
            margin-top:35px;
            padding:1%;
            cursor: none;
            background: url('img/WSF-Randomiser.png') center top no-repeat #FFFFFF;
            background-size: 80%;
            font-family: Arial;
        }
        body{
            cursor: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAZdEVYdFNvZnR3YXJlAFBhaW50Lk5FVCB2My41LjbQg61aAAAADUlEQVQYV2P4//8/IwAI/QL/+TZZdwAAAABJRU5ErkJggg=='),
                /*url(images/blank.cur),*/
            none !important;
        }
        .counter{
            padding-top:0px;
        }

        .counter ul {
            list-style-type: none;
            width: 566px;
            margin: 50px auto;
            display: block;
            margin-top:20px;
        }

        .counter li {
            float: left;
            background: url(img/filmstrip.png) 0 0 no-repeat;
            width: 53px;
            height: 79px;
        }

        .counter li.seperator {
            background: url(img/comma.png) 2px 75px no-repeat;
            width: 12px
        }

        button.start{
            font-size:21px;
            width:300px;
            margin-top:30px;
        }
        .winners{
            /*border-top:solid 3px #000;*/
            display:block;
            width:68%;
            margin:auto;
            font-size: 15px;
            margin-top:10px;
            min-height:250px;
        }
        .winners-brazil{
            width:37%;
            margin-left:2%;
        }
        .winners h2{
            margin-bottom:5px;
            margin-top:20px;
            line-height:normal
        }
        .winners ul{
            margin:0;
            padding:10px;
            background: #FFFFFF;
            opacity:0.7;

        }
        .winners ul li{
            list-style:none;
            display:inline;
            margin-left: 1%;
            margin:bottom:3px;
            text-align:center;
            font-size:25px;
            color:#000000;
            font-weight:bold;
        }
        #popup{
            display:block;
            width:0;
            margin:auto;
            /*border:solid 1px #9bcb52;*/
            font-size:4em !important;
            position:absolute;
            top:110px;
            margin-left:27%;
            text-align:center;
            color:#fff;
            height:0px;
            overflow:hidden;
            left:580px;
            top:90%;
            background:#f7941e;
            background:url('img/samba popup2.png') left no-repeat;
            font-family: Arial;
            font-weight:bold;
        }
        .popnumber{
            font-size:100%;
            color:#fff;
        }



        #Blinker {display:block; height:40px;-moz-animation-iteration-count: infinite;-moz-animation-timing-function: linear;-moz-animation-duration:1s;-moz-animation-name: blink;-webkit-animation-iteration-count: infinite;-webkit-animation-timing-function: linear;-webkit-animation-duration:1s;-webkit-animation-name: blink;font-family:georgia, serif; color:#000; line-height:40px; }

        @-moz-keyframes blink {
            0% {opacity:0;}
            100% {opacity:1;}
        }
        #Blinker:hover {
            -moz-animation-play-state: paused;
        }

        @-webkit-keyframes blink {
            0% {opacity:0;}
            100% {opacity:1;}
        }
        #Blinker:hover {
            -webkit-animation-play-state: paused;

        }
        -->
    </style>

</head>
<body cz-shortcut-listen="true">
<center>
    <table>
        <tr><td class ="counter">
                <ul>
                    <!--<li id="d9" style="background-position: 0px -5562px;"></li>-->
                    <li></li>
                    <li id="d8" style="background-position: 0px 0px;"></li>
                    <li id="d7" style="background-position: 0px 0px;"></li>
                    <li id="d6" style="background-position: 0px 0px;"></li>
                    <li class="seperator"></li>
                    <li id="d5" style="background-position: 0px 0px;"></li>
                    <li id="d4" style="background-position: 0px 0px;"></li>
                    <li id="d3" style="background-position: 0px -2472px;"></li>
                    <li class="seperator"></li>
                    <li id="d2" style="background-position: 0px -4326px;"></li>
                    <li id="d1" style="background-position: 0px -4017px;"></li>
                    <li id="d0" style="background-position: 0px -2163px;"></li>
                </ul>
            </td>
        </tr>
    </table>


</center>
<div class="winners">
    <!--<h2>100,000 winners</h2> -->
    <ul class="winnerlist">

    </ul>
</div>
<!--<div class="winners winners-brazil">
<h2>ticket winners</h2>
</div>-->
<div id="popup">
    <span id="Blinker" style="text-decoration:blink;margin-top:20px; margin-bottom:10px; line-height:normal; font-size:40px;"><blink> Winner Is....</blink></span>
    <span class="popnumber"></div>
</div>

<script type="text/javascript">
    //<![CDATA[

    // Array to hold each digit's starting background-position Y value
    var initialPos = [0, -618, -1236, -1854, -2472, -3090, -3708, -4326, -4944, -5562];
    // Amination frames
    var animationFrames = 5;
    // Frame shift
    var frameShift = 103;

    // Starting number
    var theNumber = 799999999;
    // Increment
    var increment = -121111;
    // Pace of counting in milliseconds
    var pace = 480;

    // Initializing variables
    var digitsOld = [], digitsNew = [], subStart, subEnd, x, y;
    var isPaused = false;


    // Function that controls counting
    var items = [<?php echo $numbers; ?>, 256799999999];
    var winners = [<?php echo getWinners();?>];

    var refreshIntervalId;
    var mycount = 0;
    var winnerCount = 0;
    function doCount(){
        if(isPaused) {
            //console.log('Am currently paused ');
            return;
        }
        // var x = theNumber.toString();
        // theNumber += increment;
        //var y = theNumber.toString();
        var x = items[mycount].toString();
        if( mycount < items.length){
            mycount++;
            var y = items[mycount].toString();
            if(mycount == items.length-1){
                console.log("Numbers are done")
                mycount =0;
            }

        }
        currentWinner = winners.indexOf(y);
        //check if the current number is in the list
        if(currentWinner != -1)
        {
            //clearInterval(refreshIntervalId);
            //remove the current winner from the list
            //winners.splice(currentWinner, 1);
            //wait for 10secs
            /* setTimeout(function(){
                animateAndDisplay(y)
             },1700);*/
            //check if the system expects more winners
            if(winners.length > 0){
                //restart the loop through all the numbers to find the next winner
                //	setTimeout(start,6000);
            }else{

            }
        }

        digitCheck(x, y);

    }
    function phoneFormat(phone)
    {
        var str = phone;
        var res = str.replace("256","0");
        return res;
    }
    function animateAndDisplay(y)
    {
        $('.winnerlist').append('<li> ' + phoneFormat(y) + ', </li>');
        $('.popnumber').html('<h5 style="margin: 15px 0 15px 0;font-size: 55px;">'+phoneFormat(y)+'</h5>');
        $("#popup").animate({height: "210px", top: "150px", left: "10px", width: "48%"});
        
        //remove the first digits and replace with a zero

        //$('.winnerlist').append('<li> ' + phoneFormat(y) + ', </li>');
        //$('.popnumber').html(phoneFormat(y) );
        //$("#popup").hide();
        //$("#popup").animate({height:"210px",top:"150px",left:"10px", width:"48%"});
    }
    // This checks the old count value vs. new value, to determine how many digits
    // have changed and need to be animated.
    function digitCheck(x, y){
        var digitsOld = splitToArray(x);
        var digitsNew = splitToArray(y);
        for (var i = 0, c = digitsNew.length; i < c; i++){
            if (digitsNew[i] != digitsOld[i]){
                animateDigit(i, digitsOld[i], digitsNew[i]);
            }
        }
    }

    // Animation function
    function animateDigit(n, oldDigit, newDigit){
        // I want three different animations speeds based on the digit,
        // because the pace and increment is so high. If it was counting
        // slower, just one speed would do.
        // 1: Changes so fast is just like a blur
        // 2: You can see complete animation, barely
        // 3: Nice and slow
        var speed;
        switch (n){
            case 0:
                speed = pace / 8;
                break;
            case 1:
                speed = pace / 4;
                break;
            default:
                speed = pace / 2;
                break;
        }
        // Cap on slowest animation can go
        speed = (speed > 100) ? 100 : speed;
        // Get the initial Y value of background position to begin animation
        var pos = initialPos[oldDigit];
        // Each animation is 5 frames long, and 103px down the background image.
        // We delay each frame according to the speed we determined above.
        for (var k = 0; k < animationFrames; k++){
            pos = pos-frameShift;
            if (k == (animationFrames-1)){
                $("#d"+n).delay(speed).animate({'background-position': '0 '+pos+'px'}, 0, function(){
                    // At end of animation, shift position to new digit.
                    $("#d"+n).css({'background-position': '0 '+initialPos[newDigit]+'px'}, 0);
                });
            }
            else{
                $("#d"+n).delay(speed).animate({'background-position': '0 '+pos+'px'}, 0);
            }
        }

    }

    // Splits each value into an array of digits
    function splitToArray(input){
        var digits = new Array();
        for (var i = 0, c = input.length; i < c; i++){
            subStart = input.length-(i+1);
            subEnd = input.length-i;
            digits[i] = input.substring(subStart, subEnd);
        }
        return digits;
    }

    // Sets the correct digits on load
    function initialDigitCheck(initial){
        var digits = splitToArray(initial.toString());
        for (var i = 0, c = digits.length; i < c; i++){
            $("#d"+i).css({'background-position': '0 '+initialPos[digits[i]]+'px'});
        }
    }

    // Start it up

    initialDigitCheck(theNumber);
    function sleep(milliseconds) {
        var start = new Date().getTime();
        for (var i = 0; i < 1e7; i++) {
            if ((new Date().getTime() - start) > milliseconds){
                break;
            }
        }
    }
    function start(){
        clearInterval(refreshIntervalId);
        //initialDigitCheck(theNumber);
        //mycount = 0;
        refreshIntervalId = setInterval(doCount, pace);

        $("#popup").animate({height:"0px", top:"90%",left:"25%", width:"0.1%"});
        $("#popup").hide();
    }
    $('body').live('keypress',function(e){
        var p = e.which;
        if(p==13){
            start();
        }else if (p == 32) {
            isPaused = true;
        }
        else if(p == 88){
            $("#popup").animate({height:"1px", top:"95%",left:"45%", width:"1%"});
        }
        else if(p == 77 || p ==109){
            isPaused = false;
            $("#popup").animate({height:"1px", top:"95%",left:"45%", width:"1%"});
        }
        else if(p == 78 || p ==110){
            $("#popup").animate({height:"1px", top:"95%",left:"45%", width:"1%"});
            digitCheck('799999999',winners[winnerCount]);
            animateAndDisplay(winners[winnerCount]);
            isPaused = true;

            if(winnerCount ==30){
                isPaused = true;

                // console.log(winners[winnerCount-1]+ "To " + winners[winnerCount])
                clearInterval(refreshIntervalId);
            }
        }
            winnerCount++;
    });
    $('body').click(function(){
        $("#popup").animate({height:"1px", top:"95%",left:"45%", width:"1%"});
    })

    //]]>
</script>
</body>
</html>
