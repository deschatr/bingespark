<?php

require_once API_ROOT_PATH . "/models/basemodel.php";

Class RatingModel extends BaseModel
{
    public $equalFilters = ['rating.id','movie_id','user_id'];
    public $sorts = ['updated_at'];

    public function count()
    {
        $query = 'SELECT COUNT(rating.id) AS number FROM rating';
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = 'SELECT rating.id, movie_id, user_id, score, updated_at FROM rating';
        return $this->mysqlSelect($query);
    }

    public function selectAverage() {
        $query = "SELECT AVG(score) as average_rating FROM rating";
        $result = $this->mysqlSelect($query);
        if (array_key_exists('average_rating',$result[0])) return floatval($result[0]['average_rating']);
        return null;
    }

    public function insert($data)
    {
        if (!array_keys_exist($data,'movie_id','user_id','score')) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO rating (movie_id,user_id,score) VALUES (?,?,?)","iii",$data['movie_id'],$data['user_id'],$data['score']);
    }

    public function update($data)
    {
        if (!$this->filterExists('rating.id')) throw new Exception("Invalid data",400);
        if (!array_key_exists('name',$data)) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE rating SET movie_id=?, user_id=?, score=?","iii",$data['movie_id'],$data['user_id'],$data['score']);
    }

    public function delete()
    {
        if (!$this->filterExists('rating.id') && !$this->filterExists('movie_id') && !$this->filterExists('user_id')) throw new Exception("Invalid data",400);
        return $this->mysqlDelete("DELETE FROM rating");
    }
}

?>