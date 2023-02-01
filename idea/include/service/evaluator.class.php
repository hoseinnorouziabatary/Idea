<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_Evaluator extends Spmh_Service
{
    private $_sIdeaTable;
    private $_sIdeaEvaluatorTable;
    private $_sListTable;
    private $_sUserTable;
    private $_sOpPostTable;

    private $_aValidationRules;
    private $_aValidationAliases;
    private $_oValidator;

    public function __construct()
    {
        $this->_sIdeaTable = Spmh::getT('idea');
        $this->_sIdeaEvaluatorTable = Spmh::getT('idea_evaluator');
        $this->_sUserTable = Spmh::getT('user');
        $this->_sOpPostTable = Spmh::getT('op_posts');
        $this->_sListTable = Spmh::getT('user_lists');

        $this->_oValidator = Spmh::getLib('validation');

        $iCurrentTime = SPMH_TIME;
        $iCurrentUserId = Spmh::getUserId();
        $this->_aValidationRules = array(
            'idea_id' => "required|exists:{$this->_sIdeaTable},idea_id",
            'idea_evaluator_list_id' => "nullable|exists:{$this->_sListTable},list_id",
            'idea_evaluator_user_id' => "required|exists:{$this->_sUserTable},user_id",
            'idea_evaluator_post_id' => "required|exists:{$this->_sOpPostTable},post_id",
            'idea_evaluator_created' => "default:{$iCurrentTime}|integer",
            'idea_evaluator_created_user_id' => "default:{$iCurrentUserId}|integer",
            'idea_evaluator_modified' => "default:{$iCurrentTime}|integer",
            'idea_evaluator_modified_user_id' => "default:{$iCurrentUserId}|integer",
        );
        $this->_aValidationAliases = array(
            'idea_id' => Spmh::getPhrase("idea.idea_id"),
            'idea_evaluator_list_id' => Spmh::getPhrase("idea.idea_evaluator_list_id"),
            'idea_evaluator_user_id' => Spmh::getPhrase("idea.idea_evaluator_user_id"),
            'idea_evaluator_post_id' => Spmh::getPhrase("idea.idea_evaluator_post_id"),
            'idea_evaluator_created' => Spmh::getPhrase("idea.idea_evaluator_created"),
            'idea_evaluator_created_user_id' => Spmh::getPhrase("idea.idea_evaluator_created_user_id"),
            'idea_evaluator_modified' => Spmh::getPhrase("idea.idea_evaluator_modified"),
            'idea_evaluator_modified_user_id' => Spmh::getPhrase("idea.idea_evaluator_modified_user_id"),
        );
    }

    private function validate($aDataUser)
    {
        return $this->_oValidator->validate($aDataUser, $this->_aValidationRules, $this->_aValidationAliases);
    }

    public function addMultiEvaluator($iIdeaId, $aParams){
        $aValues = array();
        foreach ($aParams as $aMember) {
            $aMember['idea_id'] = $iIdeaId;
            $aValidationResult = $this->validate($aMember);
            if (!$aValidationResult["status"])
                return Spmh_Error::setArray($aValidationResult['error']['all']);
            $aValues[] = $aValidationResult["data"];
        }

        $aFields = array(
            'idea_id',
            'idea_evaluator_list_id',
            'idea_evaluator_user_id',
            'idea_evaluator_post_id',
            'idea_evaluator_created',
            'idea_evaluator_created_user_id',
            'idea_evaluator_modified',
            'idea_evaluator_modified_user_id',
        );
        $this->database()->multiInsert($this->_sIdeaEvaluatorTable, $aFields, $aValues);

        return $this->database()->affectedRows();
    }

    public function deleteByIdeaId($iIdeaId)
    {
        $iIdeaId = intval($iIdeaId);
        if (!empty($iIdeaId)) {
            $this->database()->delete($this->_sIdeaEvaluatorTable, "idea_id = {$iIdeaId}");
            return $this->database()->affectedRows();
        }
        return false;
    }
}
?>