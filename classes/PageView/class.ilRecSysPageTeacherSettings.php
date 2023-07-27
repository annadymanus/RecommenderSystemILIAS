<?php


require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecSysCoreDB.php');


/**
 * Handles the Commands of the Admin Page.
 * 
 * @author  Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@uni-mannheim.de> * 
 *
 */
class ilRecSysPageTeacherSettings {

    private $ctrl;
    private $crs_id;	// ref_id of the course
    private $obj_id; // obj_id of the course in ILIAS db
    
    private $ilUser;
    private $ilAccess;
    
    private $plugin;  
    private $RecSysCourse;
    private $CoreDB;
    
    
    public function __construct( $crs_id ) {
        global $ilCtrl, $ilUser, $ilAccess;
        
        $this->ctrl = $ilCtrl;
        $this->ilUser = $ilUser;
        $this->ilAccess = $ilAccess;
        
        $this->ilObjectGUI = $ilObjectGUI;
        
        $this->crs_id = $crs_id;
        $this->obj_id = ilObject::_lookupObjectId($this->crs_id);       
        
        $this->RecSysCourse = ilRecSysModelCourse::getOrCreateRecSysCourse($this->crs_id);

        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }

    // ---------------------------------------------------------------------------

    /**
     * Builds the complete page with current RS-Profile in Course
     */
    public function show_teacher_settings() {
        global $tpl;
        
        $htmlContent = $this->renderTeacherTemplateContent();
        $tpl->setContent($htmlContent);
    }
    
    
    public function save_teacher_settings() 
    {            
        $this->updateCourseSettings(); 

        #$this->CoreDB = new ilRecSysCoreDB("admin");
        #$this->CoreDB->createCourse( $this->RecSysCourse );
        #$this->CoreDB->checkLastConnection();

        ilUtil::sendSuccess($this->plugin->txt("recsys_saved_successfully"), true);
        
        $this->show_teacher_settings();
    }
    
    
    public function update_student_and_resources() 
    {
        #$this->CoreDB = new ilRecSysCoreDB("admin");

        // at first create the course if not exists
        #$this->CoreDB->createCourse( $this->RecSysCourse );
        #if (!$this->CoreDB->checkLastConnection()) {
        #    $this->show_teacher_settings();
        #    return False;
        #}

        $result = $this->updateStudentsOfCourse();

        if ($result)
            $result = $this->updateResourcesOfCourse();

/*
        if ($result)
            $this->updateTestResultsOfCourse();
*/        
	if ($result) {
            ilUtil::sendSuccess($this->plugin->txt("recsys_update_successfull"), true);
	}
        $this->show_teacher_settings();
    }
    
    // ------------------------------------------------------------------------------
    
    private function updateCourseSettings() 
    {        
        $this->RecSysCourse->setCrs_status( $this->getCheckboxValue('recsys_course_status') );
        $this->RecSysCourse->setMod_tracking( $this->getCheckboxValue('recsys_tracking') );        
        
        
        //data privacy options for studens are always in full student control  
        $this->RecSysCourse->setOpt_default( 0 ); // Student Default Tracking status should always be NONE=0 (active = 1) 
        $this->RecSysCourse->setOpt_out( 1 );     // Student OPT-OUT always active
        $this->RecSysCourse->setOpt_active( 1 );  // Student OPT-IN always active
        
        // ###HWS2021 END ###
        
        $this->RecSysCourse->setMod_lo( $this->getCheckboxValue('recsys_lo') );
        $this->RecSysCourse->setMod_ig( $this->getCheckboxValue('recsys_ig') );
        #$this->RecSysCourse->setMod_ig_default( $this->getCheckboxValue('recsys_ig_default') );
        
        $this->RecSysCourse->save();
    }
    
    
    private function getCheckboxValue($id) {
        return !empty($_POST[$id]) ? 1 : 0;
    }
    
    
    private function getRadioValue($id) {
        return !empty($_POST[$id]) ? $_POST[$id] : 0;
    }
    
    
    private function renderTeacherTemplateContent()
    {
        $tplTeacher = new ilTemplate("tpl.settings_teacher.html", true, true, "Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem");
        $tplTeacher = $this->setStaticVariablesOfTeacherTemplate($tplTeacher);
        $tplTeacher = $this->setDynamicVariablesOfteacherTemplate($tplTeacher);
        return $tplTeacher->get();
    }
    
    
    private function setStaticVariablesOfTeacherTemplate($tplTeacher)
    {
        // set Action of Form
        $tplTeacher->setVariable("RECSYS_ADMIN_SAVE",                     "Admin Save");#$this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SAVE_TEACHER_SETTINGS));
        
        // set static Form Content (Language)
        $tplTeacher->setVariable("RECSYS_SETTINGS_LABEL",                 "Settings Label");#$this->plugin->txt("recsys_settings_label"));
        
        $tplTeacher->setVariable("RECSYS_SETTINGS_COURSE_ACTIVE_LABEL",   "Course Active Label");#$this->plugin->txt("recsys_settings_course_active_label"));
        $tplTeacher->setVariable("RECSYS_SETTINGS_COURSE_ACTIVE_HELP",    "Course Active Help");#$this->plugin->txt("recsys_settings_course_active_help"));
        
        #$tplTeacher->setVariable("RECSYS_SETTINGS_TRACKING_LABEL",        $this->plugin->txt("recsys_settings_tracking_label"));
        #$tplTeacher->setVariable("RECSYS_SETTINGS_TRACKING_HELP",         $this->plugin->txt("recsys_settings_tracking_help"));
        
        $tplTeacher->setVariable("RECSYS_SETTINGS_LO_LABEL",              "Settings LO Label");#$this->plugin->txt("recsys_settings_lg_label"));
        $tplTeacher->setVariable("RECSYS_SETTINGS_LO_HELP",               "Settings LO Help");#$this->plugin->txt("recsys_settings_lg_help"));
        $tplTeacher->setVariable("RECSYS_SETTINGS_LO_EDIT",               "Add / Edit Learning Objectives");       
        $tplTeacher->setVariable("RECSYS_SETTINGS_LO_URL",                "Settings LO URL");#$this->getLinkFromCouseContext('ilRSEditorGUI'));

        $tplTeacher->setVariable("RECSYS_SETTINGS_IG_LABEL",              "Settings IG Label");#$this->plugin->txt("recsys_settings_ig_label"));
        $tplTeacher->setVariable("RECSYS_SETTINGS_IG_HELP",               "Settings IG Help");#$this->plugin->txt("recsys_settings_ig_help"));
        $tplTeacher->setVariable("RECSYS_SETTINGS_IG_DEFAULT_LABEL",      "Settings IG Default Label");#$this->plugin->txt("recsys_settings_ig_default_label"));
        $tplTeacher->setVariable("RECSYS_SETTINGS_IG_DEFAULT_HELP",       "Settings IG Deafault Help");#$this->plugin->txt("recsys_settings_ig_default_help"));

        $tplTeacher->setVariable("SAVE", 							      "Save");#$this->plugin->txt("recsys_save"));
        $tplTeacher->setVariable("CANCEL", 						          "Cancel");#$this->plugin->txt("recsys_reset"));
        
        $tplTeacher->setVariable("RECSYS_SETTINGS_DATA_UPDATE_ACTION",      "Data Update");#$this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_UPDATE_STUDENTS_AND_RESOURCES));
        $tplTeacher->setVariable("RECSYS_SETTINGS_DATA_UPDATE_LABEL",       "Data Update Label");#$this->plugin->txt("recsys_settings_data_update_label"));
        $tplTeacher->setVariable("RECSYS_SETTINGS_DATA_UPDATE_BUTTON", 	 "Data Update Button"); #$this->plugin->txt("recsys_settings_data_update_button"));
        
        $tplTeacher->setVariable("RECSYS_STUDENTS_DATA_LABEL", 		     "Students Data Label");# $this->plugin->txt("recsys_students_data_label"));
        $tplTeacher->setVariable("RECSYS_STUDENTS_COURSE_LABEL", 	      "Students Course Label");#    $this->plugin->txt("recsys_students_course_label"));
        $tplTeacher->setVariable("RECSYS_STUDENTS_INACTIVE_LABEL", 	      "Students Inactive Label");#$this->plugin->txt("recsys_students_inactive_label"));
        $tplTeacher->setVariable("RECSYS_STUDENTS_ACTIVE_LABEL", 	      "Students Active Label"); #   $this->plugin->txt("recsys_students_active_label"));
        
        return $tplTeacher;
    }
    
    
    private function setDynamicVariablesOfTeacherTemplate($tplTeacher)
    {        
        $tplTeacher->setVariable("RECSYS_SETTINGS_COURSE_ACTIVE_CHECKED",          "Course Active Checked"); #($this->RecSysCourse->getCrs_status()) ? 'checked="checked"' : "");
        
        #$tplTeacher->setVariable("RECSYS_SETTINGS_TRACKING_CHECKED",                ($this->RecSysCourse->getMod_tracking()) ? 'checked="checked"' : ""); 
        
        $tplTeacher->setVariable("RECSYS_SETTINGS_OPT_DEFAULT_NONE_CHECKED",        "Opt default none checked");#($this->RecSysCourse->getOpt_default() == ilRecommenderSystemConst::OPT_DEFAULT_NONE)   ? 'checked="checked"' : "");

        $tplTeacher->setVariable("RECSYS_SETTINGS_LO_CHECKED",                      "LO Checked");#($this->RecSysCourse->getMod_lo()) ? 'checked="checked"' : "");
        $tplTeacher->setVariable("RECSYS_SETTINGS_IG_CHECKED",                      "IG Checked");#($this->RecSysCourse->getMod_ig()) ? 'checked="checked"' : "");

        // Statistics
        $tplTeacher->setVariable("RECSYS_STUDENTS_COURSE_VALUE", 	"#Students Course");#ilRecSysModelStudent::countAllStudentsOfCourse($this->crs_id));
        $tplTeacher->setVariable("RECSYS_STUDENTS_INACTIVE_VALUE", 	"#Students Inactive");#ilRecSysModelStudent::countInactiveStudentsOfCourse($this->crs_id));
        $tplTeacher->setVariable("RECSYS_STUDENTS_ACTIVE_VALUE", 	"#Students Active");#ilRecSysModelStudent::countActiveStudentsOfCourse($this->crs_id));
        
        return $tplTeacher;
    }
    
    private function updateStudentsOfCourse()
    {
        $students = array();
        $students = ilRecSysModelStudent::getIliasStudentsOfCourse($this->crs_id);

        foreach ($students as $student) {
            // get RecSys Student data and Create data if not exists
            $RecSysStudent = ilRecSysModelStudent::getOrCreateRecSysStudent($student['usr_id'], $this->crs_id);
            
            // Update RecSys Core
            #$this->CoreDB->createStudent( $RecSysStudent );
            #if (!$this->CoreDB->checkLastConnection())
            #    return False;
        }
        return True;
    }
    
    
    private function updateResourcesOfCourse()
    {
        $objects = $this->getItemsOfCourse($this->crs_id);

        // at first, add course itself as a resource
        $object = array(
            'crs_id' => $this->crs_id,
            'parent' => '0',
            'type'   => 'crs',
            'title'  => $this->RecSysCourse->getTitle(),
            'description' => $this->RecSysCourse->getDescription(),
            );
        #$this->CoreDB->createResource($object, $this->crs_id);
        #if (!$this->CoreDB->checkLastConnection())
        #    return False;

        // add materials etc ...
        #foreach ($objects as $object) {
        #    $this->CoreDB->createResource($object, $this->crs_id);
        #    if (!$this->CoreDB->checkLastConnection())
        #        return False;
        #}

         return True;
    }


    private function getItemsOfCourse($crs_ref_id)
    {
        $ilObjCourse = new ilObjCourse($crs_ref_id);
        $courseObjects = $ilObjCourse->getSubItems();
        
        $courseObjects = $courseObjects['_all'];                // get all Items of this course
        $courseObjects = $this->getAllSubItems($courseObjects); // get also Items of folders
        return $courseObjects;
    }
    
    private function getAllSubItems($container)
    {             
        if (!isset($container)) return [];  //return empty array if there is no container
        
        $items = array();
        
        foreach ($container as $item) {    
            
            array_push($items, $item);
            switch($item['type']) {
                case 'fold':
                    $ilObjFolder = new ilObjFolder($item['ref_id']);
                    $objects = $ilObjFolder->getSubItems();
                    $objects = $objects['_all'];
                    $items = array_merge($items, $this->getAllSubItems($objects));
                    break;                
                case 'grp':
                    $ilObjGroup = new ilObjGroup($item['ref_id']);
                    $objects = $ilObjGroup->getSubItems();
                    $objects = $objects['_all'];            
                    $items = array_merge($items, $this->getAllSubItems($objects));
                    break;

            }
        }
        return $items;
    }
    
    /**
     * Creates a link target and path, starting from Course Context.
     * @param String $linkTarget
     * @return String
     */
    private function getLinkFromCouseContext(String $linkTarget) {
        
        // change current_node to Course
        $cn = $this->ctrl->current_node;
        $params = $this->ctrl->getParameterArrayByClass('ilrepositorygui');
        $this->ctrl->current_node = $params['cmdNode'];
        $params = $this->ctrl->getParameterArrayByClass('ilObjCourseGUI');
        $this->ctrl->current_node = $params['cmdNode'];
        
        // create link from course Context
        $link = $this->ctrl->getLinkTargetByClass($linkTarget);
        
        // reset current_node
        $this->ctrl->current_node = $cn;
        
        return $link;
    }

}
