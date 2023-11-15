<?php

/**
 * @author Dasha
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */
class ilRecSysModelTest {
  
    var $ilDB;
   
    private $test_id;
    //private $question_no;
    private $question_id;
    private $obj_id;
    //private $difficulty;
    //private $rating_count;
    private $tag_id;
    // --------------------------------------------------------

    //My assumption is that it's better to use not just $question_no but $test_id and $question_id together
    //because it's possible to have the same question in different tests
    //Moreover, I've added a new table ui_uihk_recsys_q_a_t to store tags for questions in tests to the database (please check it)

    // --------------------------------------------------------


    // Function to add a tag to a question in a test
    public function addTagToQuestionInTest($test_id, $obj_id, $question_id, $tag_id) {
        global $ilDB;
        $ilDB->insert("ui_uihk_recsys_q_a_t", array(
            "test_id" => array("integer", $test_id),
            "obj_id" => array("integer", $obj_id),
            "question_id" => array("integer", $question_id),
            "tag_id" => array("integer", $tag_id)
        ));
    }

    // Function to remove a tag from a question in a test
    public function removeTagFromQuestionInTest($test_id, $obj_id, $question_id, $tag_id) {
        global $ilDB;
        $ilDB->manipulate("DELETE FROM ui_uihk_recsys_q_a_t WHERE test_id = ".$ilDB->quote($test_id, "integer")." AND obj_id = ".$ilDB->quote($obj_id, "integer")." AND question_id = ".$ilDB->quote($question_id, "integer")." AND tag_id = ".$ilDB->quote($tag_id, "integer"));
    }

    // Function to get all tags of a question in a test
    public function getTagsOfQuestionInTest($test_id, $question_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_q_a_t WHERE test_id = ".$ilDB->quote($test_id, "integer")." AND question_id = ".$ilDB->quote($question_id, "integer"));
        $fetched_tags = $ilDB->fetchObject($queryResult);
        $tags = new ilRecSysModelTest(
            $fetched_tags->test_id,
            $fetched_tags->obj_id,
            $fetched_tags->question_id,
            $fetched_tags->tag_id);
        return $tags;
    }

    // Function to get all tags for a test
    public function getTagsOfTest($test_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_q_a_t WHERE test_id = ".$ilDB->quote($test_id, "integer"));
        $fetched_tags = $ilDB->fetchObject($queryResult);
        $tags = new ilRecSysModelTest(
            $fetched_tags->test_id,
            $fetched_tags->obj_id,
            $fetched_tags->question_id,
            $fetched_tags->tag_id);
        return $tags;
    }

    // Function to get all tags for a question
    public function getTagsOfQuestion($question_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_q_a_t WHERE question_id = ".$ilDB->quote($question_id, "integer"));
        $fetched_tags = $ilDB->fetchObject($queryResult);
        $tags = new ilRecSysModelTest(
            $fetched_tags->test_id,
            $fetched_tags->obj_id,
            $fetched_tags->question_id,
            $fetched_tags->tag_id);
        return $tags;
    }

    // Function to update a tag of a question in a test
    public function updateTagOfQuestionInTest($test_id, $obj_id, $question_id, $tag_id) {
        global $ilDB;
        $ilDB->update("ui_uihk_recsys_q_a_t", array(
            "tag_id" => array("integer", $tag_id)
        ), array(
            "test_id" => array("integer", $test_id),
            "obj_id" => array("integer", $obj_id),
            "question_id" => array("integer", $question_id)
        ));
    }

    // Function to get all questions of a test
    public function getQuestionsOfTest($test_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_q_a_t WHERE test_id = ".$ilDB->quote($test_id, "integer"));
        $fetched_questions = $ilDB->fetchObject($queryResult);
        $questions = new ilRecSysModelTest(
            $fetched_questions->test_id,
            $fetched_questions->obj_id,
            $fetched_questions->question_id,
            $fetched_questions->tag_id);
        return $questions;
    }

    public static function getLastTestId() {

        global $ilDB;
        $queryResult = $ilDB->query("SELECT test_id FROM ui_uihk_recsys_q_a_t ORDER BY test_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_test_id = 0;
        } else {
            $last_test_id = $ilDB->fetchAssoc($queryResult);
            $last_test_id = $last_test_id['test_id'];
        }
        return $last_test_id;

    }



}

?>