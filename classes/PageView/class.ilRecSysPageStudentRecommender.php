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
    
    private $course_items_map;
    private $RecSysCourse;
    private $RecSysStudent;
    private $plugin;
    private $tree;
    
    
    public function __construct( $crs_id, $RecSysCourse, $RecSysStudent) {
        global $ilCtrl;
        
        $this->ctrl         = $ilCtrl;
        $this->crs_id       = $crs_id;       
        $this->il_crs_id       = ilObject::_lookupObjectId($crs_id);
        $this->RecSysCourse   = $RecSysCourse;
        $this->RecSysStudent  = $RecSysStudent;

        $courseObject = new ilObjCourse($crs_id);
        $all_course_items = ilRecSysPageUtils::getItemsOfCourse($courseObject);
        $course_items_map = array();
        foreach ($all_course_items as $item){
            $course_items_map[$item['obj_id']] = $item;
        }
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
        $tpl = new ilTemplate("tpl.student_recommender.html", true, false, self::PLUGIN_DIR); //set to true true later

        //placeholder function for recommendation
        //array of [section_id, material_type, [subtags], [from, to]] blocks
        $materials = array(
            array(0,"script", array("Semantic Knowledge", "Graphs and Networks", "Sublayer Performance Review Attacks"), array(3,5)),
            array(1, "webr", array("Process Trees","Process Mining"), array(null, null)),
        );
        //$materials = getRecommendations()

        $ilObjGUI = new ilObjCourseGUI("", $this->crs_id, true, false);
        $CourseContent = new ilRecSysListMaterials($ilObjGUI);
        
        $i = 0;
        foreach ($materials as $material) {
            
            //$material_tags = ilRecSysPageUtils::getMaterialTagEntries($item['obj_id'], $file_type == null ?  $material_type : $file_type);
            $this->debug_to_console($material[2]);
            foreach ($material[2] as $subtag){
                $tpl->setCurrentBlock("Subtags");
                $tpl->setVariable("TAG", $subtag);
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("RecommendedMaterials");
            $tpl->setVariable("SECTION", $material[0]);
            $tpl->setVariable("FROM", $material[3][0]);
            $tpl->setVariable("TO", $material[3][1]);

            //TODO: get obj from material, IMPLEMENT IN UTILS
            //PLACEHOLDER:
            if($i==0){
                $item = $this->course_items_map[342];
            }
            else{
                $item = $this->course_items_map[347];
            }
            $this->debug_to_console($i);
            $this->debug_to_console($item);

            $tpl->setVariable("ITEM_ID", $item["obj_id"]);
            $itemHtml = $CourseContent->getHtmlItem($item);
            $tpl->setVariable("ITEM_HTML", $itemHtml);
            $tpl->parseCurrentBlock(); 
            $i++;               
        }

        $tplMain->setVariable("RECOMMENDATIONS", $tpl->get());
        return $tplMain;
    }
    
}
