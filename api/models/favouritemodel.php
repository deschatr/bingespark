<?php

require_once API_ROOT_PATH . "/models/basemodel.php";

Class favouriteModel extends BaseModel
{
    public $equalFilters = array('favourite.id','movie_id','user_id');
    public $sorts = array('title');

    public function count()
    {
        $query = "SELECT COUNT(favourite.id) AS number FROM favourite";
        if ($this->filterExists('movie_id') || $this->sortExists('title')) $query .= " JOIN movie ON movie.id=favourite.movie_id";
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = "SELECT favourite.id, movie_id, user_id FROM favourite";
        if ($this->filterExists('movie_id') || $this->sortExists('title')) $query .= " JOIN movie ON movie.id=favourite.movie_id";
        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        if (!array_keys_exist($data,'movie_id','user_id')) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO favourite (movie_id,user_id) VALUES (?,?)",
                                    "ii",$data['movie_id'],$data['user_id']);
    }

    public function update($data)
    {
        if (!$this->filterExists('favourite.id')) throw new Exception("Invalid data",400);
        if (!array_key_exists($data,'movie_id','user_id')) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE favourite SET movie_id=?, user_id=?",
                                    "ii",$data['movie_id'],$data['user_id']);
    }

    public function delete()
    {
        if (!$this->filterExists('favourite.id') && !$this->filterExists('movie_id') && !$this->filterExists('user_id')) throw new Exception("Invalid data",400);
        return $this->mysqlDelete("DELETE FROM favourite");
    }
}

?>