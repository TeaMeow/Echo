<?php
class SSEEvent
{
    function Check()  { return true; }      //Always update if there's no Check() function in the event.
    function Update() { return '';   }      //What should we do when update?
}

class SSE
{
    /** An array which is store events and handlers */
    private $Events = [];
    public $ID = 0;        //Event ID
    
    /** Is this reconnect? */
    public $IsReconnect = false;
    
    /** How many seconds to reconnect when lost the connection? */
    public $RetryTime = 3;
    
    /** How many seconds to sleep after each data has been sent? */
    public $SleepTime = 0.5;
    
    /** Seconds about Execute limit, over how many seconds to stop while loop */
    public $ExecLimit = 600;
    
    /** Sceonds about keep sending random string to keeping this connection */
    public $KeepAlive = 300;
    
    
    
    
    function __construct()
    {
        /** If HTTP_LAST_EVENT_ID is set, this may be a reconnect */
        if(isset($_SERVER['HTTP_LAST_EVENT_ID']))
        {
            $this->ID = intval($_SERVER['HTTP_LAST_EVENT_ID']);
			$this->IsReconnect = true;
        }
    }
    
    
    
    
    /**
     * Set
     *
     * Add an event handler.
     * 
     * @param string $Event     The name of this event, can be empty.
     * @param mixed  $Handler   The main function of the event.
     * @return SSE
     */
    
    function AddEventListener($Event, $Handler)
    {
        /** Handler must in SSEvent, so we can use it */
        ($Handler instanceof SSEEvent) ? $this->Events[$Event] = $Handler
                                       : exit('An event handler must be an instance of SSEEvent.');
                                       
        return $this;
    }
    


    
    /**
     * Unset
     *
     * Remove an event handler from event list.
     * 
     * @param string $Event     The name of this event.
     * @return SSE
     */
    
    function RemoveEventListener($Event)
    {
        unset($this->Events[$Event]);
        
        return $this;
    }
    
    
    
    
    /** 
     * Output
     * 
     * @param int         $ID      The id of this data.
     * @param string|null $Event   The name of the event.
     * @param string      $Data    The data to output.
     */
    
    function Output($ID, $Event=NULL, $Data)
    {
        echo "id: $ID" . PHP_EOL;
        if($Event && $Event != '') echo "event: $Event" . PHP_EOL;
        echo "data: $Data" . PHP_EOL;
        echo PHP_EOL;
    }
    
    
    
    
    /**
     * Start
     *
     * Start the SSE, just like the header of the page.
     * 
     * @return SSE
     */
    
    function Start()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        session_write_close();
        
        /** No timeout */
        set_time_limit(0);
        
        /** Turn off GZIP */
        apache_setenv('no-gzip', '1');             //No GZIP
        ini_set('zlib.output_compression', 0);     //Disable output compression
        ini_set('implicit_flush', 1);              //If the script have any output, then flush it, just like add flush() after each echo.
        
        /** Disable the default OB */
        while(ob_get_level() > 0) ob_end_clean();

        /** Output to browser when output */
        ob_implicit_flush(true);
        
        /** Set retry time (microseconds) */
        echo 'retry: ' . ($this->RetryTime * 1000) . PHP_EOL;
        
        /** Get the time when script started */
        $StartTime = time();

        /** Keep it uuuuup ! */
        while(true)
        {
            /** Keep alive if the time not over the limit, generate a random comment to keep connection alive */
            //if(time() - $StartTime < $this->KeepAlive) echo ': ' . md5(mt_rand()) . PHP_EOL;
            
            /** Output data */
            foreach($this->Events as $Event => $Handler)
            {
                /** Update if there's something new */
                if($Handler->Check())
                {
                    $Data = $Handler->Update();         //Get data from update.
                    $this->ID++;
                    $this->Output($this->ID, $Event, $Data);
                    
                    /** FLUSH IT! */
                    @ob_flush();        //Prevert "failed to flush buffer. No buffer to flush" error.
                    @flush();
                }
                else
                {
                    /** No update, so ignore it */
                    continue;
                }
            }
            
            /** End if over the execute limit time */
            if(time() - $StartTime > $this->ExecLimit) break;
                
            /** Take a break for a little while :) */
            usleep($this->SleepTime * 1000000);           
        }
        
        return $this;
    }
}