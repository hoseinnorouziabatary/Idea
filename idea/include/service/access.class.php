<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_Access extends Spmh_Service
{
    private $_sIdeaTable;
    private $_sIdeaUserTable;
    private $_sIdeaEvaluatorTable;

    private $_aIdea;

    private $_iUserId;
    private $_iIdeaId;

    private $_bIsLibrarian = false;
    private $_bIsEvaluator = false;
    private $_bIsMainCreator = false;
    private $_bIsGroupCreator = false;

    private $_oIdeaEnum;

    private $_aAccess = [
        'show' => false,
        'edit' => false,
        'delete' => false,
        'change_status' => false,
        'comment_private' => false,
        'comment_public' => false,
        'comment_private_like_dislike' => false,
        'comment_public_like_dislike' => false,
        'like_dislike' => false
    ];

    private $_aMethods = [
        'show' => 'show',
        'edit' => 'edit',
        'delete' => 'delete',
        'changeStatus' => 'change_status',
        'commentPrivate' => 'comment_private',
        'commentPublic' => 'comment_public',
        'commentPrivateLikeDislike' => 'comment_private_like_dislike',
        'commentPublicLikeDislike' => 'comment_public_like_dislike',
        'likeDislike' => 'like_dislike'
    ];

    public function __construct(){
        $this->_sIdeaTable = Spmh::getT('idea');
        $this->_sIdeaUserTable = Spmh::getT('idea_user');
        $this->_sIdeaEvaluatorTable = Spmh::getT('idea_evaluator');

        $this->_oIdeaEnum = Spmh::getService('idea.enum');
    }

    public function __call($sName, $aArguments){
        $sAccessParam = $this->_aMethods[$sName] ?? null;

        if($sAccessParam){
            call_user_func_array([$this, 'process'], $aArguments);
            return $this->_aAccess[$sAccessParam];
        }

        throw new Exception('invalid access param');
    }
    
    private function isProcessed($iIdeaId, $iUserId){
        return $iIdeaId == $this->_iIdeaId && $iUserId == $this->_iUserId;
    }

    private function process($iIdeaId, $iUserId = null)
    {
        if($this->isProcessed($iIdeaId, $iUserId)) return $this;

        $this->_iIdeaId = intval($iIdeaId);
        $this->_iUserId = $iUserId ?? Spmh::getUserId();

        if (!empty($this->_iIdeaId) && !empty($this->_iUserId) && $this->ideaExists()) {
            $this->processAdmin();
            if (!$this->_aAccess['show']) {
                if ($this->determineRole()) {
                    $this->processShow();
                    $this->processLikeDisLike();
                    $this->processEdit();
                    $this->processChangeStatus();
                    $this->processCommentPrivate();
                    $this->processCommentPublic();
                    $this->processCommentPrivateLikeDisLike();
                    $this->processCommentPublicLikeDislike();
                }
            }
        }
        return $this;
    }

    private function ideaExists(){
        return !empty($this->database()
            ->select("idea.idea_id")
            ->from($this->_sIdeaTable, 'idea')
            ->where("idea.idea_id = {$this->_iIdeaId}")
            ->execute('getField'));
    }

    private function processAdmin(){
        if (Spmh::getUserParam('idea.can_access_all_idea')) {
            $this->_aAccess['show'] = true;
            $this->_aAccess['edit'] = true;
            $this->_aAccess['delete'] = true;
            $this->_aAccess['change_status'] = true;
            $this->_aAccess['comment_private'] = true;
            $this->_aAccess['comment_public'] = true;
            $this->_aAccess['comment_private_like_dislike'] = true;
            $this->_aAccess['comment_public_like_dislike'] = true;
            $this->_aAccess['like_dislike'] = true;
        }
    }

    private function processShow(){
        if (
            $this->_bIsMainCreator ||
            $this->_bIsEvaluator ||
            $this->_bIsGroupCreator ||
            $this->_bIsLibrarian ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusAccept ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusRunningProject ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusDoneProject ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusSelectedForProposal ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusRejectedProposal
        ) $this->_aAccess['show'] = true;
    }

    private function processLikeDisLike(){
        if (
            $this->_bIsMainCreator ||
            $this->_bIsEvaluator ||
            $this->_bIsGroupCreator ||
            $this->_bIsLibrarian ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusAccept ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusRunningProject ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusDoneProject ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusSelectedForProposal ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusRejectedProposal
        ) $this->_aAccess['like_dislike'] = true;
    }

    private function processEdit(){
        if (
            $this->_bIsLibrarian ||
            (
                $this->_bIsMainCreator &&
                (
                    $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusPending ||
                    $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusNeedToComplete
                )
            )
        ) $this->_aAccess['edit'] = true;
    }

    private function processChangeStatus(){
        if ($this->_bIsLibrarian) $this->_aAccess['change_status'] = true;
    }

    private function processCommentPrivate(){
        if (
            $this->_bIsMainCreator ||
            $this->_bIsEvaluator ||
            $this->_bIsGroupCreator ||
            $this->_bIsLibrarian
        )  $this->_aAccess['comment_private'] = true;
    }

    private function processCommentPrivateLikeDisLike(){
        if (
            $this->_bIsMainCreator ||
            $this->_bIsEvaluator ||
            $this->_bIsGroupCreator ||
            $this->_bIsLibrarian
        ) $this->_aAccess['comment_private_like_dislike'] = true;
    }

    private function processCommentPublic(){
        if (
            $this->_bIsMainCreator ||
            $this->_bIsEvaluator ||
            $this->_bIsGroupCreator ||
            $this->_bIsLibrarian   ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusAccept ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusRunningProject ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusDoneProject ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusSelectedForProposal ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusRejectedProposal
        ) $this->_aAccess['comment_public'] = true;
    }

    private function processCommentPublicLikeDisLike(){
        if (
            $this->_bIsMainCreator ||
            $this->_bIsEvaluator ||
            $this->_bIsGroupCreator ||
            $this->_bIsLibrarian   ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusAccept ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusRunningProject ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusDoneProject ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusSelectedForProposal ||
            $this->_aIdea["idea_status"] == $this->_oIdeaEnum->idea::statusRejectedProposal
        ) $this->_aAccess['comment_public_like_dislike'] = true;
    }

    private function determineRole(){
        $sUserAccess = $this->database()
            ->select("user.idea_user_user_id")
            ->from($this->_sIdeaUserTable, 'user')
            ->where("user.idea_id = idea.idea_id AND user.idea_user_user_id = " . $this->_iUserId)
            ->limit(1)
            ->execute('');

        $sEvaluatorAccess = $this->database()
            ->select("evaluator.idea_evaluator_user_id")
            ->from($this->_sIdeaEvaluatorTable, 'evaluator')
            ->where("evaluator.idea_id = idea.idea_id AND evaluator.idea_evaluator_user_id = " . $this->_iUserId)
            ->limit(1)
            ->execute('');

        $this->_aIdea = $this->database()
            ->select("
                idea.*,
                ({$sUserAccess}) AS user,
                ({$sEvaluatorAccess}) AS evaluator
            ")
            ->from($this->_sIdeaTable, 'idea')
            ->where("idea.idea_id = {$this->_iIdeaId}")
            ->execute('getRow');

        if(empty($this->_aIdea)) return false;


        $aLibrarianUserIdList = Spmh::getService('user.group.setting')->getUserIdListBySetting('idea.is_librarian');

        $this->_bIsLibrarian = in_array($this->_iUserId, $aLibrarianUserIdList);
        $this->_bIsMainCreator = $this->_aIdea['idea_created_user_id'] == $this->_iUserId;
        $this->_bIsGroupCreator = $this->_aIdea['user'] == $this->_iUserId;
        $this->_bIsEvaluator = $this->_aIdea['evaluator'] == $this->_iUserId;

        return true;
    }
}

?>