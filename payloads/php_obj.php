<?php

class ObjInjec
{
    public $command="id";

    function __wakeup()
    {
        system($this->command);
        die("done...");
    }
    
    function __destruct()
    {
        die("PHP Object Injection: " . $this->command . (178*691));
    }
}

// O:8:"ObjInjec":1:{s:2:"id";}

//$x = new ObjInjec();
//echo serialize($x)."\n";
