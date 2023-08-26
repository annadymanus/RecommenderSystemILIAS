<?php
/**
 * @author Dasha
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */

 class ilRecSysModelBibliography{

    private static $instance;
    private static $biblCounter;

    var $ilDB;
    
    private $bibl_id;
    private $obj_id;
    private $difficulty;
    private $rating_count;

    //------------------------------------------------------------------
    private function __construct($bibl_id, $obj_id, $difficulty, $rating_count){
        // global definitions
        global $ilDB;
        $this->ilDB = $ilDB;
      
        // object definitions
        $this->bibl_id = $bibl_id;
        $this->obj_id = $obj_id;
        $this->difficulty = $difficulty;
        $this->rating_count = $rating_count;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            // set instance to latest Weblink stored in Table;
            self::$biblCounter = self::getLastBiblId();
            // see wether there is a latest Weblink stored in the Table
            if(self::$biblCounter == 0){
                // initialize instance with a first dummy object not yet inserted
                self::$instance = new self(self::$biblCounter, 0, 0, 0);
            } else {
                // initialize everything with the last Weblink that was stored inside the database
                $bibliography= self::getBiblById(self::$biblCounter);
                self::$instance = new self($bibliography->bibl_id, $bibliography->obj_id, $bibliography->difficulty, $bibliography->rating_count);
            }
        }
        return self::$instance;
    }

    private function clone() {
        // Private clone method to prevent cloning of the instance
    }

    private function __wakeup() {
        // Private wakeup method to prevent unserialization of the instance
    }

    // --------------------------------------------------------------

        /**
     * class function that gets the lastBiblId-attribute from the table ui_uihk_recsys_m_c_bib
     */
    private static function getLastBiblId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT bibl_id FROM ui_uihk_recsys_m_c_bib ORDER BY bibl_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_bibl_id = 0;
        } else {
            $last_bibl_id = $ilDB->fetchAssoc($queryResult);
            $last_bibl_id = $last_bibl_id['bibl_id'];
        }
        return $last_bibl_id;
    }

        /**
     * class function that gets the last Bibliography object from the table ui_uihk_recsys_m_c_bib
     */
    public static function getBiblById($bibl_id) {
        $queryResult = self::$ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_bib WHERE bibl_id = ".self::$ilDB->quote($bibl_id, "integer"));
        $biblography = self::$ilDB->fetchObject($queryResult);
        return $biblography;
    }

    /**
     * class function that increments the unique counter of the class. This is done to produce unique ids for the ui_uihk_recsys_m_c_w table
     */
    public static function incrementBiblCounter() {
        self::$biblCounter++;
        return self::$biblCounter;
    }

    // ----------------------------------------------------------------------
    /**
     * functions that implement queries to the db
     */

    /**
     * get a bibliography element by its id, this is done by initializing the values of "this" object with the values stored in the table.
     */
    public function getBibliography($bibl_id){
        $queryResult = $this->ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_bib WHERE bibl_id = " . $this->ilDB->quote($bibl_id, "integer"));
        $bibliography = $this->ilDB->fetchObject($queryResult);
        $this->bibl_id = $bibliography->bibl_id;
        $this->obj_id = $bibliography->obj_id;
        $this->difficulty = $bibliography->difficulty;
        $this->rating_count = $bibliography->rating_count;
        return $this;
    }
    
}
 
?>