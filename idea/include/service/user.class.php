<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_User extends Spmh_Service
{
    private $_sIdeaTable;
    private $_sOpPostTable;
    private $_sUserTable;
    private $_sIdeaUsersTable;

    private $_aValidationRules;
    private $_aValidationAliases;
    private $_oValidator;

    private $_oDate;

    public function __construct()
    {
        $this->_sIdeaTable = Spmh::getT('idea');
        $this->_sOpPostTable = Spmh::getT('op_posts');
        $this->_sUserTable = Spmh::getT('user');
        $this->_sIdeaUsersTable = Spmh::getT('idea_user');

        $this->_oValidator = Spmh::getLib('validation');
        $this->_oDate = Spmh::getLib('date');
        $iCurrentUserId = Spmh::getUserId();
        $iCurrentTime = SPMH_TIME;

        $this->_aValidationRules = array(
            'idea_id' => "required|exists:{$this->_sIdeaTable},idea_id",
            'idea_user_user_id' => "required|exists:{$this->_sUserTable},user_id",
            'idea_user_post_id' => "required|exists:{$this->_sOpPostTable},post_id",
            'idea_user_created' => "default:{$iCurrentTime}|integer",
            'idea_user_created_user_id' => "default:{$iCurrentUserId}|integer",
            'idea_user_modified' => "default:{$iCurrentTime}|integer",
            'idea_user_modified_user_id' => "default:{$iCurrentUserId}|integer",
        );
        $this->_aValidationAliases = array(
            'idea_id' => Spmh::getPhrase("idea.idea_id"),
            'idea_user_user_id' => Spmh::getPhrase("idea.idea_user_user_id"),
            'idea_user_post_id' => Spmh::getPhrase("idea.idea_user_post_id"),
            'idea_user_created' => Spmh::getPhrase("idea.idea_user_created"),
            'idea_user_created_user_id' => Spmh::getPhrase("idea.idea_user_created_user_id"),
            'idea_user_modified' => Spmh::getPhrase("idea.idea_user_modified"),
            'idea_user_modified_user_id' => Spmh::getPhrase("idea.idea_user_modified_user_id"),
        );
    }

    private function validate($aDataUser)
    {
        return $this->_oValidator->validate($aDataUser, $this->_aValidationRules, $this->_aValidationAliases);
    }

    public function addMultiUser($iIdeaId, $aDataUser)
    {
        $aValues = array();
        foreach ($aDataUser as $aMember) {
            $aMember['idea_id'] = $iIdeaId;
            $aValidationResult = $this->validate($aMember);
            if (!$aValidationResult["status"])
                return Spmh_Error::setArray($aValidationResult['error']['all']);
            $aValues[] = $aValidationResult["data"];
        }
        $aFields = array(
            'idea_id',
            'idea_user_user_id',
            'idea_user_post_id',
            'idea_user_created',
            'idea_user_created_user_id',
            'idea_user_modified',
            'idea_user_modified_user_id'
        );
        $this->database()->multiInsert($this->_sIdeaUsersTable, $aFields, $aValues);

        return $this->database()->affectedRows();
    }

    public function editMultiUser($iIdeaId,$aDataUser){
        $aValues = array();
        if (!empty($aDataUser)) {
            foreach ($aDataUser as $aMember) {
                $aMember['idea_id'] = $iIdeaId;
                $aValidationResult = $this->validate($aMember);
                if (!$aValidationResult["status"])
                    return Spmh_Error::setArray($aValidationResult['error']['all']);
                $aValues[] = $aValidationResult["data"];
            }
            $aFields = array(
                'idea_id',
                'idea_user_user_id',
                'idea_user_post_id',
                'idea_user_created',
                'idea_user_created_user_id',
                'idea_user_modified',
                'idea_user_modified_user_id'
            );
        }

        $aResult = $this->getByIdeaId($iIdeaId);
        if(!empty($aResult)){
            if(!$this->deleteByIdeaId($iIdeaId)) return false;
        }
        if(!empty($aValues)) {
            $this->database()->multiInsert($this->_sIdeaUsersTable, $aFields, $aValues);
            return $this->database()->affectedRows();
        }
        return true;
    }

    public function getByIdeaId($iIdeaId)
    {
        $iIdeaId = intval($iIdeaId);
        if (empty($iIdeaId)) return false;

        return $this->database()
            ->select("*")
            ->from($this->_sIdeaUsersTable, 'user')
            ->where("user.idea_id = '{$iIdeaId}'")
            ->execute('getRow');
    }

    public function deleteByIdeaId($iIdeaId)
    {
        $iIdeaId = intval($iIdeaId);
        if (!empty($iIdeaId)) {
            $this->database()->delete($this->_sIdeaUsersTable, "idea_id = {$iIdeaId}");
            return $this->database()->affectedRows();
        }
        return false;
    }
} ?>