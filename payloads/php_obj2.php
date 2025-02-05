<?php

class ObjInjec
{
    public $command="id";

    function __wakeup()
    {
        system($command);
        die("done...");
    }
    
    function __destruct()
    {
        die("PHP Object Injection: " . $this->c . (178*691));
    }
}

// O:8:"ObjInjec":1:{s:1:"c";s:5:"hellO";}

$x = new ObjInjec();
echo serialize($x)."\n";


