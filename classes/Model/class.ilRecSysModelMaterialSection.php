<?php

//@author Anna Eschbach-Dymanus

abstract class ilRecSysModelMaterial{

    abstract protected static function fetchByMaterialID($mat_id);
    abstract protected function getMaterial();
    abstract protected function addNewMaterial();
    abstract protected function updateMaterial();
    abstract protected static function deleteMaterial($mat_id);
    abstract protected function get_id();
}

abstract class ilRecSysModelMaterialSimple extends ilRecSysModelMaterial{
    abstract protected static function fetchByObjID($obj_id);
}

abstract class ilRecSysModelMaterialFromTo extends ilRecSysModelMaterial{
    abstract protected static function fetchByObjID($obj_id, $from, $to);
}
