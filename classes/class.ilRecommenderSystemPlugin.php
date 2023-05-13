<?php

include_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");

//is this include necessary? i dont understand what it does...
//include_once ("./Services/EventHandling/classes/class.ilEventHookPlugin.php");


/**
 * User interface hook class
 * 
 * @package ILIAS\Plugins\RecommenderSystem
 * @subpackage UserInterfaceHook
 * @author Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@students.uni-mannheim.de>, Daria ...
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
        
        //if ($a_event != "toTrash")
        //    return True;
        
        // Check if the parameter is an array and if it has a ref_id. If it has no ref_id, it is not a course
        // Naturally... this assumes that we give our courses an attribute ref_id just like they do in Leap...
        
        //if (!is_array($a_parameter) || (!array_key_exists("ref_id", $a_parameter)))
        //    return True;
        
        // Check if the ref_id is numeric. If it is not numeric, it is not a course
        
        //if (!is_numeric($a_parameter["ref_id"]))
        //    return True;

        //So now we know its a course that is to be deleted...

        //Insert here database queries to delete the data from the database when a course is deleted

        //return True;
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
        //insert code that deletes all relevant data from the database when the plugin is uninstalled
	}

}
?>
