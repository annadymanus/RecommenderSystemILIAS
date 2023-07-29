<?php

include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelFeedback.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelRating.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecSysEventTracker.php');
require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecSysCoreDB.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/util/class.ilRecSysListTests.php');



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


        #$this->RecSysCourse   = ilRecSysModelCourse::getOrCreateRecSysCourse($crs_id);
        #$this->RecSysStudent  = ilRecSysModelStudent::getOrCreateRecSysStudent($this->ilUser->getId(), $this->crs_id, $this->RecSysCourse->getOpt_default());
        $this->CoreDB          = new ilRecSysCoreDB("admin");
        
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
            $tplRecSys = $this->addModuleTests($tplRecSys);
	    #if ($this->RecSysCourse->getMod_rec()) {
            #$tplRecSys = $ModuleGoals->addModuleIndividualGoals($tplRecSys);
        #}


        $htmlContent = $tplRecSys->get();
        $tpl->setContent($htmlContent);
    }


    // --- Tests -----------------------------------------------------------------------
    
    private function addModuleTests($tplMain) {
        $tpl = new ilTemplate("tpl.student_to.html", true, true, self::PLUGIN_DIR);
        $tpl->setVariable("TXT_MODULE_TO_LABEL", $this->plugin->txt("recsys_student_to_title"));       
        
        // Get Tests
        $tests = $this->getItemsOfCourse($this->il_crs_id, "tst");
        #throw new ErrorException("course_tests: " . print_r($tests, true));

        $ilObjGUI = new ilObjCourseGUI("", $this->crs_id, true, false);
        $CourseContent = new ilRecSysListTests($ilObjGUI);
        
        // If no Tests exist: cancel here        
        if(!count($tests)) {
            $tpl->setVariable("TXT_MODULE_TO_NO_CONTENT", $this->plugin->txt("recsys_student_to_no_content"));
            $tplMain->setVariable("MOD_TO", $tpl->get());
            return $tplMain;
        }  
        $tpl->setCurrentBlock("Test");
	    foreach ($tests as $item) {
                $itemHtml = $CourseContent->getHtmlItem($item);
                $tpl->setVariable("ITEM_HTML", $itemHtml);    
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
