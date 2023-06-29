<?php

include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');
#require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecSysCoreDB.php");

/**
 * The ProfilePage-class handles every command on the Setting Page for Students, 
 * 
 * @author Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@students.uni-mannheim.de>
 * 
 *
 */

 class ilRecSysPageStudentSettings {
    const PLUGIN_DIR = "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem";

    private $ctrl; // ilCtrl
    private $ref_id; // ref_if of the course
    private $obj_id; // obj_id of the course

    private $ilUser;
    private $ilAccess;

    private $CourseObject;
    private $RecSysCourse;
    private $RecSysStudent;

    private $plugin;
    #private $CoreDB;

    public function __construct($ref_id)
    {
        global $ilCtrl, $ilUser, $ilAccess;

        $this->ctrl = $ilCtrl;
        $this->ilUser = $ilUser;
        $this->ilAccess = $ilAccess;

        $this->ref_id = $ref_id;
        $this->obj_id = ilObject::_lookupObjId($this->ref_id);

        $this->courseObject = new ilObjCourse($ref_id);

        #$this->RecSysCourse = ilRecSysModelCourse::getOrCreateRecSysCourse($ref_id);
       
        #$this->RecSysStudent = ilRecSysModelStudent::getOrCreateRecSysStudent($this->ilUser->getId(), $this->ref_id, $this->RecSysCourse->getOpt_default());
        
        #CREATE COREDB CLASS!
        #$this->CoreDB = new ilRecSysCoreDB("admin");

        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }
         
    public function show_student_settings()
    {
        global $tpl;
        
        $tplRecSys = new ilTemplate("tpl.settings_student.html", true, true, self::PLUGIN_DIR);
        #$tplRecSys-> $this->addSettings($tplRecSys);

        $htmlContent = $tplRecSys->get();
        $tpl->setContent($htmlContent);
        

    }

    public function save_student_settings()
    {
        #$status = $_POST['recsys_student_status'];

        #$this->updateStudent($this->ilUser->getId(), $status);
        #$this->RecSysStudent->refreshRecSysStudent();

        #ilUtil::sendSuccess($this->plugin->txt('recsys_saved_sucessfully'), true);
        
        $this->show_student_settings();
    }

    private function addSettings($tplMain) {
        /*
        $tpl = new ilTemplate("tpl.il_recsys_pageview_student_settings.html", false, false, self::PLUGIN_DIR);
        
        // set static Form Content (Language)
        $tpl->setVariable("RECSYS_STUDENT_STATUS_TEXT", 		    $this->plugin->txt("recsys_student_status_text"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_LABEL", 		    $this->plugin->txt("recsys_student_status_label"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE_LABEL",     $this->plugin->txt("recsys_student_status_active_label"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE_HELP",      $this->plugin->txt("recsys_student_status_active_help"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE_LABEL",   $this->plugin->txt("recsys_student_status_inactive_label"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE_HELP",    $this->plugin->txt("recsys_student_status_inactive_help"));
        $tpl->setVariable("SAVE", 								    $this->plugin->txt("recsys_save"));
        $tpl->setVariable("CANCEL", 							    $this->plugin->txt("recsys_reset"));
        
        $tpl->setVariable("RECSYS_STUDENT_STATUS_DATA_LABEL", 	    $this->plugin->txt("recsys_student_status_data_label"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_DATA_TEXT", 	    $this->plugin->txt("recsys_student_status_data_text"));
        #$tpl->setVariable("RECSYS_STUDENT_STATUS_DATA_EXPORT", 	$this->plugin->txt("recsys_student_status_data_export"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_DELETE_LABEL", 	$this->plugin->txt("recsys_student_status_delete_label"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_DELETE_TEXT", 	    $this->plugin->txt("recsys_student_status_delete_text"));
        
        //Dynamic Content                
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE", 	
            ($this->RecSysStudent->getStatus() == ilRecSysModelStudent::USER_STATUS_ACTIVE ? 'checked="checked"' : ''));
        $tpl->setVariable("LEAP_STUDENT_STATUS_INACTIVE", 	
            ($this->RecSysStudent->getStatus() == ilRecSysModelStudent::USER_STATUS_INACTIVE ? 'checked="checked"' : ''));
        
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE_RADIO",
            ( $this->RecSysCourse->getOpt_active() ? '' : 'disabled'));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE_RADIO",
            ( $this->RecSysCourse->getOpt_out() ? '' : 'disabled')); 
        
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE_VISIBILITY", 	    
            ( $this->RecSysCourse->getOpt_active() ? '' : 'recsys-tracking-opt-hidden'));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ANONYM_VISIBILITY", 	    
            ( $this->RecSysCourse->getOpt_anonym() ? '' : 'recsys-tracking-opt-hidden'));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE_VISIBILITY", 	
            ( $this->RecSysCourse->getOpt_out()    ? '' : 'recsys-tracking-opt-hidden'));        
        
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE_TOOLTIPP",  
            ( $this->RecSysCourse->getOpt_active() ? '' : $this->plugin->txt("recsys_student_status_tooltipp")));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE_TOOLTIPP",  
            ( $this->RecSysCourse->getOpt_out() ? '' : $this->plugin->txt("recsys_student_status_tooltipp")));
        
        
        // set Action of Forms
        $tpl->setVariable("RECSYS_STUDENT_SAVE", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SAVE_STUDENT_SETTINGS));
        $tpl->setVariable("RECSYS_STUDENT_EXPORT", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_EXPORT_STUDENT_DATA));
              
        return $tpl;    
        */
    }

    public function updateStudent($usr_id, $status) {
        $RecSysStudent = ilRecSysModelStudent::getorCreateRecSysStudent($usr_id, $this->ref_id);
        $RecSysStudent->setStatus($status);
        $RecSysStudent->saveRecSysStudent();

        
        $this->CoreDB->createStudent($RecSysStudent);
        if (!$this->CoreDB->checkLastConnection())
            return False;
        
        return True;
        
    }

    private function writeRecSysPluginData() {
        $FILEDATA  = "";
        $FILEDATA .= "ILIAS Plugin Data\n";
        $FILEDATA .= "status: " . $this->RecSysStudent->getStatus() . "\n";
        $FILEDATA .= "Last 10 status updates: " . json_encode($this->RecSysStudent->getUpdates()) . "\n";
        $FILEDATA .= "( 0=inactive, 1=active)\n";
        return $FILEDATA;
    }
 }



