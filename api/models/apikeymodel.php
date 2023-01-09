<?php

require_once API_ROOT_PATH . "/models/basemodel.php";

class APIKeyModel extends BaseModel
{
    public $equalFilters = array('api_key.id','user_id');
    public $rangeFilters = array('issued_at');
    public $sorts = array('issued_at');

    public function count()
    {
        $query = 'SELECT COUNT(api_key.id) AS number FROM api_key';
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = 'SELECT api_key.id, encrypted_key, user_id, issued_at FROM api_key';
        $result = $this->mysqlSelect($query);
        return $result;
    }

    public function insert($data)
    {
        // following line comented out to allow for api keys without user (website)
        // if (!array_keys_exist($data,'user_id')) throw new Exception("Invalid data",400);
        if (isset($data['user_id'])) return $this->mysqlInsert("INSERT INTO api_key (encrypted_key,user_id) VALUES (?,?)",
            "si",password_hash($data['apikey'], PASSWORD_DEFAULT),$data['user_id']);
        else return $this->mysqlInsert("INSERT INTO api_key (encrypted_key) VALUES (?)",
            "s",password_hash($data['apikey'], PASSWORD_DEFAULT));
    }

    public function update($data)
    {
        if (!$this->filterExists('api_key.id')) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("UPDATE api_key SET encrypted_key=?,user_id=?",
                                "si",password_hash($data['apikey'], PASSWORD_DEFAULT),$data['user_id']);
    }

    public function delete()
    {
        if (!$this->filterExists('api_key.id')) throw new Exception("Invalid data",400);
        return $this->mysqlDelete("DELETE FROM api_key");
    }

    public function hasRights($scope,$apiKey)
    {
        $results = $this->select("SELECT api_key_scope.id. encrypted_key FROM api_key_scope " .
                                "JOIN api_key ON api_key.id=api_key_scope.key_id " .
                                "JOIN api_scope ON api_scope.id=api_key_scope.scope_id ".
                                "WHERE scope=?",'s',$scope);
        foreach ($results as $result)
        {
            if (password_verify($apiKey,$result['encrypted_key'])) return true;
        }
        return false;
    }

    public function generate() {
        return md5(time()*rand());
    }
}

?>