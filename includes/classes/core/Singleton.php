<?php
class vnad_Singleton {
    var $Lang;
    var $Utils;
    var $Form;
    var $Check;
    var $Options;
    var $Log;
    var $Cron;
    var $Tracking;
    var $Manager;
    var $Ecommerce;
    var $Plugin;
    var $Tabs;

    function __construct() {
        $this->Lang=new vnad_Language();
        $this->Tabs=new vnad_Tabs();
        $this->Utils=new vnad_Utils();
        $this->Form=new vnad_Form();
        $this->Check=new vnad_Check();
        $this->Options=new vnad_Options();
        $this->Log=new vnad_Logger();
        $this->Cron=new vnad_Cron();
        $this->Tracking=new vnad_Tracking();
        $this->Manager=new vnad_Manager();
        $this->Ecommerce=new vnad_Ecommerce();
        $this->Plugin=new vnad_Plugin();
    }
    public function init() {
        $this->Lang->load('vnad', vnad_PLUGIN_DIR.'languages/Lang.txt');
        $this->Tabs->init();
        $this->Cron->init();
        $this->Manager->init();
    }
}