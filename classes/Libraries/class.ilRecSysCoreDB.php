<?php

class ilRecSysCoreDB {

    private $RecSysCoreDBdriver;
    private $ConfigModel;

    public function __construct($context)
    {                                                           
        #hey! Anna here :) I commented out this line cause since the ilRecSysCoreDBdriverLibrary 
        #is still empty, it can't be instantiated and causes an error on the website, feel free to
        #uncomment it again once you have the library ready

        #$this -> $RecSysCoreDBdriver = new ilRecSysCoreDBdriverLibrary;
    }

}

?>