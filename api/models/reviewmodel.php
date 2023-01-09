<?php

require_once API_ROOT_PATH . "/models/basemodel.php";

class ReviewModel extends BaseModel
{
    public $equalFilters = ['review.id'];
    public $sorts = ['updated_at'];

    public function count()
    {
        $query = 'SELECT COUNT(review.id) AS number FROM review';
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = 'SELECT review.id, movie_id, user_id, comment, updated_at FROM review';
        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        if (!array_keys_exist($data,'movie_id','user_id','comment')) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO review (movie_id, user_id, comment) VALUES (?,?,?)","iis",$data['movie_id'],$data['user_id'],$data['comment']);
    }
    
    public function update($data)
    {
        if (!$this->filterExists('review.id')) throw new Exception("Invalid data",400);
        if (!array_keys_exist($data,'movie_id','user_id','comment')) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE review SET movie_id=?, user_id=?, comment=?","iis",$data['movie_id'],$data['user_id'],$data['comment']);
    }

    public function delete()
    {
        if (!$this->filterExists('review.id') && !$this->filterExists('movie_id') && !$this->filterExists('user_id')) throw new Exception("Invalid data",400);
        return $this->mysqlDelete("DELETE FROM review");
    }
}

?>