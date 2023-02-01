<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_Idea extends Spmh_Service
{

    private $_sIdeaTable;
    private $_sLabelTable;
    private $_sLabelAssignTable;
    private $_sUserTable;
    private $_sIdeaUserTable;
    private $_sIdeaEvaluatorTable;
    private $_sOpPostAssignTable;
    private $_sOpPostTable;

    private $_aAddValidationRules;
    private $_aEditValidationRules;
    private $_aValidationAliases;

    private $_oIdeaUser;
    private $_oIdeaCategory;
    private $_oIdeaEvaluator;
    private $_oIdeaComment;
    private $_oIdeaProposal;
    private $_oIdeaLog;
    private $_oIdeaEnum;
    private $_oIdeaAccess;

    private $_oDate;
    private $_oValidator;

    private $_oMethodChangeStatus;

    public function __construct()
    {
        $this->_sIdeaTable = Spmh::getT('idea');
        $this->_sLabelTable = Spmh::getT('labels');
        $this->_sLabelAssignTable = Spmh::getT('labels_assign_items');
        $this->_sUserTable = Spmh::getT('user');
        $this->_sIdeaUserTable = Spmh::getT('idea_user');
        $this->_sIdeaEvaluatorTable = Spmh::getT('idea_evaluator');
        $this->_sOpPostAssignTable = Spmh::getT('op_post_assign');
        $this->_sOpPostTable = Spmh::getT('op_posts');

        $this->_oDate = Spmh::getLib('date');
        $this->_oValidator = Spmh::getLib('validation');

        $this->_oIdeaUser = Spmh::getService("idea.user");
        $this->_oIdeaEvaluator = Spmh::getService("idea.evaluator");
        $this->_oIdeaComment = Spmh::getService("idea.comment");
        $this->_oIdeaProposal = Spmh::getService("idea.proposal");
        $this->_oIdeaCategory = Spmh::getService('idea.category');
        $this->_oIdeaEnum = Spmh::getService('idea.enum');
        $this->_oIdeaAccess = Spmh::getService('idea.access');
        $this->_oIdeaLog = Spmh::getService('idea.log');

        $this->_oMethodChangeStatus = Spmh::getService("idea.method.change-status");


        $iCurrentTime = SPMH_TIME;
        $iCurrentUserId = Spmh::getUserId();
        $this->_aAddValidationRules = array(
            'idea_description' => 'required',
            'idea_title' => 'required|max:255',
            'idea_target' => 'required|max:255',
            'idea_status' => 'required|max:50|in:' . implode(',', array_keys($this->_oIdeaEnum->idea->getStatusList())),
            'idea_created' => "default:{$iCurrentTime}|integer",
            'idea_created_user_id' => "default:{$iCurrentUserId}|integer",
            'idea_modified' => "default:{$iCurrentTime}|integer",
            'idea_modified_user_id' => "default:{$iCurrentUserId}|integer",
        );
        $this->_aEditValidationRules = array(
            'idea_description' => 'required',
            'idea_title' => 'required|max:255',
            'idea_target' => 'required|max:255',
            'idea_status' => 'required|max:50|in:' . implode(',', array_keys($this->_oIdeaEnum->idea->getStatusList())),
            'idea_modified' => "default:{$iCurrentTime}|integer",
            'idea_modified_user_id' => "default:{$iCurrentUserId}|integer",
        );
        $this->_aValidationAliases = array(
            'idea_description' => Spmh::getPhrase("idea.idea_description"),
            'idea_title' => Spmh::getPhrase("idea.idea_title"),
            'idea_target' => Spmh::getPhrase("idea.idea_target"),
            'idea_status' => Spmh::getPhrase("idea.idea_status"),
            'idea_created' => Spmh::getPhrase("idea.idea_created"),
            'idea_created_user_id' => Spmh::getPhrase("idea.idea_created_user_id"),
            'idea_modified' => Spmh::getPhrase("idea.idea_modified"),
            'idea_modified_user_id' => Spmh::getPhrase("idea.idea_modified_user_id"),
        );
    }

    private function validate($aValues, $sMode){
        $aValidationRules = $sMode == 'add' ? $this->_aAddValidationRules : $this->_aEditValidationRules;
        return $this->_oValidator->validate($aValues, $aValidationRules, $this->_aValidationAliases);
    }

    public function add($aValues, $aDataUser = null)
    {
        $aValidationResult = $this->validate($aValues,'add');

        if ($aValidationResult['status']) {
            $this->database()->beginTransaction();
            $iResult = $this->database()->insert($this->_sIdeaTable, $aValidationResult['data']);
            if ($iResult) {
                if ($this->_oIdeaCategory->add($iResult, $aValues["idea_category"])){
                    if ($aDataUser){
                        if(!$this->_oIdeaUser->addMultiUser($aDataUser,$iResult)) Spmh_Error::set(Spmh::getPhrase('core.there_is_an_error_in_operation'));
                    }

                    $this->_oIdeaLog->add(array(
                        'idea_log_item_id' => $iResult,
                        'idea_log_item_type' => 'add_idea_success',
                        'idea_log_data' => array(
                            'idea' => $aValidationResult['data'],
                            'users' => $aDataUser,
                            'categories' => $aValues["idea_category"]
                        )
                    ));
                    if(Spmh_Error::isPassed()){
                        $this->database()->commit();
                        return $iResult;
                    }
                }
            }
            $this->database()->rollback();
        } else {
            Spmh_Error::setArray($aValidationResult['error']['all']);
        }
        return false;
    }

    public function edit($iIdeaId, $aValues, $aDataUser = null){
        $iIdeaId = intval($iIdeaId);
        $aIdea = $this->getById($iIdeaId);
        if(empty($aIdea) || !$this->_oIdeaAccess->edit($iIdeaId)) return Spmh_Error::set(Spmh::getPhrase('idea.idea_does_not_exists'));

        $aValues = array_merge($aIdea, $aValues);
        $aValidationResult = $this->validate($aValues,'edit');

        if($aValidationResult['status']){

            if ($aIdea['idea_status'] == $this->_oIdeaEnum->idea::statusNeedToComplete && Spmh::getUserId() == $aIdea["idea_created_user_id"]){
                $aValidationResult['data']['idea_status'] = $this->_oIdeaEnum->idea::statusPending;
            }

            $this->database()->beginTransaction();

            $iResult = $this->database()->update($this->_sIdeaTable, $aValidationResult['data'], "idea_id = {$iIdeaId}");
            if ($iResult && $this->_oIdeaCategory->edit($iIdeaId, $aValues['idea_category'])) {
                if ($aDataUser){
                    if(!$this->_oIdeaUser->editMultiUser($iIdeaId, $aDataUser)) Spmh_Error::set(Spmh::getPhrase('core.there_is_an_error_in_operation'));
                }
                $this->_oIdeaLog->add(array(
                    'idea_log_item_id' => $iIdeaId,
                    'idea_log_item_type' => 'update_idea_success',
                    'idea_log_data' => array(
                        'old_idea' => $aIdea,
                        'values' => $aValues,
                        'users' => $aDataUser,
                        'categories' => $aValues["idea_category"]
                    )
                ));
                if(Spmh_Error::isPassed()){
                    $this->database()->commit();
                    return $iResult;
                }
            }
            $this->database()->rollback();
        }else{
            Spmh_Error::setArray($aValidationResult['error']['all']);
        }
        return false;
    }

    public function delete($iIdeaId, $bTransaction = true)
    {
        $iIdeaId = intval($iIdeaId);
        $aIdeaInfo = $this->getById($iIdeaId);

        if(empty($aIdeaInfo) || !$this->_oIdeaAccess->delete($iIdeaId)) return Spmh_Error::set(Spmh::getPhrase('idea.idea_does_not_exists'));

        if ($bTransaction) $this->database()->beginTransaction();

        $this->_oIdeaUser->deleteByIdeaId($iIdeaId);
        $this->_oIdeaEvaluator->deleteByIdeaId($iIdeaId);
        $this->_oIdeaProposal->deleteByIdeaId($iIdeaId);
        $this->_oIdeaCategory->delete($iIdeaId);

        $this->database()->delete($this->_sIdeaTable, "idea_id = {$iIdeaId}");

        if ($this->database()->affectedRows()) {
            $this->_oIdeaLog->add(array(
                'idea_log_item_id' => $iIdeaId,
                'idea_log_item_type' => 'delete_idea_success',
                'idea_log_data' => array(
                    'idea' => $aIdeaInfo
                )
            ));
            if($bTransaction) $this->database()->commit();
            return true;
        }
        if ($bTransaction) $this->database()->rollback();

        return false;
    }

    public function getById($iIdeaId){
        $iIdeaId = intval($iIdeaId);
        if (empty($iIdeaId)) return false;

        return $this->database()
            ->select("*")
            ->from($this->_sIdeaTable, 'idea')
            ->where("idea.idea_id = '{$iIdeaId}'")
            ->execute('getRow');
    }

    public function get($iIdeaId)
    {
        $iIdeaId = intval($iIdeaId);

        if (empty($iIdeaId)) return false;

        $sCategorySubQuery = $this->database()
            ->select("CONCAT(GROUP_CONCAT(subQuery_labelAssign.label_id), '|', GROUP_CONCAT(subQuery_category.label_title))")
            ->from($this->_sLabelAssignTable, 'subQuery_labelAssign')
            ->join($this->_sLabelTable, 'subQuery_category', "subQuery_category.label_id = subQuery_labelAssign.label_id")
            ->where("subQuery_labelAssign.item_id = idea.idea_id AND subQuery_labelAssign.type_id = '" . $this->_oIdeaEnum->category::categoryLabelType . "'")
            ->execute('');

        $sPostSubQuery = $this->database()
            ->select("subQuery_opPostParent.post_name")
            ->from($this->_sOpPostAssignTable, 'subQuery_opPostAssign')
            ->join($this->_sOpPostTable, 'subQuery_opPost', "subQuery_opPost.post_id = subQuery_opPostAssign.post_id AND NOW() <= subQuery_opPost.exp_date")
            ->join($this->_sOpPostTable, 'subQuery_opPostParent', "subQuery_opPostParent.post_id = subQuery_opPost.parent_id AND NOW() <= subQuery_opPostParent.exp_date")
            ->where("subQuery_opPostAssign.assign_to = idea.idea_created_user_id AND NOW() <= subQuery_opPostAssign.exp_date")
            ->limit(1)
            ->execute('');

        $sUserSubQuery = $this->database()
            ->select("CONCAT(GROUP_CONCAT(subQuery_ideaUser.idea_user_user_id), '|', GROUP_CONCAT(subQuery_user.full_name))")
            ->from($this->_sIdeaUserTable, 'subQuery_ideaUser')
            ->join($this->_sUserTable, 'subQuery_user', 'subQuery_user.user_id = subQuery_ideaUser.idea_user_user_id')
            ->where("subQuery_ideaUser.idea_id = idea.idea_id")
            ->execute('');

        $sEvaluatorSubQuery = $this->database()
            ->select("CONCAT(GROUP_CONCAT(subQuery_ideaEvaluator.idea_evaluator_user_id), '|', GROUP_CONCAT(subQuery_user.full_name))")
            ->from($this->_sIdeaEvaluatorTable, 'subQuery_ideaEvaluator')
            ->join($this->_sUserTable, 'subQuery_user', 'subQuery_user.user_id = subQuery_ideaEvaluator.idea_evaluator_user_id')
            ->where("subQuery_ideaEvaluator.idea_id = idea.idea_id")
            ->execute('');

        $aResult = $this->database()
            ->select("
                idea.*,
                importance.label_title AS importance_title,
                priority.label_title AS priority_title,
                ({$sUserSubQuery}) AS users,
                ({$sEvaluatorSubQuery}) AS evaluators,
                ({$sCategorySubQuery}) AS categories,
                ({$sPostSubQuery}) AS organization_unit
             ")
            ->from($this->_sIdeaTable, 'idea')
            ->leftJoin($this->_sLabelTable, 'importance', 'importance.label_id = idea.idea_importance_id')
            ->leftJoin($this->_sLabelTable, 'priority', 'priority.label_id = idea.idea_priority_id')
            ->where("idea.idea_id = '{$iIdeaId}'")
            ->execute('getRow');

        if (empty($aResult)) return false;

        $aCategoryList = $this->dividerList($aResult["categories"]);
        foreach ($aCategoryList as $iKey => $sItem){
            $aCategoryList[$iKey]['title'] = Spmh::getPhrase($sItem["title"]);
        }

        $aResult['idea_created_original'] = $aResult['idea_created'];
        $aResult['idea_modified_original'] = $aResult['idea_modified'];
        $aResult['idea_created'] = $this->_oDate->UnixTimeToShamsiDate($aResult['idea_created'], 'Y/m/d H:i');
        $aResult['idea_modified'] = $this->_oDate->UnixTimeToShamsiDate($aResult['idea_modified'], 'Y/m/d H:i');
        $aResult['importance_title'] = Spmh::getPhrase($aResult['importance_title']);
        $aResult['priority_title'] = Spmh::getPhrase($aResult['priority_title']);
        $aResult['categories'] = $aCategoryList;
        $aResult['users'] = $this->dividerList($aResult["users"]);
        $aResult['evaluators'] = $this->dividerList($aResult["evaluators"]);

        return $aResult;
    }

    public function paginate($aParams = array(), $iPage = 1, $iLimit = 20, $bGetCount = false){
        $aParams = $this->database()->escape($aParams);
        $iPage = intval($iPage);
        $iLimit = intval($iLimit);

        $aWhere = array();
        $sSortField = '';
        $sSortDir = '';

        foreach ($aParams as $sKey => $sParam){
            $sParam = trim($sParam);
            if(!empty($sParam)){
                switch ($sKey){
                    case 'filter_description':
                        $aWhere[] = "idea.idea_description LIKE '%{$sParam}%'";
                        break;
                    case 'filter_title':
                        $aWhere[] = "idea.idea_title LIKE '%{$sParam}%'";
                        break;
                    case 'filter_target':
                        $aWhere[] = "idea.idea_target LIKE '%{$sParam}%'";
                        break;
                    case 'filter_status':
                        $aWhere[] = "idea.idea_status = '{$sParam}'";
                        break;
                    case 'filter_confidentiality':
                        $aWhere[] = "idea.idea_confidentiality = '{$sParam}'";
                        break;
                    case 'filter_importance_id':
                        $aWhere[] = "idea.idea_importance_id = '{$sParam}'";
                        break;
                    case 'filter_idea_code':
                        $aWhere[] = "idea.idea_code = '{$sParam}'";
                        break;
                    case 'filter_priority_id':
                        $aWhere[] = "idea.idea_priority_id = '{$sParam}'";
                        break;
                    case 'filter_category_list':
                        $sCategorySubQuery = $this->database()
                            ->select("subQuery_labelAssign.label_id")
                            ->from($this->_sLabelAssignTable, 'subQuery_labelAssign')
                            ->where("
                                subQuery_labelAssign.item_id = idea.idea_id AND 
                                subQuery_labelAssign.type_id = '" . $this->_oIdeaEnum->category::categoryLabelType . "' AND 
                                subQuery_labelAssign.label_id IN ({$sParam})
                            ")
                            ->limit(1)
                            ->execute('');
                        $aWhere[] = "({$sCategorySubQuery}) IS NOT NULL";
                        break;
                    case 'filter_related_user_id':
                        $sUserWhereSubQuery = $this->database()
                            ->select("subQuery_ideaUser.idea_user_user_id")
                            ->from($this->_sIdeaUserTable, 'subQuery_ideaUser')
                            ->where("subQuery_ideaUser.idea_id = idea.idea_id AND subQuery_ideaUser.idea_user_user_id = {$sParam}")
                            ->limit(1)
                            ->execute('');

                        $sEvaluatorWhereSubQuery = $this->database()
                            ->select("subQuery_ideaEvaluator.idea_evaluator_user_id")
                            ->from($this->_sIdeaEvaluatorTable, 'subQuery_ideaEvaluator')
                            ->where("subQuery_ideaEvaluator.idea_id = idea.idea_id AND subQuery_ideaEvaluator.idea_evaluator_user_id = {$sParam}")
                            ->limit(1)
                            ->execute('');

                        $aWhere[] = "(({$sUserWhereSubQuery}) IS NOT NULL OR ({$sEvaluatorWhereSubQuery}) IS NOT NULL)";
                        break;
                    case 'filter_created_user_id':
                        $aWhere[] = "idea.idea_created_user_id = '{$sParam}'";
                        break;
                    case 'filter_modified_user_id':
                        $aWhere[] = "idea.idea_modified_user_id = '{$sParam}'";
                        break;
                    case 'filter_created_start':
                        $iParam = $this->_oDate->getCurrentDateStartHour($this->_oDate->shamsiDateToUnixTime($sParam));
                        $aWhere[] = "idea.idea_created >= {$iParam}";
                        break;
                    case 'filter_created_end':
                        $iParam = $this->_oDate->getCurrentDateEndHour($this->_oDate->shamsiDateToUnixTime($sParam));
                        $aWhere[] = "idea.idea_created <= {$iParam}";
                        break;
                    case 'filter_modified_start':
                        $iParam = $this->_oDate->getCurrentDateStartHour($this->_oDate->shamsiDateToUnixTime($sParam));
                        $aWhere[] = "idea.idea_modified >= {$iParam}";
                        break;
                    case 'filter_modified_end':
                        $iParam = $this->_oDate->getCurrentDateEndHour($this->_oDate->shamsiDateToUnixTime($sParam));
                        $aWhere[] = "idea.idea_modified <= {$iParam}";
                        break;
                    case 'filter_section':
                    {
                        switch ($sParam) {
                            case 'blackboard':
                            {
                                $aWhere[] = "(
                                    idea.idea_status = '" . $this->_oIdeaEnum->idea::statusFinalAccept . "' OR
                                    idea.idea_status = '" . $this->_oIdeaEnum->idea::statusRunningProject . "' OR
                                    idea.idea_status = '" . $this->_oIdeaEnum->idea::statusDoneProject . "' OR
                                    idea.idea_status = '" . $this->_oIdeaEnum->idea::statusSelectedForProposal . "' OR
                                    idea.idea_status = '" . $this->_oIdeaEnum->idea::statusRejectedProposal . "'
                                )";
                                break;
                            }
                        }
                    }
                    case 'sort_field':
                        $sSortField = $sParam;
                        break;
                    case 'sort_dir':
                        $sSortDir = $sParam;
                        break;
                }
            }
        }

        //access
        $sUserAccessSubQuery = $this->database()
            ->select("subQuery_ideaUser.idea_user_user_id")
            ->from($this->_sIdeaUserTable, 'subQuery_ideaUser')
            ->where("subQuery_ideaUser.idea_id = idea.idea_id AND subQuery_ideaUser.idea_user_user_id = " . Spmh::getUserId())
            ->limit(1)
            ->execute('');

        $sEvaluatorAccessSubQuery = $this->database()
            ->select("subQuery_ideaEvaluator.idea_evaluator_user_id")
            ->from($this->_sIdeaEvaluatorTable, 'subQuery_ideaEvaluator')
            ->where("subQuery_ideaEvaluator.idea_id = idea.idea_id AND subQuery_ideaEvaluator.idea_evaluator_user_id = " . Spmh::getUserId())
            ->limit(1)
            ->execute('');

        $aWhere[] = "(
            idea.idea_status = '" . $this->_oIdeaEnum->idea::statusFinalAccept . "' OR
            idea.idea_status = '" . $this->_oIdeaEnum->idea::statusRunningProject . "' OR
            idea.idea_status = '" . $this->_oIdeaEnum->idea::statusDoneProject . "' OR
            idea.idea_status = '" . $this->_oIdeaEnum->idea::statusSelectedForProposal . "' OR
            idea.idea_status = '" . $this->_oIdeaEnum->idea::statusRejectedProposal . "' OR
            idea.idea_created_user_id = " . Spmh::getUserId() . " OR
            ({$sUserAccessSubQuery}) IS NOT NULL OR
            ({$sEvaluatorAccessSubQuery}) IS NOT NULL
        )";
        //end of access

        $sWhere = implode(' AND ', $aWhere);

        if($bGetCount){
            return $this->database()
                ->select("COUNT(idea.idea_id)")
                ->from($this->_sIdeaTable, 'idea')
                ->where($sWhere)
                ->execute('getField');
        }else{
            $sPostSubQuery = $this->database()
                ->select("subQuery_opPostParent.post_name")
                ->from($this->_sOpPostAssignTable, 'subQuery_opPostAssign')
                ->join($this->_sOpPostTable, 'subQuery_opPost', "subQuery_opPost.post_id = subQuery_opPostAssign.post_id AND NOW() <= subQuery_opPost.exp_date")
                ->join($this->_sOpPostTable, 'subQuery_opPostParent', "subQuery_opPostParent.post_id = subQuery_opPost.parent_id AND NOW() <= subQuery_opPostParent.exp_date")
                ->where("subQuery_opPostAssign.assign_to = idea.idea_created_user_id AND NOW() <= subQuery_opPostAssign.exp_date")
                ->limit(1)
                ->execute('');

            $sCategorySubQuery = $this->database()
                ->select("CONCAT(GROUP_CONCAT(subQuery_labelAssign.label_id), '|', GROUP_CONCAT(subQuery_category.label_title))")
                ->from($this->_sLabelAssignTable, 'subQuery_labelAssign')
                ->join($this->_sLabelTable, 'subQuery_category', "subQuery_category.label_id = subQuery_labelAssign.label_id")
                ->where("subQuery_labelAssign.item_id = idea.idea_id AND subQuery_labelAssign.type_id = '" . $this->_oIdeaEnum->category::categoryLabelType . "'")
                ->execute('');

            $sUserSubQuery = $this->database()
                ->select("CONCAT(GROUP_CONCAT(subQuery_ideaUser.idea_user_user_id), '|', GROUP_CONCAT(subQuery_user.full_name))")
                ->from($this->_sIdeaUserTable, 'subQuery_ideaUser')
                ->join($this->_sUserTable, 'subQuery_user', 'subQuery_user.user_id = subQuery_ideaUser.idea_user_user_id')
                ->where("subQuery_ideaUser.idea_id = idea.idea_id")
                ->execute('');

            $sEvaluatorSubQuery = $this->database()
                ->select("CONCAT(GROUP_CONCAT(subQuery_ideaEvaluator.idea_evaluator_user_id), '|', GROUP_CONCAT(subQuery_user.full_name))")
                ->from($this->_sIdeaEvaluatorTable, 'subQuery_ideaEvaluator')
                ->join($this->_sUserTable, 'subQuery_user', 'subQuery_user.user_id = subQuery_ideaEvaluator.idea_evaluator_user_id')
                ->where("subQuery_ideaEvaluator.idea_id = idea.idea_id")
                ->execute('');

            $aResult = $this->database()
                ->select("
                    idea.*,
                    importance.label_title AS importance_title,
                    priority.label_title AS priority_title,
                    ({$sUserSubQuery}) AS users,
                    ({$sCategorySubQuery}) AS categories,
                    ({$sEvaluatorSubQuery}) AS evaluators,
                    ({$sPostSubQuery}) AS organization_unit
                ")
                ->from($this->_sIdeaTable, 'idea')
                ->leftJoin($this->_sLabelTable, 'importance', 'importance.label_id = idea.idea_importance_id')
                ->leftJoin($this->_sLabelTable, 'priority', 'priority.label_id = idea.idea_priority_id')
                ->where($sWhere)
                ->limit(($iPage * $iLimit) - $iLimit, $iLimit)
                ->order("{$sSortField} {$sSortDir}")
                ->execute('getRows');

            $iRowNumber = ($iPage - 1) * $iLimit;
            foreach ($aResult as $iKey => $aItem){
                $iRowNumber++;
                $aResult[$iKey]['row'] = $iRowNumber;
                $aResult[$iKey]['idea_created_original'] = $aItem['idea_created'];
                $aResult[$iKey]['idea_modified_original'] = $aItem['idea_modified'];
                $aResult[$iKey]['idea_created'] = $this->_oDate->UnixTimeToShamsiDate($aItem['idea_created'], 'Y/m/d H:i');
                $aResult[$iKey]['idea_modified'] = $this->_oDate->UnixTimeToShamsiDate($aItem['idea_modified'], 'Y/m/d H:i');
                $aResult[$iKey]['importance_title'] = Spmh::getPhrase($aItem['importance_title']);
                $aResult[$iKey]['priority_title'] = Spmh::getPhrase($aItem['priority_title']);

                $aCategoryList = $this->dividerList($aItem["categories"]);
                foreach ($aCategoryList as $iCategoryKey => $sValue){
                    $aCategoryList[$iCategoryKey]['title'] = Spmh::getPhrase($sValue["title"]);
                }

                $aResult[$iKey]['users'] = $this->dividerList($aItem["users"]);
                $aResult[$iKey]['categories'] = $aCategoryList;
                $aResult[$iKey]['evaluators'] = $this->dividerList($aItem["evaluators"]);
            }

            return $aResult;
        }
    }

    private function dividerList($aItemList){

        if (empty($aItemList)) return [];

        $aItemInfo = [];
        $aDivider = explode('|', $aItemList);

        $aIdList = explode(',', $aDivider[0]);
        $aTitleList = explode(',', $aDivider[1]);

        foreach ($aIdList as $iKey => $sItem){
            $aItemInfo[] = [
                'id' => $sItem,
                'title' => $aTitleList[$iKey]
            ];
        }
        return $aItemInfo;
    }

    public function changeStatus($iIdeaId, $aParams){
        return $this->_oMethodChangeStatus
            ->init($this, $this->_oIdeaLog, $this->_oIdeaEvaluator, $this->_oIdeaComment, $this->_oIdeaAccess)
            ->process($iIdeaId, $aParams);
    }
}
?>