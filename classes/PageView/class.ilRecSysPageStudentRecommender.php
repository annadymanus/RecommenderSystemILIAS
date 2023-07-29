<?php

include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');



/**
 * Handles the Commands of the Student Page.
 *
 * @author  Anna Maria Eschbach-Dymanus <anna.maria.eschbach-dymanus@uni-mannheim.de> * 
 *
 */
class ilRecSysPageStudentRecommender {
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
        $tpl = new ilTemplate("tpl.student_recommender.html", true, false, self::PLUGIN_DIR); //set to true true later
        $tplMain->setVariable("MOD_REC", $tpl->get());
        return $tplMain;
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
    
 
}
