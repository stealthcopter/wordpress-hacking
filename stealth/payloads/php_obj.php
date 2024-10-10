<?php

class ObjInjec
{
    public $c="hellO";
    function __destruct()
    {
        die("PHP Object Injection: " . $this->c . (178*691));
    }
}

//$x = new ObjInjec();
//echo serialize($x)."\n";
