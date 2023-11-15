<?php

include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelRecommender.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelFeedback.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelRating.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecSysEventTracker.php');
require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecSysCoreDB.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/util/class.ilRecSysListMaterials.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageStudentRecommender.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/class.ilRecommenderSystemPageGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageUtils.php');



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
    private $recommender;
    
    
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
        
        $this->recommender = new ilRecSysModelRecommender($this->ilUser->getId(), $this->crs_id);

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
        $section_mattype_tuples = array();
        foreach ($_POST as $key => $value) {
            $this->debug_to_console($key, "key");
            $this->debug_to_console($value, "value");
            $item_id = explode("_", $key)[0];
            $section_id = explode("_", $key)[1];
            //go sure item and section id are integers
            if(!is_numeric($item_id) || !is_numeric($section_id)){
                continue;
            }
            $material_type = ilRecSysPageUtils::objIsType($item_id);
            $material_type = ilRecSysPageUtils::MATERIAL_TYPE_TO_INDEX[$material_type];
            $section_mattype_tuples[] = array($section_id, $material_type);
        }
        $this->debug_to_console($section_mattype_tuples, "sections");

        //$recs = new ilRecSysModelRecommender($this->ilUser->getId(), $this->crs_id);
        $this->recommender->setRecommendationQuery($section_mattype_tuples);
        //$this->debug_to_console("test");
        //$this->debug_to_console($_POST);
        //$this->debug_to_console($_POST["exc 2_Topic1"]);
        //$this->updateStudent($this->ilUser->getId(), $status);
        //$this->RecSysStudent->refreshRecSysStudent();

        //ilUtil::sendSuccess($this->plugin->txt('recsys_saved_sucessfully'), true);
        
        $this->show_student();
    }


    // --- Materials -----------------------------------------------------------------------
    
    private function addModuleMaterials($tplMain) {
        $tpl = new ilTemplate("tpl.materials_student.html", true, true, self::PLUGIN_DIR);

        $material_types = ilRecSysPageUtils::getAllValidObjTypes();
        foreach ($material_types as $material_type){
            // Get Materials
            $materials = ilRecSysPageUtils::getItemsOfCourse($this->CourseObject, $material_type);

            $ilObjGUI = new ilObjCourseGUI("", $this->crs_id, true, false);
            $CourseContent = new ilRecSysListMaterials($ilObjGUI);
            
            // If no Material exists: cancel here        
            if(!count($materials)) {
                continue;
            }
            foreach ($materials as $item) {
                $file_type = null;
                if($material_type == "file"){
                    $file_type = ilRecSysPageUtils::fileIsType($item['obj_id']);	
                }
                $material_tags = ilRecSysPageUtils::getMaterialTagEntries($item['obj_id'], $file_type == null ?  $material_type : $file_type);
                //$material_tags = array(array(0,array("Semantic Knowledge", "Graphs and Networks", "Sublayer Performance Review Attacks"), array(3,5)), array(1,array("Topic2"), array(7,9)), array(2,array("Topic3","Topic4","Topic5"), array(11,13)));

                //if no tags given, skip material
                if($material_tags[0][1][0]==""){
                    continue;
                }
                foreach ($material_tags as $material_tag) {
                    foreach ($material_tag[1] as $subtag){
                        $tpl->setCurrentBlock("Subtags");
                        $tpl->setVariable("TAG", $subtag);
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock("Tags");
                    $tpl->setVariable("TAG_SELECTED", False ? 'checked' : ''); #Query Student Selection (Student Selection Status)
                    $tpl->setVariable("SECTION", $material_tag[0]);
                    $tpl->setVariable("FROM", $material_tag[2][0]);
                    $tpl->setVariable("TO", $material_tag[2][1]);
                    $tpl->setVariable("MATERIAL_TYPE", $material_type);
                    $tpl->setVariable("FILE_TYPE", $file_type);
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
        $tpl->setVariable("CLEAR_SELECTION", "Clear Selection"); //PUT TO LOCALE FILE
        $tpl->setVariable("RECSYS_STUDENT_RECOMMEND", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_STUDENT_RECOMMEND));
        $tplMain->setVariable("MATERIALS", $tpl->get());
        return $tplMain;
    }



    
}
