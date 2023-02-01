<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_Log extends Spmh_Service{

    private $_sLogTable;

    public function __construct(){
        $this->_sLogTable = Spmh::getT('idea_log');
    }

    public function add($aValues){
        $iCurrentTime = SPMH_TIME;
        $iCurrentUserId = Spmh::getUserId() ? Spmh::getUserId() : 0;

        $aValues['idea_log_created'] = $iCurrentTime;
        $aValues['idea_log_created_user_id'] = $iCurrentUserId;
        if(isset($aValues['idea_log_data']) && !empty($aValues['idea_log_data'])){
            $aValues['idea_log_data'] = json_encode($aValues['idea_log_data']);
        }else{
            $aValues['idea_log_data'] = null;
        }

        return $this->database()->insert($this->_sLogTable, $aValues);
    }

    public function groupAdd($aValues){
        $iCurrentTime = SPMH_TIME;
        $iCurrentUserId = Spmh::getUserId() ? Spmh::getUserId() : 0;

        $aLogFields = array(
            'idea_log_item_id',
            'idea_log_item_type',
            'idea_log_data',
            'idea_log_created',
            'idea_log_created_user_id',
        );
        foreach ($aValues as $iKey => $aValue){
            $aValues[$iKey]['idea_log_created'] = $iCurrentTime;
            $aValues[$iKey]['idea_log_created_user_id'] = $iCurrentUserId;

            if(isset($aValue['idea_log_data']) && !empty($aValue['idea_log_data'])){
                $aValues[$iKey]['idea_log_data'] = json_encode($aValue['idea_log_data']);
            }else{
                $aValues[$iKey]['idea_log_data'] = null;
            }
        }

        return $this->database()->multiInsert($this->_sLogTable, $aLogFields, $aValues);
    }

    public function get($aValues){
        $aValues = $this->database()->escape($aValues);
        $aWhere = array();
        foreach ($aValues as $sKey => $mValue){
            if(is_array($mValue)){
                $aWhere[] = "{$sKey} {$mValue['operator']} '{$mValue['value']}'";
            }else{
                $aWhere[] = "{$sKey} = '{$mValue}'";
            }
        }
        if(!empty($aValues)){
            return $this->database()
                ->select('*')
                ->from($this->_sLogTable, 'log')
                ->where(implode(' AND ', $aWhere))
                ->execute('getRow');
        }

        return false;
    }

    public function getAll($aValues){
        $aValues = $this->database()->escape($aValues);
        $aWhere = array();
        foreach ($aValues as $sKey => $mValue){
            if(is_array($mValue)){
                $aWhere[] = "{$sKey} {$mValue['operator']} '{$mValue['value']}'";
            }else{
                $aWhere[] = "{$sKey} = '{$mValue}'";
            }
        }
        if(!empty($aValues)){
            return $this->database()
                ->select('*')
                ->from($this->_sLogTable, 'log')
                ->where(implode(' AND ', $aWhere))
                ->execute('getRows');
        }

        return false;
    }
}
?>