<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_Proposal extends Spmh_Service
{
    private $_sIdeaProposalTable;

    public function __construct()
    {
        $this->_sIdeaProposalTable = Spmh::getT('idea_proposal');
    }

    public function deleteByIdeaId($iIdeaId)
    {
        $iIdeaId = intval($iIdeaId);
        if (!empty($iIdeaId)) {
            $this->database()->delete($this->_sIdeaProposalTable, "idea_id = {$iIdeaId}");
            return $this->database()->affectedRows();
        }
        return false;
    }
}
?>