<?php
include '../../src/speaker.php';

if(isset($_GET['content']))
{
    $chat = json_decode(file_get_contents('chat.json'));
    
    if($chat != null)
        array_push($chat, $_GET['content']);
    else
        $chat = [$_GET['content']];
    
    file_put_contents('chat.json', json_encode($chat));
}
else
{
    class Chatroom extends SpeakerEvents
    {
        private $chat = '';
        
        function __construct()
        {
            $this->chat = file_get_contents('chat.json');
        }
        
        function update()
        {
            return end(json_decode($this->chat));
        }
        
        function check()
        {
            $newestChat = file_get_contents('chat.json');
            
            if($newestChat == $this->chat)
                return false;
    
            $this->chat = $newestChat;
            
            return true;
        }
    }
    
    $speaker = new Speaker();
    $speaker->addListener('', new Chatroom());
    $speaker->start();
}
?>