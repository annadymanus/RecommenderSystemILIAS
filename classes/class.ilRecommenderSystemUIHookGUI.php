<?php

include_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");
//Import model classes
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');


/**
 * User interface hook class
 * 
 * @package ILIAS\Plugins\RecommenderSystem
 * @subpackage UserInterfaceHook
 * @author Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@students.uni-mannheim.de>
 *  
 */

class ilRecommenderSystemUIHookGUI extends ilUIHookPluginGUI {

    protected $plugin; // The plugin object
    protected $ctrl; // The controller object responsible for handling requests
    protected $ilTabs; //It is used to add/activate tabs and subtabs
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

        $ref_id = (int)$_GET['ref_id'];
        $this->crs_id = $this->tree->checkForParentType($ref_id, 'crs');        

        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }

     /**
     * Called everytime the GUI is build. 
     * It is used here to inject the RecSys Plugin Tab
     */
    function modifyGUI($a_comp, $a_part, $a_par = array()) 
    {
        $correct_position = $this->isThisCorrectPositionForAddingRecSysTab($a_comp, $a_part, $a_par);

        if ($correct_position) 
        {
            //Dont know what that does in Leap
            $this->rememberRefId();
            $this->addRecSysTab();
        }
    }

    private function rememberRefId() {
        $ref_id = (int)$_GET['ref_id'];
        $this->ctrl->setParameterByClass('ilRecommenderSystemPageGUI', 'ref_id', $ref_id);
    }


    private function addRecSysTab() 
    {
        // i dont know what this tabId is about...but i guess to later access the tab among all tabs
        $tabId = "recsys_cockpit";
        $tabLabel = $this->plugin->txt("tab_label");
        
        //$tabLink = $this->ctrl->null; //getLinkTargetByClass('ilRecommenderSystemPageGUI', "show");
        //Link the actual page rather than null, so the tab is clickable
        $tabLink = $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', "show");
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
        
        $crsId = (int)$_GET['ref_id'];
        $objectType = 'undefined';

        if ($crsId > 0) {
                    try {
                        $objectType = ilObjectFactory::getInstanceByRefId($crsId)->type;
                    }
                    catch (Exception $e) {
                        return false;
                    }
        }

	    if ((strtolower($baseClass) == "ilrepositorygui") && ($objectType == 'crs') && ($a_part == "tabs")) 
        {
            //Check if course has been activated for RecSys
            if ($this->isCourseActive()) {
                return true;
            }
            
            //$ConfigModel = new ilRecommenderSystemConfig();
            //$user_is_enabled = $ConfigModel->isUserRecSysEnabled($this->ilUser->getLogin());
            $user_is_admin = $this->currentUserIsCourseAdmin();
            if ( $user_is_admin ) {
                // is dozent
                //if ($user_is_enabled)
                    return true;
            } else {
                //Check if this course is actually a RecSys course!!
                if($this->isCourseActive())
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

    private function isCourseActive() {
        // first check, if a course supports RecommenderSystems, if not skip, if instanciate ilRecommenderSystemCourse
        return true; #REMOVE!!!

        if (ilRecSysModelCourse::existsRecSysCourse($this->crs_id)) {  
            $Course = ilRecSysModelCourse::getRecSysCourse($this->crs_id);
            if ($Course->getCrs_status()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}