<?php

include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');

/**
 * The ProfilePage-class handles every command on the Setting Page for Students, CURRENTLY NOT USED!
 * 
 * @author Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@students.uni-mannheim.de>
 * 
 *
 */

 class ilRecSysPageStudentSettings {
    const PLUGIN_DIR = "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem";

    private $ctrl; // ilCtrl
    private $crs_id; // ref_if of the course
    private $il_crs_id; // obj_id of the course

    private $ilUser;
    private $ilAccess;

    private $CourseObject;
    private $RecSysCourse;
    private $RecSysStudent;

    private $plugin;
    private $CoreDB;

    public function __construct($crs_id)
    {
        global $ilCtrl, $ilUser, $ilAccess;

        $this->ctrl = $ilCtrl;
        $this->ilUser = $ilUser;
        $this->ilAccess = $ilAccess;

        $this->crs_id = $crs_id;
        $this->il_crs_id = ilObject::_lookupObjId($this->crs_id);

        $this->CourseObject = new ilObjCourse($crs_id);

        //$this->RecSysCourse = ilRecSysModelCourse::getOrCreateRecSysCourse($crs_id);
        //$this->RecSysStudent = ilRecSysModelStudent::getOrCreateRecSysStudent($this->ilUser->getId(), $this->crs_id, $this->RecSysCourse->getOpt_default());
        

        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }
         
    public function show_student_settings()
    {
        global $tpl;
        
        $tplRecSys = new ilTemplate("tpl.settings_student.html", true, true, self::PLUGIN_DIR);
        #SET false, false to true, true once addSettings is implemented to throw out empty blocks

        $tplRecSys = $this->addSettings($tplRecSys);

        $htmlContent = $tplRecSys->get();
        $tpl->setContent($htmlContent);
        

    }

    public function save_student_settings()
    {
        $status = $_POST['recsys_student_status'];

        $this->updateStudent($this->ilUser->getId(), $status);
        $this->RecSysStudent->refreshRecSysStudent();

        ilUtil::sendSuccess($this->plugin->txt('recsys_saved_sucessfully'), true);
        
        $this->show_student_settings();
    }

    private function addSettings($tplMain) {
        
        $tpl = new ilTemplate("tpl.settings_student.html", false, false, self::PLUGIN_DIR);
        
        // set static Form Content (Language)
        $tpl->setVariable("RECSYS_STUDENT_STATUS_TEXT", 		    "Status text");#$this->plugin->txt("recsys_student_status_text"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_LABEL", 		    "Status Label");#$this->plugin->txt("recsys_student_status_label"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE_LABEL",     "Status Active Label");#$this->plugin->txt("recsys_student_status_active_label"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE_HELP",      "Status Active Help");#$this->plugin->txt("recsys_student_status_active_help"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE_LABEL",   "Status Inactive Label");#$this->plugin->txt("recsys_student_status_inactive_label"));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE_HELP",    "Status Inactive Help");#$this->plugin->txt("recsys_student_status_inactive_help"));
        $tpl->setVariable("SAVE", 								    "Save");#$this->plugin->txt("recsys_save"));
        $tpl->setVariable("CANCEL", 							    "Cancel");#$this->plugin->txt("recsys_reset"));
        
    
        //Dynamic Content                
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE", true);	
            #($this->RecSysStudent->getStatus() == ilRecSysModelStudent::USER_STATUS_ACTIVE ? 'checked="checked"' : ''));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE", true);	
            #($this->RecSysStudent->getStatus() == ilRecSysModelStudent::USER_STATUS_INACTIVE ? 'checked="checked"' : ''));
        
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE_RADIO", true);
            #( $this->RecSysCourse->getOpt_active() ? '' : 'disabled'));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE_RADIO", true);
            #( $this->RecSysCourse->getOpt_out() ? '' : 'disabled')); 
        
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE_VISIBILITY", true);	    
            #( $this->RecSysCourse->getOpt_active() ? '' : 'recsys-tracking-opt-hidden'));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE_VISIBILITY", 	true);
            #( $this->RecSysCourse->getOpt_out()    ? '' : 'recsys-tracking-opt-hidden'));        
        
        $tpl->setVariable("RECSYS_STUDENT_STATUS_ACTIVE_TOOLTIPP", "test"); 
            #( $this->RecSysCourse->getOpt_active() ? '' : $this->plugin->txt("recsys_student_status_tooltipp")));
        $tpl->setVariable("RECSYS_STUDENT_STATUS_INACTIVE_TOOLTIPP",  "test");
            #( $this->RecSysCourse->getOpt_out() ? '' : $this->plugin->txt("recsys_student_status_tooltipp")));
        
        
        // set Action of Forms
        $tpl->setVariable("RECSYS_STUDENT_SAVE", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SAVE_STUDENT_SETTINGS));
        #$tpl->setVariable("RECSYS_STUDENT_EXPORT", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_EXPORT_STUDENT_DATA));
              
        return $tpl;    

    }

    public function updateStudent($usr_id, $crs_status) {
        $RecSysStudent = ilRecSysModelStudent::getOrCreateRecSysStudent($usr_id, $this->crs_id);
        $RecSysStudent->setCrs_status($crs_status);
        $RecSysStudent->saveRecSysStudent();

        
        #$this->CoreDB->createStudent($RecSysStudent);
        #if (!$this->CoreDB->checkLastConnection())
        #    return False;
        
        return True;
        
    }

    private function writeRecSysPluginData() {
        $FILEDATA  = "";
        $FILEDATA .= "RecSys Plugin Data\n";
        $FILEDATA .= "crs_status: " . $this->RecSysStudent->getCrs_status() . "\n";
        $FILEDATA .= "Last 10 status updates: " . json_encode($this->RecSysStudent->getUpdates()) . "\n";
        $FILEDATA .= "( 0=inactive, 1=active)\n";
        return $FILEDATA;
    }
 }



