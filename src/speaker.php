<?php
class SpeakerEvents
{
    function check() { return true; }
    function update(){ return '';   }
}

class Speaker 
{
    /**
     * Events
     * 
     * Stores the event handlers.
     * 
     * @var array
     */
    
    private $events = [];
    
    /**
     * ID
     * 
     * Stores the last server event id.
     * 
     * @var int
     */
    
    public $id = 0;
    
    /**
     * Is Reconnection
     * 
     * True when it's a reconnection.
     * 
     * @var bool
     */
     
    public $isReconnection = false;
    
    /**
     * Retry Time
     * 
     * The seconds to retry after disconnected.
     * 
     * @var int
     */
     
    public $retryTime = 3;
    
    /**
     * Sleep Time
     * 
     * The seconds to wait after each data has been sent.
     * 
     * @var int
     */
     
    public $sleepTime   = 0.5;
    
    /**
     * Execute Time
     * 
     * The maximum execution time of the sse (seconds).
     * 
     * @var int
     */
     
    public $execTime    = 600;
    
    /**
     * Keep Time
     * 
     * Send the random string to kee the connection alive after every few seconds.
     * 
     * @var int
     */
     
    public $keepTime    = 300;
    
    
    
    
    function __construct()
    {
        /** It's a reconnection if the last event id has been setted */
        if(isset($_SERVER['HTTP_LAST_EVENT_ID']))
        {
            $this->id             = intval($_SERVER['HTTP_LAST_EVENT_ID']);
			$this->isReconnection = true;
        }
    }
    
    
    
    
    /**
     * Add Listener
     * 
     * Add an event listener.
     * 
     * @param string $eventName   The name of the event, can be an empty string if no specify an event.
     * @param mixed  $handler     The handler class.
     * 
     * @return Speaker
     */
    
    function addListener($eventName, $handler)
    {
        /** Handler must in SSEvent, so we can use it */
        ($handler instanceof SpeakerEvents) ? $this->events[$eventName] = $handler
                                            : exit('An event handler must be an instance of SpeakerEvents.');
                                       
        return $this;
    }
    
    
    
    
    /**
     * Remove Listener
     * 
     * Remove an event listener.
     * 
     * @param string $eventName   The name of the event.
     * 
     * @return Speaker
     */
    
    function removeListener($eventName)
    {
        unset($this->events[$eventName]);
        
        return $this;
    }
    
    
    
    
    /**
     * Output
     * 
     * Output the server-sent event data.
     * 
     * @param int         $id          The event id.
     * @param string|null $eventName   The event name.
     * @param string      $data        The data.
     * 
     * @return Speaker
     */
    
    function output($id, $eventName = null, $data)
    {
        echo "id: $id" . PHP_EOL;
        
        if($eventName && $eventName != '')
            echo "event: $eventName" . PHP_EOL;
            
        echo "data: $data" . PHP_EOL;
        echo PHP_EOL;
        
        return $this;
    }
    
    
    
    
    /**
     * Initialize SSE
     * 
     * Initialize the headers, settings for the SSE.
     * 
     * @return Speaker
     */
    
    function initializeSSE()
    {
        /** The server-sent event header */
        header('Content-Type: text/event-stream');
        
        /** No cache header*/
        header('Cache-Control: no-store, no-cache, must-revalidate');
        
        /** Turn off the session so PHP won't locked the whole shit */
        session_write_close();
        
        /** No timeout */
        set_time_limit(0);
        
        /** Turn off gzip */
        apache_setenv('no-gzip', '1');
        
        /** Turn off the output compression */
        ini_set('zlib.output_compression', 0);
        
        /** Flush it when there's a new output */
        ini_set('implicit_flush', 1);
    }
    
    
    
    
    /**
     * Start
     * 
     * Start the server-sent event.
     */
    
    function start()
    {
        $this->initializeSSE();
        
        /** Disable the default output buffer */
        while(ob_get_level() > 0) ob_end_clean();

        /** Flush the contents automatically  */
        ob_implicit_flush(true);
        
        /** Set retry time (seconds to microseconds) */
        echo 'retry: ' . ($this->retryTime * 1000) . PHP_EOL;
        
        /** Get the time when script started */
        $startTime = time();

        /** Never gonna give you up */
        while(true)
        {
            /** Keep alive if the time not over the limit, generate a random comment to keep connection alive */
            //if(time() - $StartTime < $this->KeepAlive) echo ': ' . md5(mt_rand()) . PHP_EOL;
            
            /** Output data */
            foreach($this->events as $eventName => $handler)
            {
                /** Update if there's something new */
                if($handler->check())
                {
                    $data = $handler->update();
                    $this->id++;
                    $this->output($this->id, $eventName, $data);
                    
                    @ob_flush(); 
                    @flush();
                }
                else
                {
                    /** Ignore it if there's no any update */
                    continue;
                }
            }
            
            /** End if over the execute limit time */
            if(time() - $startTime > $this->execTime) break;
                
            /** Take a break for a little while :) */
            usleep($this->sleepTime * 1000000);           
        }
        
        return $this;
    }
}   
?>