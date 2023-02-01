<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_Enum_Idea extends Spmh_Service
{
    const statusPending = 'pending';
    const statusReject = 'reject';
    const statusNeedToComplete = 'need_to_complete';
    const statusAccept = 'accept';
    const statusFinalAccept = 'final_accept';
    const statusFinalReject = 'final_reject';
    const statusSelectedForProposal = 'selected_for_proposal';
    const statusRejectedProposal = 'rejected_proposal';
    const statusRunningProject = 'running_project';
    const statusDoneProject = 'done_project';

    const confidentialityNormal = 'normal';
    const confidentialityConfidential = 'confidential';

    public function getStatusList(): array
    {
        return [
            self::statusPending => Spmh::getPhrase('idea.idea_status_pending'),
            self::statusReject => Spmh::getPhrase('idea.idea_status_reject'),
            self::statusNeedToComplete => Spmh::getPhrase('idea.idea_status_Need_to_complete'),
            self::statusAccept => Spmh::getPhrase('idea.idea_status_accept'),
            self::statusFinalAccept => Spmh::getPhrase('idea.idea_status_final_accept'),
            self::statusFinalReject => Spmh::getPhrase('idea.idea_status_final_reject'),
            self::statusRunningProject => Spmh::getPhrase('idea.idea_status_running_project'),
            self::statusDoneProject => Spmh::getPhrase('idea.idea_status_done_project'),
            self::statusSelectedForProposal => Spmh::getPhrase('idea.idea_status_selected_for_proposal'),
            self::statusRejectedProposal => Spmh::getPhrase('idea.idea_status_rejected_proposal')
        ];
    }

    public function getConfidentialityList(): array
    {
        return [
            self::confidentialityNormal => Spmh::getPhrase('idea.idea_confidentiality_normal'),
            self::confidentialityConfidential => Spmh::getPhrase('idea.idea_confidentiality_Confidential')
        ];
    }
}