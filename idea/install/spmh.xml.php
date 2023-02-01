<module>
    <data>
        <module_id>idea</module_id>
        <product_id>spmh</product_id>
        <is_core>0</is_core>
        <is_active>1</is_active>
        <is_menu>1</is_menu>
        <menu />
        <phrase_var_name>module_idea</phrase_var_name>
        <writable />
    </data>
    <phrases>
    <phrase module_id="idea" var_name="module_idea">Idea</phrase>
    <phrase module_id="idea" var_name="idea_description">Idea Description</phrase>
    <phrase module_id="idea" var_name="idea_category">Idea Category</phrase>
    <phrase module_id="idea" var_name="idea_status_description">Idea Status Description</phrase>
    <phrase module_id="idea" var_name="idea_code">Idea Status Code</phrase>
    <phrase module_id="idea" var_name="idea_title">Idea Title</phrase>
    <phrase module_id="idea" var_name="idea_target">Idea Target</phrase>
    <phrase module_id="idea" var_name="idea_category_type_id">Type Id</phrase>
    <phrase module_id="idea" var_name="idea_status">Idea Status</phrase>
    <phrase module_id="idea" var_name="idea_confidentiality">Idea Confidentiality</phrase>
    <phrase module_id="idea" var_name="idea_category">Idea Category</phrase>
    <phrase module_id="idea" var_name="idea_importance_id">Idea Importance Id</phrase>
    <phrase module_id="idea" var_name="idea_priority_id">Idea Priority Id</phrase>
    <phrase module_id="idea" var_name="idea_created">Idea Created</phrase>
    <phrase module_id="idea" var_name="idea_user_created">Idea User Created</phrase>
    <phrase module_id="idea" var_name="idea_created_user_id">Idea Created User Id</phrase>
    <phrase module_id="idea" var_name="idea_user_created_user_id">Idea User Created User Id</phrase>
    <phrase module_id="idea" var_name="idea_modified">Idea Modified</phrase>
    <phrase module_id="idea" var_name="idea_user_modified">Idea User Modified</phrase>
    <phrase module_id="idea" var_name="idea_modified_user_id">Idea Modified User Id</phrase>
    <phrase module_id="idea" var_name="idea_user_modified_user_id">Idea User Modified User Id</phrase>
    <phrase module_id="idea" var_name="idea_does_not_exists">Idea Does Not Exists</phrase>
    <phrase module_id="idea" var_name="idea_you_do_not_have_access_private_idea">You Do Not Have Access Private Idea</phrase>
    <phrase module_id="idea" var_name="idea_you_do_not_have_access_public_idea">You Do Not Have Access Public Idea</phrase>
    <phrase module_id="idea" var_name="idea_transaction_was_not_successful">The Transaction Was Not Successful</phrase>
    <phrase module_id="idea" var_name="idea_id">Idea Id</phrase>
    <phrase module_id="idea" var_name="idea_user_post_id">Idea Post Id</phrase>
    <phrase module_id="idea" var_name="idea_user_user_id">Idea User Id</phrase>
    <phrase module_id="idea" var_name="idea_error_in_multi_add_user">Idea Error In Multi Add User</phrase>
    <phrase module_id="idea" var_name="idea_status_pending">Pending</phrase>
    <phrase module_id="idea" var_name="idea_confidentiality_normal">Normal</phrase>
    <phrase module_id="idea" var_name="idea_confidentiality_Confidential">Confidential</phrase>
    <phrase module_id="idea" var_name="idea_status_reject">Reject</phrase>
    <phrase module_id="idea" var_name="idea_status_Need_to_complete">Need To Complete</phrase>
    <phrase module_id="idea" var_name="idea_status_accept">Accept</phrase>
    <phrase module_id="idea" var_name="is_librarian">is Librarian</phrase>
    <phrase module_id="idea" var_name="idea_status_final_accept">Final Accept</phrase>
    <phrase module_id="idea" var_name="idea_status_final_reject">Final Reject</phrase>
    <phrase module_id="idea" var_name="idea_status_running_project">Running Project</phrase>
    <phrase module_id="idea" var_name="idea_status_done_project">Done Project</phrase>
    <phrase module_id="idea" var_name="idea_status_rejected_proposal">Rejected Proposal</phrase>
    <phrase module_id="idea" var_name="idea_status_selected_for_proposal">Selected For Proposal</phrase>
    <phrase module_id="idea" var_name="idea_Users_of_this_idea_have_not_been_deleted">Users Of This Idea Have Not Been Deleted</phrase>
    <phrase module_id="idea" var_name="item_phrase">Item Phrase</phrase>
    <phrase module_id="idea" var_name="idea_evaluator_list_id">Idea Evaluator List Id</phrase>
    <phrase module_id="idea" var_name="idea_evaluator_user_id">Idea Evaluator User Id</phrase>
    <phrase module_id="idea" var_name="idea_evaluator_post_id">Idea Evaluator Post Id</phrase>
    <phrase module_id="idea" var_name="idea_evaluator_modified">Idea Evaluator Modified</phrase>
    <phrase module_id="idea" var_name="idea_evaluator_modified_user_id">Idea Evaluator Modified User Id</phrase>
    <phrase module_id="idea" var_name="idea_evaluator_created">Idea Evaluator Created</phrase>
    <phrase module_id="idea" var_name="idea_evaluator_created_user_id">Idea Evaluator Created User Id</phrase>
    </phrases>

    <user_group_settings>
        <setting is_admin_setting="0" module_id="idea" type="boolean" admin="1" user="0" guest="0" staff="0" module="idea" ordering="0">can_access_all_idea</setting>
        <setting is_admin_setting="0" module_id="idea" type="boolean" admin="0" user="0" guest="0" staff="0" module="idea" ordering="0">is_librarian</setting>
    </user_group_settings>

    <install><![CDATA[

         $this->database()->query("CREATE TABLE IF NOT EXISTS " . Spmh::getT('idea') . "(
           `idea_id`                       INT(11)             UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
           `idea_description`              TEXT                NULL,
           `idea_title`                    VARCHAR(255)        NOT NULL,
           `idea_target`                   VARCHAR(255)        NULL,
           `idea_status`                   VARCHAR(50)         NOT NULL,
           `idea_confidentiality`          VARCHAR(20)         NULL,
           `idea_importance_id`            INT(11)             UNSIGNED NULL,
           `idea_code`                     VARCHAR(255)        NULL,
           `idea_duplicate_id`             INT(11)             UNSIGNED NULL,
           `idea_priority_id`              INT(11)             UNSIGNED NULL,
           `idea_total_like`               INT(11)             UNSIGNED NULL,
           `idea_total_dislike`            INT(11)             UNSIGNED NULL,
           `idea_created`                  INT(10)             NOT NULL,
           `idea_created_user_id`          INT(11)             UNSIGNED NOT NULL,
           `idea_modified`                 INT(10)             NOT NULL,
           `idea_modified_user_id`         INT(11)             UNSIGNED NOT NULL
        )  ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci");


        // CREATE TABLE FOR IDEA USERS
        $this->database()->query("CREATE TABLE IF NOT EXISTS " . Spmh::getT('idea_user')."(
            `idea_user_id`                 INT(11)      UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `idea_id`                      INT(11)      UNSIGNED    NOT NULL,
            `idea_user_user_id`            INT(11)      UNSIGNED    NOT NULL,
            `idea_user_post_id`            INT(11)      UNSIGNED    NOT NULL,
            `idea_user_created`            INT(10)      NOT NULL,
            `idea_user_created_user_id`    INT(11)      UNSIGNED    NOT NULL,
            `idea_user_modified`           INT(10)      NOT NULL,
            `idea_user_modified_user_id`   INT(11)      UNSIGNED    NOT NULL
        )  ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci");


        // CREATE TABLE FOR IDEA EVALUATOR
        $this->database()->query("CREATE TABLE IF NOT EXISTS " . Spmh::getT('idea_evaluator')."(
            `idea_evaluator_id`                 INT(11)      UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `idea_id`                           INT(11)      UNSIGNED    NOT NULL,
            `idea_evaluator_list_id`            INT(11)      UNSIGNED    NOT NULL,
            `idea_evaluator_user_id`            INT(11)      UNSIGNED    NOT NULL,
            `idea_evaluator_post_id`            INT(11)      UNSIGNED    NOT NULL,
            `idea_evaluator_created`            INT(10)      NOT NULL,
            `idea_evaluator_created_user_id`    INT(11)      UNSIGNED    NOT NULL,
            `idea_evaluator_modified`           INT(10)      NOT NULL,
            `idea_evaluator_modified_user_id`   INT(11)      UNSIGNED    NOT NULL
        )  ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci");


        // CREATE TABLE FOR IDEA LOG
        $this->database()->query("CREATE TABLE IF NOT EXISTS " . Spmh::getT('idea_log') . "(
            `idea_log_id`                   INT(11)         UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `idea_log_item_id`              INT(11)         UNSIGNED NOT NULL,
            `idea_log_item_type`            VARCHAR(255)    NULL,
            `idea_log_data`                 TEXT            NULL,
            `idea_log_created`              INT(10)         NOT NULL,
            `idea_log_created_user_id`      INT(11)         UNSIGNED NOT NULL
        )  ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci");


        // CREATE TABLE FOR IDEA PROPOSAL
        $this->database()->query("CREATE TABLE IF NOT EXISTS " . Spmh::getT('idea_proposal') . "(
            `idea_proposal_id`                          INT(11)         UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `idea_id`                                   INT(11)         UNSIGNED    NOT NULL,
            `idea_proposal_user_id`                     INT(11)         UNSIGNED    NOT NULL,
            `idea_proposal_title`                       VARCHAR(255)    NOT NULL,
            `idea_proposal_target`                      VARCHAR(255)    NOT NULL,
            `idea_proposal_description`                 TEXT            NULL,
            `idea_proposal_status`                      VARCHAR(50)     NOT NULL,
            `idea_proposal_cost`                        INT(20)         UNSIGNED    NOT NULL,
            `idea_proposal_created`                     INT(10)         NOT NULL,
            `idea_proposal_created_user_id`             INT(11)         UNSIGNED    NOT NULL,
            `idea_proposal_modified`                    INT(10)         NOT NULL,
            `idea_proposal_modified_user_id`            INT(11)         UNSIGNED    NOT NULL
        )  ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci");


        //Add label for Idea Category
        if(!Spmh::getService('labels.criteria')->checkNiceNameExist('idea.idea_category')){
            $iTypeId = Spmh::getService('labels.type')->add(array(
                'type_nicename' => 'idea.idea_category',
                'type_phrase' => array(
                    'Fa' => array('text' => 'دسته بندی ایده'),
                    'en' => array('text' => 'Idea Category')
                )
            ));
            $iForWhatId = Spmh::getService('labels.for-what')->add(array(
                'for_what_nicename' => 'idea.idea_category',
                'for_what_phrase' => array(
                    'Fa' => array('text' => 'دسته بندی ایده'),
                    'en' => array('text' => 'Idea Category')
                )
            ));
            Spmh::getService('labels.criteria')->add(array(
                'criteria_nicename' => 'idea.idea_category',
                'type_id' => $iTypeId,
                'for_what_id' => $iForWhatId,
                'criteria_title' => 'idea.idea_category',
                'criteria_description' => 'Idea Category'
            ));
        }
    ]]></install>
</module>
