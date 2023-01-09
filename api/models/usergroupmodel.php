<?php

require_once API_ROOT_PATH . "/models/basemodel.php";

class UserGroupModel extends BaseModel
{
    public $equalFilters = ['usergroup.id'];
    public $sorts = ['name'];

    public function count()
    {
        $query = 'SELECT COUNT(usergroup.id) AS number FROM usergroup';
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = 'SELECT usergroup.id, name FROM usergroup';
        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        if (!array_keys_exist($data,'name')) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO usergroup (name=?)",
                                    "s",$data['name']);
    }
    
    public function update($data)
    {
        if (!$this->filterExists('usergroup.id')) throw new Exception("Invalid data",400);
        if (!array_keys_exist($data,'name')) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE usergroup SET name=?",
                                    "s",$data['name']);
    }

    public function delete()
    {
        if (!$this->filterExists('usergroup.id')) throw new Exception("Invalid data",400);
        return $this->mysqlDelete("DELETE FROM usergroup");
    }
}