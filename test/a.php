<?php
include '../src/speaker.php';


class Test extends SpeakerEvents
{
    function update()
    {
        return 'ok';
    }
    
    function check()
    {
        return true;
    }
}

$speaker = new Speaker();
$speaker->addListener('', new Test());
$speaker->start();

?>