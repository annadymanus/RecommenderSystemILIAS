<?php

require_once("./Services/Object/classes/class.ilObjectGUI.php");
include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php");
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilLeapPageTeacher.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilLeapPageTeacherSettings.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilLeapPageStudent.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageStudentSettings.php');


/**
 * The ProfilePage-class handles every command, 
 * checks the rights of the current user and 
 * forwards the commands to the other page-classes
 * 
 * @author Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@students.uni-mannheim.de>
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
        global $ilTabs, $ilUser, $ilAccess;
        parent::__construct("", $a_id, true, false);
        
        $this->ilUser = $ilUser;        
        $this->ilAccess = $ilAccess;
        $this->ilTabs = $ilTabs;
        
        
        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(); // Get the command

        if ($this->actAsStudent()){
            switch ($cmd){
                case ilRecommenderSystemConst::CMD_SHOW_STUDENT_SETTINGS:
                    $this->addTab();
                    $this->ilTabs->setSubTabActive("recsys_student_settings");
                    
                    $page = new ilRecSysPageStudentSettings($this->ref_id);
                    $page->show_student_settings();
                    break;
                case ilRecommenderSystemConst::CMD_SAVE_STUDENT_SETTINGS:
                    $this->addTab();
                    $this->ilTabs->setSubTabActive("recsys_student_settings");
                    
                    $page = new ilRecSysPageStudentSettings($this->ref_id);
                    $page->save_student_settings();
                    break;
                case ilRecommenderSystemConst::CMD_SHOW:
                case ilRecommenderSystemConst::CMD_SHOW_STUDENT:
                    $this->addTab();
                    $this->ilTabs->setSubTabActive("recsys_student_overview");
                    
                    #$page = new ilRecSysPageStudent($this->ref_id);
                    #$page->show_student();
                    break;
                default:
                    ilUtil::redirect("goto.php?target=crs_".$this->ctrl->getContextObjId());
                    break;
            }
        } 
        elseif ($this->isCurrentUserRecSysEnabled()){
            switch ($cmd) {
                case ilRecommenderSystemConst::CMD_SHOW_TEACHER:
                case ilRecommenderSystemConst::CMD_SHOW:
                    #$this->checkPermission("read");
                    $this->addTab();
                    $this->ilTabs->setSubTabActive("recsys_teacher_overview");
                    
                    #$page = new ilRecSysTeacher($this->ref_id);
                    #$page->show_teacher();
                    break;

                case ilRecommenderSystemConst::CMD_SHOW_TEACHER_SETTINGS:
                    #$this->checkPermission("write");
                    $this->addTab();
                    $this->ilTabs->setSubTabActive("recsys_teacher_settings");
                    
                    #$page = new ilRecSysTeacherSettings($this->ref_id);
                    #$page->show_teacher_settings();
                    break;
                case ilRecommenderSystemConst::CMD_SAVE_TEACHER_SETTINGS:
                    #$this->checkPermission("write");
                    $this->addTab();
                    $this->ilTabs->setSubTabActive("recsys_teacher_settings");
                    
                    #$page = new ilRecSysTeacherSettings($this->ref_id);
                    #$page->save_teacher_settings();
                    break;

                case ilRecommenderSystemConst::CMD_UPDATE_STUDENTS_AND_RESOURCES:
                    #$this->checkPermission("write");
                    $this->addTab();
                    $this->ilTabs->setSubTabActive("recsys_teacher_settings");
                    
                    #$page = new ilRecSysTeacherSettings($this->ref_id);
                    #$page->update_students_and_resources();
                    break;
                default:
                    ilUtil::redirect("goto.php?target=crs_".$this->ctrl->getContextObjId());
                    break;
            }
        }
    }

    private function actAsStudent(){

        $loggedinAsTeacher = $this->isCurrentUserCourseAdmin();
        $viewMode = ilMemberViewSettings::getInstance();
        $teacherViewModeStudent = $viewMode->isEnabled() && $viewMode->isActive();

        if (!$loggedinAsTeacher)
            return True;
        if (!$teacherViewModeStudent)
            return true;
        return False;
    }

    private function isCurrentUserRecSysEnabled(){
        /*
        create config model class first
        $ConfigModel = new ilRecSysConfig();
        $user_login = $this->ilUser->getLogin();

        return $ConfigModel->isUserRecSysEnabled($user_login);
        */
        return true;
    }

    private function isCurrentUserCourseAdmin(){
        if ($this->ilAccess->checkAccess("write", "", $this->ref_id)) // Check if the current user is a course admin
        {
            return true;
        } else {
            return false;
        }
    }

    private function addTab()
    {
        $ilObjGUI = new ilObjCourseGUI("", $this->ref_id, true, false);
        $cn = $this->ctrl->current_node;
        $params = $this->ctrl->getParameterArrayByClass('ilObjCourseGUI');
        $this->ctrl->current_node = $params['cmdNode'];
        $ilObjGUI->prepareOutput();
        $this->ctrl->current_node = $cn;
        //$this->prepareOutput();

        // subtabs
        if ($this->isCurrentUserCourseAdmin()) {        
            $this->ilTabs->addSubTab(
                  'recsys_teacher_overview', 
                  "Overview", 
                  $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SHOW_TEACHER));
            $this->ilTabs->addSubTab(
                  'recsys_teacher_settings', 
                  "Settings", 
                  $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SHOW_TEACHER_SETTINGS));
        } else {
            $this->ilTabs->addSubTab(
                'recsys_student_overview',
                "Overview",
                $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SHOW_STUDENT));
            $this->ilTabs->addSubTab(
                'recsys_student_settings',
                "Settings",
                $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SHOW_STUDENT_SETTINGS));
        }
    }
}