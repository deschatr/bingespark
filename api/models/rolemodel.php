<?php

require_once API_ROOT_PATH . "/models/basemodel.php";
require_once API_ROOT_PATH . "/models/creditmodel.php";

class RoleModel extends BaseModel
{
    public $equalFilters = ['role.id','movie_id'];
    public $sorts = ['role'];

    public function count()
    {
        $query = 'SELECT COUNT(role.id) AS number FROM role';
        if ($this->filterExists('movie_id')) $query .= ' JOIN credit on credit.role_id=role.id';
        $result =  $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = 'SELECT role.id, role.role FROM role';
        if ($this->filterExists('movie_id')) $query .= ' JOIN credit on credit.role_id=role.id';
        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        if (!array_key_exists('role',$data)) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO role (role) VALUES (?)","s",$data['role']);
    }

    public function update($data)
    {
        if (!$this->filterExists('role.id')) throw new Exception("Invalid data",400);
        if (!array_key_exists('role',$data)) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE role SET role=?","s",$data['role']);
    }

    public function delete()
    {
        if (!$this->filterExists('role.id')) throw new Exception("Invalid data",400);
        $model = new CreditModel;
        $model->addEqualFilter('role_id',$this->getEqualFilter('role.id'));
        $model->delete();
        return $this->mysqlDelete("DELETE FROM role");;
    }
}

?>