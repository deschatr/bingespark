<?php

require_once API_ROOT_PATH . "/models/basemodel.php";

class CreditModel extends BaseModel
{
    public $equalFilters = array('credit.id','movie_id','person_id','role_id','role');
    public $sorts = array('last_name','title','role','count(movie_id)');
    public $groups = array('person_id');

    public function count()
    {
        $query = 'SELECT COUNT(credit.id) AS number FROM credit';
        if ($this->sortExists('title') || $this->sortExists('count(movie_id)')) $query .= " JOIN movie ON movie.id=movie_id";
        if ($this->sortExists('last_name')) $query .= " JOIN person ON person.id=person_id";
        if ($this->sortExists('role') || $this->filterExists('role')) $query .= " JOIN role ON role.id=role_id";
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = "SELECT credit.id, movie_id, person_id, role_id FROM credit";
        if ($this->sortExists('title') || $this->sortExists('count(movie_id)')) $query .= " JOIN movie ON movie.id=movie_id";
        if ($this->sortExists('last_name')) $query .= " JOIN person ON person.id=person_id";
        if ($this->sortExists('role') || $this->filterExists('role')) $query .= " JOIN role ON role.id=role_id";
        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        if (!array_keys_exist($data,'movie_id','person_id','role_id')) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO credit (movie_id,person_id,role_id) VALUES (?,?,?)",
                                "iii",$data['movie_id'],$data['person_id'],$data['role_id']);
    }

    public function update($data)
    {
        if (!$this->filterExists('credit.id')) throw new Exception("Invalid data",400);
        if (!array_keys_exist($data,'movie_id','person_id','role_id')) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE credit SET movie_id=?, person_id=?, role_id=?",
                                "iii",$data['movie_id'],$data['person_id'],$data['role_id']);
    }

    public function delete()
    {
        if (!$this->filterExists('credit.id') && !$this->filterExists('movie_id') && !$this->filterExists('person_id') && !$this->filterExists('role_id'))
            throw new Exception("Invalid data",400);
        return $this->mysqlDelete("DELETE FROM credit");
    }
}

?>