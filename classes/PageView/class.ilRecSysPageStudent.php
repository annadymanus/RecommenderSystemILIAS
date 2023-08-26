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
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/class.ilRecommenderSystemPageGUI.php');




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


        $this->RecSysCourse   = ilRecSysModelCourse::getOrCreateRecSysCourse($crs_id);
        $this->RecSysStudent  = ilRecSysModelStudent::getOrCreateRecSysStudent($this->ilUser->getId(), $this->crs_id, $this->RecSysCourse->getOpt_default());
        $this->CoreDB          = null;//new ilRecSysCoreDB("admin");
        
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

    function debug_to_console($data, $context = 'Debug in Console') {

        // Buffering to solve problems frameworks, like header() in this and not a solid return.
        ob_start();
    
        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);
    
        echo $output;
    }   

    public function save_student_recommendations() {
        $status = $_POST['form_recsys'];
        foreach ($_POST as $key => $value) {
            $this->debug_to_console($key);
        }
        //$this->debug_to_console("test");
        //$this->debug_to_console($_POST);
        //$this->debug_to_console($_POST["exc 2_Topic1"]);
        //$this->updateStudent($this->ilUser->getId(), $status);
        //$this->RecSysStudent->refreshRecSysStudent();

        //ilUtil::sendSuccess($this->plugin->txt('recsys_saved_sucessfully'), true);
        
        //$this->show_student_settings();
    }


    // --- Materials -----------------------------------------------------------------------
    
    private function addModuleMaterials($tplMain) {
        $tpl = new ilTemplate("tpl.materials_student.html", true, true, self::PLUGIN_DIR);
        
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
                $tags = array(array(array("Topic1", "Topic2"), "From x to y"), array(array("Topic2"), "From x to y and some very long description and some very long description and some very long description and some very long description and some very long description"), array(array("Topic3","Topic4","Topic5"), "From x to y"));
                foreach ($tags as $tag) {
                    foreach ($tag[0] as $subtag){
                        $tpl->setCurrentBlock("Subtags");
                        $tpl->setVariable("TAG", $subtag);
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock("Tags");
                    $tpl->setVariable("TAG_SELECTED", False ? 'checked' : ''); #Query Student Selection (Student Selection Status)
                    $tpl->setVariable("TAGS", implode("|", $tag[0]));
                    $tpl->setVariable("COMMENT", $tag[1]);
                    $tpl->setVariable("ITEM_ID", $item['obj_id']);
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
        $tpl->setVariable("RECOMMEND", $this->plugin->txt("recsys_student_recommend"));
        $tpl->setVariable("RECSYS_STUDENT_RECOMMEND", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_STUDENT_RECOMMEND));
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
