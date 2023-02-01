<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_Like extends Spmh_Service
{
    private $_oIdea;
    private $_oLikeProcess;
    private $_oLike;
    private $_oIdeaEnum;
    private $_oIdeaAccess;

    public function __construct()
    {
        $this->_oIdea = Spmh::getService('idea');
        $this->_oLikeProcess = Spmh::getService('like.process');
        $this->_oLike = Spmh::getService('like');
        $this->_oIdeaEnum = Spmh::getService('idea.enum');
        $this->_oIdeaAccess = Spmh::getService('idea.access');
    }

    private function checkModule(){
        return Spmh::isModule('like');
    }

    public function like($sTypeId, $iIdeaId, $iItemId = null){
        $iIdeaId = intval($iIdeaId);
        $sTypeId = $this->hasAccess($sTypeId, $iIdeaId);

        if (!$this->checkModule() || empty($sTypeId)) return false;

        return $this->_oLikeProcess->add(
            $sTypeId,
            $iItemId ?? $iIdeaId,
            Spmh::getUserId(),
            null,
            $iItemId ? $this->_oIdeaEnum->like::typeModuleComment : $this->_oIdeaEnum->like::typeModuleIdea
        );
    }

    public function removeLike($sTypeId, $iIdeaId, $iItemId = null){
        $iIdeaId = intval($iIdeaId);
        $sTypeId = $this->hasAccess($sTypeId, $iIdeaId);

        if (!$this->checkModule() || empty($sTypeId)) return false;

        return $this->_oLikeProcess->delete($sTypeId, $iItemId ?? $iIdeaId);
    }

    public function dislike($sTypeId, $iIdeaId, $iItemId = null){
        $iIdeaId = intval($iIdeaId);
        $sTypeId = $this->hasAccess($sTypeId, $iIdeaId);

        if (!$this->checkModule() || empty($sTypeId)) return false;

        return $this->_oLikeProcess->doAction(
            2,
            $sTypeId,
            $iItemId ?? $iIdeaId,
            $iItemId ? $this->_oIdeaEnum->like::typeModuleComment : $this->_oIdeaEnum->like::typeModuleIdea
        );
    }

    public function removeDislike($sTypeId, $iIdeaId, $iItemId = null){
        $iIdeaId = intval($iIdeaId);
        $sTypeId = $this->hasAccess($sTypeId, $iIdeaId);

        if (!$this->checkModule() || empty($sTypeId)) return false;

        return $this->_oLikeProcess->removeAction(
            2,
            $sTypeId,
            $iItemId ?? $iIdeaId,
            $iItemId ? $this->_oIdeaEnum->like::typeModuleComment : $this->_oIdeaEnum->like::typeModuleIdea
        );
    }

    public function getLikes($sType, $iIdeaId, $iLimit = 4, $bLoadCount = false){
        $iIdeaId = intval($iIdeaId);

        if (!$this->checkModule() || empty($sType)) return false;

        return $this->_oLike->getLikesForFeed($sType, $iIdeaId, false, $iLimit, $bLoadCount);
    }

    private function hasAccess($sType, $iIdeaId){
        if ($sType == $this->_oIdeaEnum->like::typeIdea && $this->_oIdeaAccess->likeDisLike($iIdeaId)){
            $sAccessType = 'idea';
        }else if (
            $sType == $this->_oIdeaEnum->like::typeCommentPrivate && $this->_oIdeaAccess->commentPrivateLikeDislike($iIdeaId) ||
            $sType == $this->_oIdeaEnum->like::typeCommentPublic && $this->_oIdeaAccess->commentPublicLikeDislike($iIdeaId)
        ){
            $sAccessType = 'feed_mini';
        }else
            return false;

        return $sAccessType;
    }
}
?>