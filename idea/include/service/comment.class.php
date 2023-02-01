<?php
/**
 * [SPMH_HEADER]
 */
defined('SPMH') or exit('NO DICE!');

class Idea_Service_Comment extends Spmh_Service{

    private $_oCommentProcess;
    private $_oLog;
    private $_oIdeaEnum;
    private $_oIdeaAccess;

    public function __construct()
    {
        $this->_oCommentProcess = Spmh::getService('comment.process');
        $this->_oLog = Spmh::getService('idea.log');
        $this->_oIdeaEnum = Spmh::getService('idea.enum');
        $this->_oIdeaAccess = Spmh::getService('idea.access');
    }

    public function add($sTypeId, $iIdeaId, $sContent, $iParentId = 0, $bTransaction = true){
        $iIdeaId = intval($iIdeaId);

        if(empty($iIdeaId) || empty($sTypeId)) return false;

        if ($sTypeId == $this->_oIdeaEnum->comment::typePrivate && !$this->_oIdeaAccess->commentPrivate($iIdeaId)){
            return Spmh_Error::set(Spmh::getPhrase('idea.idea_you_do_not_have_access_private_idea'));
        }else if($sTypeId == $this->_oIdeaEnum->comment::typePublic && !$this->_oIdeaAccess->commentPublic($iIdeaId)){
            return Spmh_Error::set(Spmh::getPhrase('idea.idea_you_do_not_have_access_public_idea'));
        }

        if ($bTransaction) $this->database()->beginTransaction();

        $iResult = $this->_oCommentProcess->add(array (
            'type' => $sTypeId,
            'item_id' => $iIdeaId,
            'parent_id' => $iParentId,
            'is_via_feed' => '0',
            'text' => $sContent
        ));

        if ($iResult){
            $this->_oLog->add(array(
                'idea_log_item_id' => $iResult,
                'idea_log_item_type' => 'add_comment_success',
                'idea_log_data' => array(
                    'idea_id' => $iIdeaId,
                    'type_id' => $sTypeId,
                    'content' => $sContent,
                    'parent_id' => $iParentId
                )
            ));
            if(Spmh_Error::isPassed()){
                if ($bTransaction) $this->database()->commit();
                return $iResult;
            }
        }

        if ($bTransaction) $this->database()->rollback();
        return false;
    }

    public function get($sTypeId, $iIdeaId){
        $iIdeaId = intval($iIdeaId);

        if(empty($iIdeaId) || empty($sTypeId)) return false;

        if ($sTypeId == $this->_oIdeaEnum->comment::typePrivate && !$this->_oIdeaAccess->commentPrivate($iIdeaId)){
            return Spmh_Error::set(Spmh::getPhrase('idea.idea_you_do_not_have_access_private_idea'));
        }else if($sTypeId == $this->_oIdeaEnum->comment::typePublic && !$this->_oIdeaAccess->commentPublic($iIdeaId)){
            return Spmh_Error::set(Spmh::getPhrase('idea.idea_you_do_not_have_access_public_idea'));
        }
        $aComments = Spmh::getService('comment')->getCommentsForFeed($sTypeId, $iIdeaId, 10000);

        return $this->cleanGetValues($aComments);
    }

    private function cleanGetValues($aComments){
        foreach ($aComments as $iKey => $aComment){
            $aChildren = null;
            if(count($aComment['children']['comments'])){
                $aChildren = $this->cleanGetValues($aComment['children']['comments']);
            }
            $aComments[$iKey] = [
                'text' => $aComment['text'],
                'total_like' => $aComment['total_like'],
                'total_dislike' => $aComment['total_dislike'],
                'user_image' => $aComment['user_image'],
                'comment_id' => $aComment['comment_id'],
                'user_id' => $aComment['user_id'],
                'user_name' => $aComment['user_name'],
                'type_id' => $aComment['type_id'],
                'item_id' => $aComment['item_id'],
                'children' => $aChildren
            ];
        }
        return $aComments;
    }
}