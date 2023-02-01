<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_Category extends Spmh_Service
{
    private $_oLabelAssignItem;
    private $_oLabels;
    private $_oIdeaEnum;

    public function __construct()
    {
        $this->_oLabelAssignItem = Spmh::getService('labels.label-assign');
        $this->_oLabels = Spmh::getService('labels');
        $this->_oIdeaEnum = Spmh::getService('idea.enum');
    }

    private function checkModule(){
        return Spmh::isModule('labels');
    }

    public function getAll(){
        return $this->_oLabels->getLabelsByCriteria($this->_oIdeaEnum->category::categoryLabelCriteria, true);
    }

    public function add($iItemId, $mLabels)
    {
        if (!$this->checkModule() || empty($mLabels)) return false;

        return $this->_oLabelAssignItem->add($this->_oIdeaEnum->category::categoryLabelType, $iItemId, $mLabels, Spmh::getUserId(), 'idea');
    }

    public function edit($iItemId, $mLabels)
    {
        if (!$this->checkModule() || empty($mLabels)) return false;

        if ($this->delete($iItemId)){
            return $this->add($iItemId, $mLabels);
        }
        return false;
    }

    public function delete($iItemId)
    {
        if (!$this->checkModule()) return false;

        $this->_oLabelAssignItem->deleteByTypeIdAndItemId($this->_oIdeaEnum->category::categoryLabelType, $iItemId);
        return $this->database()->affectedRows();
    }
}
