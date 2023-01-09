<?php

require_once API_ROOT_PATH . "/models/basemodel.php";

class APIScopeModel extends BaseModel
{
    public $equalFilters = ['api_key_scope.id','scope_id','scope','key_id'];

    public function count()
    {
        $query = "SELECT COUNT(api_scope.id) AS number FROM api_scope";
        if ($this->filtersExistAny('key_id','scope_id')) $query .= " JOIN api_key_scope ON api_key_scope.scope_id=api_scope.id";
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = "SELECT api_scope.id, scope FROM api_scope";
        if ($this->filtersExistAny('key_id','scope_id')) $query .= " JOIN api_key_scope ON api_key_scope.scope_id=api_scope.id";
        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        if (!array_key_exists('scope',$data)) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO api_scope (scope) VALUES (?)",
                                    "s",$data['scope']);
    }

    public function update($data)
    {
        if (!$this->filterExists('api_scope.id')) throw new Exception("Invalid data",400);
        if (!array_key_exists('scope',$data)) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE api_scope SET scope=?",
                                    "s",$data['scope']);
    }

    public function delete()
    {
        if (!$this->filterExists('api_scope.id')) throw new Exception("Invalid data",400);
        return  $this->mysqlDelete("DELETE FROM api_scope");
    }
}