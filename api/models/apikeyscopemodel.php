<?php

require_once API_ROOT_PATH . "/models/basemodel.php";

class APIKeyScopeModel extends BaseModel
{
    public $equalFilters = ['api_key_scope.id','scope_id','scope','key_id', 'user_id'];
    public $sorts = ['scope','user_id'];

    public function count()
    {
        $query = "SELECT COUNT(api_key_scope.id) AS number FROM api_key_scope JOIN api_scope ON api_scope.id=api_key_scope.scope_id JOIN api_key ON api_key.id=api_key_scope.key_id LEFT JOIN user ON user.id = api_key.user_id";
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = "SELECT api_key_scope.id, key_id, user_id, scope_id, scope FROM api_key_scope JOIN api_scope ON api_scope.id=api_key_scope.scope_id JOIN api_key ON api_key.id=api_key_scope.key_id LEFT JOIN user ON user.id = api_key.user_id";
        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        if (!array_keys_exist($data,'key_id','scope_id')) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO api_key_scope (key_id,scope_id) VALUES (?,?)",
                                    "ii",$data['key_id'],$data['scope_id']);
    }

    public function update($data)
    {
        if (!$this->filterExists('api_key_scope.id')) throw new Exception("Invalid data",400);
        if (!array_keys_exist($data,'key_id','scope_id')) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE api_key_scope SET key_id=?, scope_id=?",
                                    "ii",$data['key_id'],$data['scope_id']);
    }

    public function delete()
    {
        if (!$this->filterExists('api_key_scope.id') && (!$this->FilterExists('key_id') || !$this->filterExists('scope_id'))) throw new Exception("Invalid data",400);
        return  $this->mysqlDelete("DELETE FROM api_key_scope");
    }
}