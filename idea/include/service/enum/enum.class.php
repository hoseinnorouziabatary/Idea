<?php
/**
 * [SPMH_HEADER]
 */

defined('SPMH') or exit('NO DICE!');

class Idea_Service_Enum_Enum extends Spmh_Service
{
    public $idea;
    public $category;
    public $comment;
    public $like;

    public function __construct(){
        $this->idea = Spmh::getService('idea.enum.idea');
        $this->category = Spmh::getService('idea.enum.category');
        $this->comment = Spmh::getService('idea.enum.comment');
        $this->like = Spmh::getService('idea.enum.like');
    }
}