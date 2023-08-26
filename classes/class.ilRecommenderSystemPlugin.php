<?php

include_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");

//is this include necessary? i dont understand what it does...
//include_once ("./Services/EventHandling/classes/class.ilEventHookPlugin.php");


/**
 * User interface hook class
 * 
 * @package ILIAS\Plugins\RecommenderSystem
 * @subpackage UserInterfaceHook
 * @author Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@students.uni-mannheim.de>
 */
class ilRecommenderSystemPlugin extends ilUserInterfaceHookPlugin
{

    function getPluginName()
    {
        return "RecommenderSystem";
    }

    protected function init()
    {
        // do nothing
    }

    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        // a_component : "Services/Object"
        // a_event     : "toTrash"
        // a_parameter looks like:
        /*
           Array
           (
               [obj_id] => 387
               [ref_id] => 116
               [old_parent_ref_id] => 1
           )
        */

        // Check if its the delete event
        
        if ($a_event != "toTrash")
            return True;
        
        // Check if the parameter is an array and if it has a crs_status. If it has no crs_status, it is not a course
        
        if (!is_array($a_parameter) || (!array_key_exists("crs_status", $a_parameter)))
            return True;
        
        // Check if the ref_id is numeric. If it is not numeric, it is not a course
        
        if (!is_numeric($a_parameter["crs_status"]))
            return True;

        //So now we know its a course that is to be deleted...
        global $ilDB;
        $queryResult = $ilDB->query("DELETE FROM ui_uihk_recsys_courses where crs_id = " . $a_parameter["crs_id"]);
        $queryResult = $ilDB->query("DELETE FROM ui_uihk_recsys_user where crs_id = " . $a_parameter["crs_id"]);
        return True;
    }

	protected function afterActivation()
	{
		// do nothing
	}
	
	/**
	 * Remove all DataBase Entries
	 * @see ilPlugin::beforeUninstall()
	 */
	protected function beforeUninstall()
	{
	    $log = ilLoggerFactory::getLogger('ilRecommenderSystemPlugin');
	    global $ilDB;
	    
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_config");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_config_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_user");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_user_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_courses");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_courses_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_tags");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_tags_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_t_p_m");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_t_p_m_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_o_t_u");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_o_t_u_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_s_t_u");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_s_t_u_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_f_s");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_f_s_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_f_p");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_f_p_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_f_v");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_f_v_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_pic");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_pic_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_w");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_w_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_bib");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_c_bib_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_a_t");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_a_t_seq");
	    $log->info("Uninstalled Recsys");	    
	    return true; // false would indicate that anything went wrong	    
	}

}
?>
