<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');


class Idea_Service_Callback extends Spmh_Service{

	private $_sIdeaTable;

	private $_oIdea;

	public function __construct()
	{
		$this->_sIdeaTable = Spmh::getT('idea');

		$this->_oIdea = Spmh::getService('idea');
	}

	public function getCommentItemPrivate($iId){
		$aIdea = $this->_oIdea->getById($iId);

		if(empty($aIdea)) return false;

		return [
			'comment_item_id' => $aIdea['idea_id'],
			'comment_user_id' => $aIdea['idea_created_user_id'],
			'comment_view_id' => 0
		];
	}

	public function getCommentItemPublic($iId){
		$aIdea = $this->_oIdea->getById($iId);

		if(empty($aIdea)) return false;

		return [
			'comment_item_id' => $aIdea['idea_id'],
			'comment_user_id' => $aIdea['idea_created_user_id'],
			'comment_view_id' => 0
		];
	}

	public function addCommentPrivate(){}
	public function addCommentPublic(){}

	public function getLikeItem($iId){
		$aIdea = $this->_oIdea->getById($iId);

		if(empty($aIdea)) return false;

		return [
			'like_item_id' => $aIdea['idea_id'],
			'like_user_id' => $aIdea['idea_created_user_id']
		];
	}

	public function addLike($iId, $bDoNotSendEmail = false){
		$aIdea = $this->_oIdea->getById($iId);

		if(empty($aIdea)) return false;

		return $this->database()->update(
			$this->_sIdeaTable,
			['idea_total_like' => $aIdea['idea_total_like'] + 1],
			'idea_id = ' . $aIdea['idea_id']
		);
	}

	public function deleteLike($iId, $bDoNotSendEmail = false){
		$aIdea = $this->_oIdea->getById($iId);

		if(empty($aIdea)) return false;

		return $this->database()->update(
			$this->_sIdeaTable,
			['idea_total_like' => $aIdea['idea_total_like'] - 1],
			'idea_id = ' . $aIdea['idea_id']
		);
	}

	public function addAction($sActionTypeId, $sItemTypeId, $iItemId, $sModuleId){
		$aIdea = $this->_oIdea->getById($iItemId);

		if(empty($aIdea)) return false;

		return $this->database()->update(
			$this->_sIdeaTable,
			['idea_total_dislike' => $aIdea['idea_total_dislike'] + 1],
			'idea_id = ' . $aIdea['idea_id']
		);
	}

	public function deleteAction($sActionTypeId, $sItemTypeId, $iItemId, $sModuleId){
		$aIdea = $this->_oIdea->getById($iItemId);

		if(empty($aIdea)) return false;

		return $this->database()->update(
			$this->_sIdeaTable,
			['idea_total_dislike' => $aIdea['idea_total_dislike'] - 1],
			'idea_id = ' . $aIdea['idea_id']
		);
	}

	public function commentLikeExtraParams($aComment){
		return [
			'disable_mail_and_notification' => true
		];
	}
}
?>
