<?php
include '../../src/speaker.php';

class SystemLoad extends SpeakerEvents
{
    function update()
    {
        return '一分前: '     . sys_getloadavg()[0] . 
               ', 五分前: '   . sys_getloadavg()[1] .
               ', 十五分前: ' . sys_getloadavg()[2];
    }
    
    function check()
    {
        return true;
    }
}
    
    
$speaker = new Speaker();
$speaker->sleepTime = 2;
$speaker->addListener('', new SystemLoad());
$speaker->start();

?>