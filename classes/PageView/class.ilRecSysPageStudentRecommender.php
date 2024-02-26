<?php

include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelRecommender.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageUtils.php');



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
    
    private $course_items_map;
    private $RecSysCourse;
    private $RecSysStudent;
    private $plugin;
    private $tree;
    private $recommender;
    
    
    public function __construct( $crs_id, $RecSysCourse, $RecSysStudent) {
        global $ilCtrl;
        
        $this->ctrl         = $ilCtrl;
        $this->crs_id       = $crs_id;       
        $this->il_crs_id      = ilObject::_lookupObjectId($crs_id);
        $this->RecSysCourse   = $RecSysCourse;
        $this->RecSysStudent  = $RecSysStudent;
        $this->recommender = new ilRecSysModelRecommender($this->RecSysStudent->getUsr_id(), $this->crs_id);

        $courseObject = new ilObjCourse($crs_id);
        $all_course_items = ilRecSysPageUtils::getItemsOfCourse($courseObject);
        $course_items_map = array();
        foreach ($all_course_items as $item){
            $course_items_map[$item['obj_id']] = $item;
        }
        $this->debug_to_console($crs_id, "crs_id");
        $this->debug_to_console($course_items_map, "course_items_map");

        $this->course_items_map = $course_items_map;


        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }
    

    function debug_to_console($data, $context = 'Debug in Console') {

        // Buffering to solve problems frameworks, like header() in this and not a solid return.
        ob_start();
    
        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);
    
        echo $output;
    }   

    public function addModuleRecommendedMaterials($tplMain) {
        $tpl = new ilTemplate("tpl.student_recommender.html", true, true, self::PLUGIN_DIR); //set to true true later

        //placeholder function for recommendation
        //array of [obj_id, section_id, material_type, [subtags], [from, to]] blocks
        //$materials = array(
        //    array(1,"script", array("Semantic Knowledge", "Graphs and Networks", "Sublayer Performance Review Attacks"), array(3,5), 50),
        //    array(2, "webr", array("Process Trees","Process Mining"), array(null, null), 2.52),
        //);
    
        $materials = $this->recommender->getTagOnlyRecommend();
        $this->debug_to_console($materials, "RECOMMENDED MATERIALS");
        $this->debug_to_console($materials, "RECOMMENDED MATERIALS");

        //Sort materials by last entry (match)
        if ($materials == null){
            $tplMain->setVariable("RECOMMENDATIONS", "");
            return $tplMain;
        }
        usort($materials, function($a, $b) {
            return !($a[5] <=> $b[5]);
        });
        
        $ilObjGUI = new ilObjCourseGUI("", $this->crs_id, true, false);
        $CourseContent = new ilRecSysListMaterials($ilObjGUI);
        
        foreach ($materials as $material) {
            
            //$material_tags = ilRecSysPageUtils::getMaterialTagEntries($item['obj_id'], $file_type == null ?  $material_type : $file_type);

            foreach ($material[3] as $subtag){
                $tpl->setCurrentBlock("Subtags");
                $tpl->setVariable("TAG", $subtag);
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("RecommendedMaterials");
            $tpl->setVariable("SECTION", $material[1]);
            $tpl->setVariable("FROM", $material[4][0]);
            $tpl->setVariable("TO", $material[4][1]);
            $tpl->setVariable("MATCH", round($material[5]));

            $this->debug_to_console($material[0], "MATERIAL_ID");
            $item = $this->course_items_map[$material[0]];
            $tpl->setVariable("ITEM_ID", $item["obj_id"]);
            $this->debug_to_console($item["obj_id"], "ITEM_ID");
            $tpl->setVariable("MATERIAL_TYPE", $item["type"]);
            $tpl->setVariable("FILE_TYPE", $material[2]);
            $itemHtml = $CourseContent->getHtmlItem($item);
            $tpl->setVariable("ITEM_HTML", $itemHtml);
            $tpl->parseCurrentBlock(); 
        }
        $tpl->setVariable("RECSYS_STUDENT_CLICK", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_STUDENT_CLICK));
        $tplMain->setVariable("RECOMMENDATIONS", $tpl->get());
        return $tplMain;
    }
}
