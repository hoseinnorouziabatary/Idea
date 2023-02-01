<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_Method_Change_Status extends Spmh_Service
{

    private $_sIdeaTable;
    private $_sLabelTable;

    private $_oIdea;
    private $_oIdeaEvaluator;
    private $_oIdeaComment;
    private $_oIdeaAccess;
    private $_oIdeaEnum;

    private $_oLog;
    private $_oValidator;

    public function __construct()
    {
        $this->_sIdeaTable = Spmh::getT('idea');
        $this->_sLabelTable = Spmh::getT('labels');

        $this->_oValidator = Spmh::getLib('validation');

        $this->_oIdeaEnum = Spmh::getService('idea.enum');
    }

    public function init($oIdea, $oLog, $oEvaluator, $oComment, $oAccess){
        $this->_oIdea = $oIdea;
        $this->_oLog = $oLog;
        $this->_oIdeaEvaluator = $oEvaluator;
        $this->_oIdeaComment = $oComment;
        $this->_oIdeaAccess = $oAccess;
        return $this;
    }

    public function process($iIdeaId, $aParams){
        $iIdeaId = intval($iIdeaId);
        $aIdea = $this->_oIdea->getById($iIdeaId);
        $ValidationRules = array(
            'description' => 'required',
            'status' => 'required|max:50|in:' . implode(',', array_keys($this->_oIdeaEnum->idea->getStatusList()))
        );
        $ValidationAliases = array(
            'description' => Spmh::getPhrase("idea.idea_status_description"),
            'status' => Spmh::getPhrase("idea.idea_status"),
        );
        $aValidationResult = $this->_oValidator->validate($aParams, $ValidationRules, $ValidationAliases);

        if(!empty($aIdea) && $aValidationResult['status'] && $this->_oIdeaAccess->changeStatus($iIdeaId)){

            $aStatusMethods = [
                $this->_oIdeaEnum->idea::statusAccept => 'accept',
                $this->_oIdeaEnum->idea::statusNeedToComplete => 'needToComplete',
                $this->_oIdeaEnum->idea::statusReject => 'reject',
                $this->_oIdeaEnum->idea::statusFinalAccept => 'finalAccept',
                $this->_oIdeaEnum->idea::statusFinalReject => 'finalReject',
            ];

            $this->database()->beginTransaction();

            $aResultStatus = call_user_func([$this, $aStatusMethods[$aValidationResult["data"]["status"]]], $aIdea, $aParams);
            if (is_array($aResultStatus)){
                $iResult = $this->database()->update(
                    $this->_sIdeaTable,
                    array_merge([
                        'idea_status' => $aValidationResult["data"]["status"],
                        'idea_modified' => SPMH_TIME,
                        'idea_modified_user_id' => Spmh::getUserId(),
                    ], $aResultStatus),
                    "idea_id = {$iIdeaId}"
                );

                if ($iResult && $this->_oIdeaComment->add($this->_oIdeaEnum->comment::typePrivate, $iIdeaId, $aValidationResult["data"]["description"], 0, false)){
                    $this->_oLog->add(array(
                        'idea_log_item_id' => $iIdeaId,
                        'idea_log_item_type' => 'change_status_idea_success',
                        'idea_log_data' => array(
                            'idea_status' => $aValidationResult["data"]["status"],
                            'old_idea_status' => $aIdea["idea_status"],
                            'description' => $aValidationResult["data"]["description"],
                            'params' => $aParams
                        )
                    ));
                    if(Spmh_Error::isPassed()){
                        $this->database()->commit();
                        return $iResult;
                    }
                }
            }
            $this->database()->rollback();
        }
        return false;
    }

    private function accept($aIdea, $aParams){
        $aValidOriginStatus = [$this->_oIdeaEnum->idea::statusPending];

        $aValidationRules = array(
            'idea_code' => "required|alpha_num|max:15|not_exists:{$this->_sIdeaTable},idea_code",
            'idea_confidentiality' => 'required|max:20|in:' . implode(',', array_keys($this->_oIdeaEnum->idea->getConfidentialityList())),
            'idea_importance_id' => "required|exists:{$this->_sLabelTable},label_id",
            'idea_priority_id' => "required|exists:{$this->_sLabelTable},label_id",
        );
        $aValidationAliases = array(
            'idea_code' => Spmh::getPhrase("idea.idea_code"),
            'idea_confidentiality' => Spmh::getPhrase("idea.idea_confidentiality"),
            'idea_importance_id' => Spmh::getPhrase("idea.idea_importance_id"),
            'idea_priority_id' => Spmh::getPhrase("idea.idea_priority_id"),
        );

        if(in_array($aIdea['idea_status'], $aValidOriginStatus)){
            $aValidationResult = $this->_oValidator->validate($aParams, $aValidationRules, $aValidationAliases);

            if ($aValidationResult['status']) {
                if(!$this->_oIdeaEvaluator->addMultiEvaluator($aIdea['idea_id'], $aParams['evaluators']))
                    Spmh_Error::set(Spmh::getPhrase('core.there_is_an_error_in_operation'));

                return [
                    'idea_code' => $aValidationResult['data']['idea_code'],
                    'idea_confidentiality' => $aValidationResult['data']['idea_confidentiality'],
                    'idea_importance_id' => $aValidationResult['data']['idea_importance_id'],
                    'idea_priority_id' => $aValidationResult['data']['idea_priority_id'],
                ];
            }
        }
        return false;
    }

    private function needToComplete($aIdea){
        $aValidOriginStatus = [$this->_oIdeaEnum->idea::statusPending];

        if(in_array($aIdea['idea_status'], $aValidOriginStatus)){
            return [];
        }
        return false;
    }

    private function reject($aIdea, $aParams){
        $aValidOriginStatus = [$this->_oIdeaEnum->idea::statusPending];

        if(in_array($aIdea['idea_status'], $aValidOriginStatus)){

            $iIdeaDuplicateId = intval($aParams["idea_duplicate_id"]);
            $aIdeaDuplicate = $this->_oIdea->getById($iIdeaDuplicateId);

            if (
                !empty($aIdeaDuplicate) &&
                $aIdeaDuplicate["idea_status"] != $this->_oIdeaEnum->idea::statusPending &&
                $this->_oIdeaAccess->show($aIdeaDuplicate['idea_id'])
            ){
                return [
                    'idea_duplicate_id' => $aIdeaDuplicate['idea_id']
                ];
            }
        }
        return false;
    }

    private function finalAccept($aIdea){
        $aValidOriginStatus = [$this->_oIdeaEnum->idea::statusAccept];

        if(in_array($aIdea['idea_status'], $aValidOriginStatus)){
            return [];
        }
        return false;
    }

    private function finalReject($aIdea){
        $aValidOriginStatus = [$this->_oIdeaEnum->idea::statusAccept];

        if(in_array($aIdea['idea_status'], $aValidOriginStatus)){
            return [];
        }
        return false;
    }
}
?>