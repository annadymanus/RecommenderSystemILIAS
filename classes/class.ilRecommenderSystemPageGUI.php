<?php

require_once("./Services/Object/classes/class.ilObjectGUI.php");


/**
 * The ProfilePage-class handles every command, 
 * checks the rights of the current user and 
 * forwards the commands to the other page-classes
 * 
 * @author 
 *
 * @ilCtrl_IsCalledBy ilRecommenderSystemPageGUI: ilRepositoryGUI, ilAdministrationGUI, ilCommonActionDispatcherGUI, ilRecommenderSystemUIHookGUI
 *
 */
class ilRecommenderSystemPageGUI extends ilObjectGUI {
    private $ilUser;
    private $ilAccess;
    private $plugin;
    private $ilTabs;
    
    public function __construct($a_data = '', $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
    {            
        //global $ilCtrl, $ilTabs, $ilUser, $ilAccess;
        global $ilTabs, $ilUser, $ilAccess;
        parent::__construct("", $a_id, true, false);
        
        $this->ilUser = $ilUser;        
        $this->ilAccess = $ilAccess;
        $this->ilTabs = $ilTabs;
        
        
        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }
}