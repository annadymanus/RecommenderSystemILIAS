<?php

include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelFeedback.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelRating.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecSysEventTracker.php');
require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecSysCoreDB.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/util/class.ilRecSysListMaterials.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageStudentRecommender.php');




/**
 * Handles the Commands of the Student Overview.
 *
 * @author  Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@uni-mannheim.de> * 
 *
 */
class ilRecSysPageStudent {
    const PLUGIN_DIR = "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem";
        
    //var $ctrl;
    private $crs_id;	// ref_id of the course
    private $il_crs_id;    // obj_id of course
    
    private $CourseObject;
    private $ilUser;
    private $RecSysCourse;
    private $RecSysStudent;
    private $CoreDB;
    private $plugin;
    
    
    public function __construct( $crs_id ) {
        global $ilCtrl, $ilUser;
        
        $this->ctrl         = $ilCtrl;
        $this->crs_id       = $crs_id;       
        $this->il_crs_id    = ilObject::_lookupObjectId($crs_id);
        $this->CourseObject = new ilObjCourse($crs_id);
        $this->ilUser       = $ilUser;


        $this->RecSysCourse   = null; //ilRecSysModelCourse::getOrCreateRecSysCourse($crs_id);
        $this->RecSysStudent  = null; //ilRecSysModelStudent::getOrCreateRecSysStudent($this->ilUser->getId(), $this->crs_id, $this->RecSysCourse->getOpt_default());
        #$this->CoreDB          = new ilRecSysCoreDB("admin");
        
        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }
    
    // ---------------------------------------------------------------------------
    
    public function show_student()
    {
        global $tpl;

        // set last visit
        #$this->RecSysStudent->setLastvisit(time());
        #$this->RecSysStudent->saveRecSysStudent();        

        // set my Content
        $tplRecSys = new ilTemplate("tpl.student.html", true, true, self::PLUGIN_DIR);   
        #CHANGE TO true, true once filled up with data 
        
        #if ($this->RecSysCourse->getMod_lo())
            $tplRecSys = $this->addModuleMaterials($tplRecSys);
	    #if ($this->RecSysCourse->getMod_rec()) {
            $Recommendations = new ilRecSysPageStudentRecommender($this->crs_id, $this->RecSysCourse, $this->RecSysStudent);
            $tplRecSys = $Recommendations->addModuleRecommendedMaterials($tplRecSys);
        #}


        $htmlContent = $tplRecSys->get();
        $tpl->setContent($htmlContent);
    }


    // --- Materials -----------------------------------------------------------------------
    
    private function addModuleMaterials($tplMain) {
        $tpl = new ilTemplate("tpl.materials_student.html", true, true, self::PLUGIN_DIR);
        $tpl->setVariable("RECOMMEND", $this->plugin->txt("recsys_student_recommend"));
        
        $material_types = array("exc", "file", "link", "lm", "tst");
        foreach ($material_types as $material_type){

            // Get Materials
            $materials = $this->getItemsOfCourse($this->il_crs_id, $material_type);

            $ilObjGUI = new ilObjCourseGUI("", $this->crs_id, true, false);
            $CourseContent = new ilRecSysListMaterials($ilObjGUI);
            
            // If no Material exists: cancel here        
            if(!count($materials)) {
                continue;
            }
            foreach ($materials as $item) {
                $tags = array(array("Topic1", "From x to y"), array("Topic2", "From x to y and some very long description and some very long description and some very long description and some very long description and some very long description"), array("Topic3", "From x to y"));
                foreach ($tags as $tag) {
                    $tpl->setCurrentBlock("Tags");
                    $tpl->setVariable("TAG_SELECTED", False ? 'checked' : ''); #Query Student Selection (Student Selection Status)
                    $tpl->setVariable("TAG", $tag[0]);
                    $tpl->setVariable("COMMENT", $tag[1]);
                    $tpl->setVariable("ITEM_TITLE", $item['title']);
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock("Materials");
                $itemHtml = $CourseContent->getHtmlItem($item);
                $tpl->setVariable("ITEM_HTML", $itemHtml);
                $tpl->parseCurrentBlock();                
            }
            $tpl->setCurrentBlock("Types");
            $tpl->setVariable("MATERIAL_TYPE", $material_type);
            $tpl->parseCurrentBlock(); 
        }
        
        $tplMain->setVariable("MOD_TO", $tpl->get());
        return $tplMain;
    }

    
    private function getItemsOfCourse($crs_ref_id, $type=null)
    {
        $courseObjects = $this->CourseObject->getSubItems();
        
        $courseObjects = $courseObjects['_all'];                // get all Items of this course
        $courseObjects = $this->getAllSubItems($courseObjects, $type); // get also Items of folders
        return $courseObjects;
    }
    
    private function getAllSubItems($container, $type = null)
    {             
        if (!isset($container)) return [];  //return empty array if there is no container
        
        $items = array();
        
        foreach ($container as $item) {    
          
            if ($item['type'] == $type || $type == null) {
                array_push($items, $item);
            } else if ($item['type'] == 'fold') {
                $ilObjFolder = new ilObjFolder($item['ref_id']);
                $objects = $ilObjFolder->getSubItems();
                $objects = $objects['_all'];
                $items = array_merge($items, $this->getAllSubItems($objects, $type));
            } else if ($item['type'] == 'grp') {
                $ilObjGroup = new ilObjGroup($item['ref_id']);
                $objects = $ilObjGroup->getSubItems();
                $objects = $objects['_all'];            
                $items = array_merge($items, $this->getAllSubItems($objects, $type));
            }
        }
        return $items;
    }
    
}
