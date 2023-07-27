<?php

/** 
 * have a look in Services/Container/classes/class.ilContainerObjectiveGUI.php
 * copied from protected function renderObjective()
 */

include_once('./Services/Object/classes/class.ilObjectActivation.php');

class ilRecSysListMaterials extends ilContainerContentGUI
//class ilLeapGetListItemHtml
{   

    public function getAllItems($a_objective_id)
    {
        return ilObjectActivation::getItemsByObjective($a_objective_id);
    }
    public function getHtmlItem($item)
    {   

        $ItemObject = $this->getItemGUI($item);
        $ItemObject->enableIcon(true);
        $ItemObject->enableProperties(false);
        $ItemObject->enableCommands(True, True);
    	$ItemObject->enableDescription(True);
    	//$ItemObject->addCustomProperty("<b>hallo<b>", "<b>welt<b>");

        $itemHtml = $ItemObject->getListItemHTML(
            $item['ref_id'],
            $item['obj_id'],
            $item['title'],
            $item['description']
        );

        return $itemHtml;

    }

    // "contains 1 abstract method and must therefore be declared abstract or implement the remaining methods (ilContainerContentGUI::getMainContent)"
    public function getMainContent() {
        return True;
    }


    /**
     *  used from Modules/Course/classes/class.ilCourseObjectiveMaterials.php "_getAssignedMaterials($a_objective_id)"
     * to get the material_id by the obj_id
     */
    /*public static function getMaterialRefID($objective_id, $a_obj_id)
    {   
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT ref_id FROM crs_objective_lm " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') .
            " AND objective_id = " . $ilDB->quote($objective_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = $row->ref_id;
        }
        if (count($ref_ids) != 1)
            return False;
        return $ref_ids[0];
    }*/



}