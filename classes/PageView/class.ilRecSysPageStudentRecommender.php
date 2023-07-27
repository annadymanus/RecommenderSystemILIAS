<?php

include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libs/class.ilRecommenderSystemConst.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');



/**
 * Handles the Commands of the Student Page.
 *
 * @author  Anna Maria Eschbach-Dymanus <anna.maria.eschbach-dymanus@uni-mannheim.de> * 
 *
 */
class ilRecSysPageRecommender {
    const PLUGIN_DIR = "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem";
        
    //var $ctrl;
    private $crs_id;	// ref_id of the course
    private $il_crs_id;    // obj_id of course
    
    private $CourseObject;
    private $RecSysCourse;
    private $RecSysStudent;
    private $plugin;
    
    
    public function __construct( $crs_id, $RecSysCourse, $RecSysStudent) {
        global $ilCtrl;
        
        $this->ctrl         = $ilCtrl;
        $this->crs_id       = $crs_id;       
        $this->il_crs_id       = ilObject::_lookupObjectId($crs_id);
        $this->RecSysCourse   = $RecSysCourse;
        $this->RecSysStudent  = $RecSysStudent;
       
        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }
    

    public function addModuleRecommendedMaterials($tplMain) {
        $tpl = new ilTemplate("tpl.student_recommender.html", true, true, self::PLUGIN_DIR);

        // Set default Materials 
        #$this->initDefaultMaterials();

        // Static Data
        #$tpl->setVariable("TXT_MODULE_IG_TITLE", $this->plugin->txt("recsys_student_ig_title"));
        #$tpl->setVariable("TXT_MODULE_IG_STARTDATE", $this->plugin->txt("recsys_student_ig_startdate"));        
        #$tpl->setVariable("TXT_MODULE_IG_ENDDATE", $this->plugin->txt("recsys_student_ig_enddate"));
        #$tpl->setVariable("TXT_MODULE_IG_LABEL", $this->plugin->txt("recsys_student_ig_label"));
        #$tpl->setVariable("TXT_MODULE_IG_PROGRESS", $this->plugin->txt("recsys_student_ig_progress"));
        #$tpl->setVariable("TXT_MODULE_IG_TOOLTIPP", $this->plugin->txt("recsys_student_ig_tooltipp"));
        #$tpl->setVariable("TXT_MODULE_IG_BUTTON_NEW_LABEL", $this->plugin->txt("recsys_student_ig_button_new_label"));
        #$tpl->setVariable("TXT_MODULE_IG_BUTTON_ADD_LABEL", $this->plugin->txt("recsys_student_ig_button_add_label"));
        #$tpl->setVariable("TXT_MODULE_IG_BUTTON_RESET_LABEL", $this->plugin->txt("recsys_student_ig_button_reset_label"));
    
        
        // Dynamic Data        
        #$goals = ilRecSysModelRecommendation::getRecSysMaterialsforStudent($this->RecSysStudent->getUsr_id(), $this->RecSysCourse->getRef_id());
        
        #$goals[] = $this->addGoalTemplate(); // adds an empty default goal
        
        #foreach ($goals as $goal) {            
            
            #$tpl = $this->parseIndividualMaterials($tpl, $goal); // parse all materials of this course for this individual goal         
            #$tpl = $this->parseIndividualAssignedMaterials($tpl, $goal); // parse assigned materials for this individual goal         
            
            #$Goal = (object) $goal; // cast array to object
                        
            #$tpl->setCurrentBlock("IndividualMaterials");
            
            // Static Texts
            #$tpl->setVariable("TXT_MODULE_IG_BUTTON_OPEN_LABEL", $this->plugin->txt("leap_student_ig_button_open_label"));
            #$tpl->setVariable("TXT_MODULE_IG_BUTTON_CLOSE_LABEL", $this->plugin->txt("leap_student_ig_button_close_label"));           
            #$tpl->setVariable("TXT_MODULE_IG_BUTTON_SAVE_LABEL", $this->plugin->txt("leap_student_ig_button_save_label"));
            #$tpl->setVariable("TXT_MODULE_IG_BUTTON_DELETE_LABEL", $this->plugin->txt("leap_student_ig_button_delete_label"));
            #$tpl->setVariable("TXT_MODULE_IG_BUTTON_DELETE_CONFIRM", $this->plugin->txt("leap_student_ig_button_delete_confirm"));
            #$tpl->setVariable("TXT_MODULE_IG_RESOURCES", $this->plugin->txt("leap_student_ig_resources"));
            #$tpl->setVariable("TXT_MODULE_IG_MATERIALS_BUTTON_OPEN_LABEL", $this->plugin->txt("leap_student_ig_material_button_edit"));
            #$tpl->setVariable("TXT_MODULE_IG_MATERIALS_BUTTON_CLOSE_LABEL", $this->plugin->txt("leap_student_ig_material_button_close"));
            
            // Dynamic texts
            #$tpl->setVariable("VAL_MODULE_IG_ID", $Goal->goal_id);
            #$tpl->setVariable("VAL_MODULE_IG_TITLE", $Goal->title);
            #$tpl->setVariable("VAL_MODULE_IG_PROGRESS", $Goal->progress);
            $#tpl->setVariable("VAL_MODULE_IG_TEXT", $Goal->text);   
            
            // Transform Database Date format to Calender Date format: 2018-02-01 -> 01.02.2018
            #$tpl->setVariable("VAL_MODULE_IG_STARTDATE", date("d.m.Y", strtotime($Goal->startdate)) );
            #$tpl->setVariable("VAL_MODULE_IG_ENDDATE", date("d.m.Y", strtotime($Goal->enddate)) );
                     
            
         #   $tpl->parseCurrentBlock();
        #}

        $tplMain->setVariable("MOD_REC", $tpl->get());
        return $tplMain;
    }
    
    private function addRecommenderTemplate() {
        return array(
            'recommender_id'   => 0,
            'user_id'   => $this->RecSysStudent->getUsr_id(), 
            'crs_id'    => $this->RecSysCourse->getRef_id(),
            'priority'  => 0,
            'title'     => "New...",
            'text'      => "...",
            'materials' => "[]"
        );
    }
 
    private function parseIndividualMaterials($tpl, $goal) {
        //$materials = $this->getMaterialsFromCourse($course_ref);
        $materials = $this->getMaterialsFromCourse();
        
        foreach ($materials as $material) {            
            $tpl->setCurrentBlock("IndividualMaterials");
            #$tpl->setVariable("VAL_IG_ID", $goal['goal_id']);
            #$tpl->setVariable("VAL_IG_MATERIAL_ID", $material['ref_id']);
            #$tpl->setVariable("VAL_IG_MATERIAL_TITLE", $material['title']);
            #$tpl->setVariable("VAL_IG_MATERIAL_CHECKED", ( $this->isMaterialAssignedToGoal($material, $goal ) ? "checked" : "") );
            #$tpl->parseCurrentBlock();
        }        
        return $tpl;
    }
    
    
    private function parseIndividualAssignedMaterials($tpl, $goal) {
        //$materials = $this->getMaterialsFromCourse($course_ref);        
        $materials = $this->getMaterialsFromCourse();
        
        foreach ($materials as $material) {            
            if ( $this->isMaterialAssignedToRecommendation($material, $goal) ) {
                $tpl->setCurrentBlock("IndividualAssignedMaterials");
                #$tpl->setVariable("VAL_IG_ID", $goal['goal_id']);
                #$tpl->setVariable("VAL_IG_MATERIAL_ID", $material['ref_id']);
                #$tpl->setVariable("VAL_IG_MATERIAL_TITLE", $material['title']);
                
                #if ($material['type'] == 'file') {
                 #   $tpl->setVariable("VAL_IG_MATERIAL_ISLINK", 'leap-show');
                 #   $tpl->setVariable("VAL_IG_MATERIAL_ISNOLINK", 'leap-hidden');
                 #   $tpl->setVariable("VAL_IG_MATERIAL_HREF", ilObjFileAccess::_getPermanentDownloadLink( $material['ref_id'] ));
                #} else {
                 #   $tpl->setVariable("VAL_IG_MATERIAL_ISLINK", 'leap-hidden');
                 #   $tpl->setVariable("VAL_IG_MATERIAL_ISNOLINK", 'leap-show');
                 #   $tpl->setVariable("VAL_IG_MATERIAL_HREF", "");
                #}                
                $tpl->parseCurrentBlock();
            }          
        }
        return $tpl;
    }
 

    private function getMaterialsFromCourse() {
        $materials = array();
        
        //TODO_daniel improve, and get all Ressources directly from Course
        // Get Learning Objectives
        $objective_ids = ilCourseObjective::_getObjectiveIds($this->il_crs_id, true);

        foreach($objective_ids as $objective_id) {
            foreach(ilCourseObjectiveMaterials::_getAssignedMaterials($objective_id) as $mat_ref_id) {
                $type = ilObject::_lookupType($mat_ref_id, true);
                $il_crs_id = ilObject::_lookupObjectId($mat_ref_id);                  
                $title = ilObject::_lookupTitle($il_crs_id);
                $material = array(
                    'ref_id'    => $mat_ref_id,
                    'title'     => $title,
                    'type'      => $type,
                );   
                
                if (!key_exists($mat_ref_id, $materials)) {
                    $materials[$mat_ref_id] = $material;
                }               
            }
        }
        return $materials;
    }
    
 
    private function isMaterialAssignedToRecommendation($material, $goal) {
        $materials = $goal['materials'];
        $materials = ( (trim($materials) != "") ? json_decode($materials) : array() );
        
        if (empty($materials)) {
            return false;
        } else if (in_array($material['ref_id'], $materials)) {
            return true;
        } else {
            return false;
        }
    }
    
    /*private function initDefaultGoals() {        
        // if student never before visited this course
        if ($this->RecSysCourse->getMod_ig_default() == 1 && $this->RecSysStudent->getLastvisit() < 1) {
            
            $goal = new ilLeapModelGoal(0, $this->LeapStudent->getUsr_id(), $this->LeapCourse->getRef_id());
            $goal->setStartdate(date("Y-m-d"));
            $goal->setEnddate(date("Y-m-d", strtotime("+1 week")));
            $goal->setProgress(0);
            $goal->setTitle($this->plugin->txt("leap_student_ig_goal1_title"));
            $goal->setText($this->plugin->txt("leap_student_ig_goal1_text"));
            $goal->save();
            
            $goal = new ilLeapModelGoal(0, $this->LeapStudent->getUsr_id(), $this->LeapCourse->getRef_id());
            $goal->setStartdate(date("Y-m-d"));
            $goal->setEnddate(date("Y-m-d", strtotime("+1 week")));
            $goal->setProgress(0);
            $goal->setTitle($this->plugin->txt("leap_student_ig_goal2_title"));
            $goal->setText($this->plugin->txt("leap_student_ig_goal2_text"));
            $goal->save();
            
            $goal = new ilLeapModelGoal(0, $this->LeapStudent->getUsr_id(), $this->LeapCourse->getRef_id());
            $goal->setStartdate(date("Y-m-d"));
            $goal->setEnddate(date("Y-m-d", strtotime("+1 week")));
            $goal->setProgress(0);
            $goal->setTitle($this->plugin->txt("leap_student_ig_goal3_title"));
            $goal->setText($this->plugin->txt("leap_student_ig_goal3_text"));
            $goal->save();
            
            $goal = new ilLeapModelGoal(0, $this->LeapStudent->getUsr_id(), $this->LeapCourse->getRef_id());
            $goal->setStartdate(date("Y-m-d"));
            $goal->setEnddate(date("Y-m-d", strtotime("+1 week")));
            $goal->setProgress(0);
            $goal->setTitle($this->plugin->txt("leap_student_ig_goal4_title"));
            $goal->setText($this->plugin->txt("leap_student_ig_goal4_text"));
            $goal->save();
            
            $goal = new ilLeapModelGoal(0, $this->LeapStudent->getUsr_id(), $this->LeapCourse->getRef_id());
            $goal->setStartdate(date("Y-m-d"));
            $goal->setEnddate(date("Y-m-d", strtotime("+1 week")));
            $goal->setProgress(0);
            $goal->setTitle($this->plugin->txt("leap_student_ig_goal5_title"));
            $goal->setText($this->plugin->txt("leap_student_ig_goal5_text"));
            $goal->save();
        }
    }*/
 
}
