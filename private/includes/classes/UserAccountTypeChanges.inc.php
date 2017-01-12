<?php
class UserAccountTypeChanges extends Tobjects
{
    function __construct()
    {
        $this->search_keys = NULL;
        $this->table_name = 'user_account_type_changes';
    }

    protected function load($where='', $values=NULL, $return_array=false, $order_by='', $limit='')
    {
        //
    }
}

class UserAccountTypes
{
    const LIMITED_UNPAID = 'LIMITED_UNPAID';
    const FULL_PAID = 'FULL_PAID';
    const LIFETIME_MEMBER = 'LIFETIME_MEMBER';

    public static function load($account_type) {

        switch ($account_type) {
            case UserAccountTypes::LIMITED_UNPAID:
            case UserAccountTypes::FULL_PAID:
            case UserAccountTypes::LIFETIME_MEMBER:
                return $account_type;
            default:
                return UserAccountTypes::LIMITED_UNPAID;
        }
    }
}

class UserAccountTypeChange extends Tobject
{
    protected $id;
    protected $user;
    protected $account_type;
    protected $notes;

    function __construct($properties=NULL)
    {
        $table_name = 'user_account_type_changes';
        parent::__construct($table_name,$properties);
        if( isset($properties['user']))
            $this->user = new User($properties['user']);
    }

    public function get_id()
    {
        return intval($this->id);
    }

    public function get_users_id()
    {
        return intval($this->user->get_id());
    }

    public function add()
    {
        $sql =
            'INSERT INTO user_account_type_changes
            (
                users_id,
                account_type,
                notes
            )
            VALUES
            (
                :users_id,
                :account_type,
                :notes
            )
            ';

        $values = array(
            ':users_id'=>$this->user->get_id(),
            ':account_type'=>$this->account_type,
            ':notes'=>$this->notes
        );
        if(pdologged_preparedQuery($sql, $values))
        {
            $this->id=Tabmin::$db->lastInsertId();
            return true;
        }

        return false;
    }

    function update()
    {
        return false;
    }

    function delete()
    {
        return false;
    }

}
