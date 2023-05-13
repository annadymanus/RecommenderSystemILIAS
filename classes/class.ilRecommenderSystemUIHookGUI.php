<?php

include_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");
//Import model classes


/**
 * User interface hook class
 * 
 * @package ILIAS\Plugins\RecommenderSystem
 * @subpackage UserInterfaceHook
 * @author 
 */

class ilRecommenderSystemUIHookGUI extends ilUIHookPluginGUI {

    protected $plugin; // The plugin object
    protected $ctrl; // The controller object responsible for handling requests
    protected $ilTabs;
    protected $tree; // Idk what that does, but seems to be necessary to get parent classes?
    protected $ilAccess; // Manages read and write access to objects (e.g. to check if admin)
    protected $ilUser; // The logged in user

    //Model specific stuff...We need to add models later
    protected $crs_id; // The id of the course
    //protected $RecSysStudent; // The student object
    
    public function __construct()
    {
        global $ilCtrl, $ilTabs, $ilUser, $ilAccess, $tree;
        $this->ctrl = $ilCtrl;
        $this->ilTabs = $ilTabs;
        $this->ilUser = $ilUser;
	    $this->ilAccess = $ilAccess;
        $this->tree = $tree;
        //Student Model specific
        //$RecSysStudent = false; //Initially false, but we will check if the user is a student later

        //I dont understand the difference between ref_id and crs_id ... maybe every object have ref_ids but only courses (subtype of objects) have ref_ids ... Could probably just check the ilias models
        $ref_id = (int)$_GET['ref_id'];
        $this->crs_id = $this->tree->checkForParentType($ref_id, 'crs');        

        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }

     /**
     * Called everytime the GUI is build. 
     * It is used here to inject the LeAP Plugin Tab
     */
    function modifyGUI($a_comp, $a_part, $a_par = array()) 
    {
        $correct_position = $this->isThisCorrectPositionForAddingRecSysTab($a_comp, $a_part, $a_par);
        
        if ($correct_position) 
        {
            //Dont know what that does in Leap
            //$this->rememberRefId();

            $this->addRecSysTab();
        }
    }

    private function addRecSysTab() 
    {
        // i dont know what this tabId is about...but i guess to later access the tab among all tabs
        $tabId = "recsys_cockpit";
        $tabLabel = $this->plugin->txt("tab_label");
        
        $tabLink = $this->ctrl->null; //getLinkTargetByClass('ilRecommenderSystemPageGUI', "show");
        //Link the actual page rather than null, so the tab is clickable
        //$tabLink = $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', "show");
        $this->ilTabs->addTab($tabId, $tabLabel, $tabLink);

        
        // activate Tab if it is current Tab
        $cmdClass = $this->ctrl->getCmdClass();        

        //Here the page is activated if the tab is clicked, but page logic is still not implemented
        //No idea yet how exactly this ctrl thing
        //if ($cmdClass == 'ilrecommendersystempagegui') {            
        //    $this->ilTabs->activateTab('recsys_cockpit');            
        //}
    }

    private function isThisCorrectPositionForAddingRecSysTab($a_comp, $a_part, $a_par = array()) 
    { 
        $baseClass = $_GET["baseClass"]; // baseClass wird nur so ausgelesen
        $contextObjId = $this->ctrl->getContextObjId();
        
        $refId = (int)$_GET['ref_id'];
        $objectType = 'undefined';

        if ($refId > 0) {
                    try {
                        $objectType = ilObjectFactory::getInstanceByRefId($refId)->type;
                    }
                    catch (Exception $e) {
                        return false;
                    }
        }

	    if ((strtolower($baseClass) == "ilrepositorygui") && ($objectType == 'crs') && ($a_part == "tabs")) 
        {
            //TODO: Check if user has recsys enabled
            //$ConfigModel = new ilRecommenderSystemConfig();
            //$user_is_enabled = $ConfigModel->isUserRecSysEnabled($this->ilUser->getLogin());
            $user_is_admin = $this->currentUserIsCourseAdmin();
            if ( $user_is_admin ) {
                // is dozent
                if ($user_is_enabled)
                    return true;
            } else {
                //Check if this course is actually a RecSys course!!
                //if($this->isCourseActive())
                return true;
            }
        }
        return false;
    }

    private function currentUserIsCourseAdmin()
    {
        $ref_id = (int)$_GET['ref_id'];        
        if ($this->ilAccess->checkAccess('write','',$ref_id)) { // access is checked on ref_id of course object
            return true;
        } else {
            return false;
        }
    }
}