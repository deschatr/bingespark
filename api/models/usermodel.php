<?php

require_once API_ROOT_PATH . "/models/basemodel.php";
require_once API_ROOT_PATH . "/models/reviewmodel.php";
require_once API_ROOT_PATH . "/models/ratingmodel.php";
require_once API_ROOT_PATH . "/models/favouritemodel.php";

class UserModel extends BaseModel
{
    public $equalFilters = ['user.id','usergroup_id','username'];
    public $sorts = ['username','last_name','updated_at'];

    public function count()
    {
        $query = 'SELECT COUNT(user.id) AS number FROM user';
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = 'SELECT user.id,usergroup_id,username,first_name,last_name,email,encrypted_password,updated_at FROM user';
        $result = $this->mysqlSelect($query);
        return $result;
    }

    public function insert($data)
    {
        if (!array_keys_exist($data,'usergroup_id','username','first_name','last_name','email','password')) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO user (usergroup_id,username,first_name,last_name,email,encrypted_password) " .
                                    "VALUES (?,?,?,?,?,?)",
                                    "isssss",$data['usergroup_id'],$data['username'],$data['first_name'],$data['last_name'],$data['email'],password_hash($data['password'], PASSWORD_DEFAULT));
    }
    
    public function update($data)
    {
        if (!$this->filterExists('user.id')) throw new Exception("Invalid data",400);
        if (!array_keys_exist($data,'usergroup_id','username','first_name','last_name','email')) throw new Exception("Invalid data",400);
        if (isset($data['password']))
            return $this->mysqlUpdate("UPDATE user SET usergroup_id=?, username=?, first_name=?, last_name=?, email=?, encrypted_password=?",
                                    "isssss",$data['usergroup_id'],$data['username'],$data['first_name'],$data['last_name'],$data['email'],password_hash($data['password'], PASSWORD_DEFAULT));
        else return $this->mysqlUpdate("UPDATE user SET usergroup_id=?, username=?, first_name=?, last_name=?, email=?",
                                    "issss",$data['usergroup_id'],$data['username'],$data['first_name'],$data['last_name'],$data['email']);
    }

    public function delete()
    {
        if (!$this->filterExists('user.id')) throw new Exception("Invalid data",400);
        $model = new ReviewModel;
        $model->addEqualFilter('user_id',$this->getEqualFilter('user.id'));
        $model->delete();
        $model = new RatingModel;
        $model->addEqualFilter('user_id',$this->getEqualFilter('user.id'));
        $model->delete();
        $model = new FavouriteModel;
        $model->addEqualFilter('user_id',$this->getEqualFilter('user.id'));
        $model->delete();
        return $this->mysqlDelete("DELETE FROM user");
    }

    public function verifyPassword($username, $password)
    {
        $results = $this->mysqlSelect("SELECT encrypted_password FROM user WHERE username=?",'s',$username);
        if (empty($results)) return false;
        return password_verify($password,$results[0]['encrypted_password']);
    }
}