<?php

require_once API_ROOT_PATH . "/models/basemodel.php";

class MovieGenreModel extends BaseModel
{
    public $equalFilters = array('movie_genre.id','genre_id','movie_id');
    public $sorts = array('title','name');

    public function count()
    {
        $query = "SELECT COUNT(movie_genre.id) AS number FROM movie_genre";
        if ($this->filterExists('title')) $query .= " JOIN movie ON movie.id=movie_genre.movie_id";
        if ($this->filterExists('name')) $query .= " JOIN genre ON genre.id=movie_genre.genre_id";
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = "SELECT movie_genre.id, movie_id, genre_id FROM movie_genre";
        if ($this->filterExists('title') || $this->sortExists('title')) $query .= " JOIN movie ON movie.id=movie_genre.movie_id";
        if ($this->filterExists('name') || $this->sortExists('name')) $query .= " JOIN genre ON genre.id=movie_genre.genre_id";
        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        if (!array_key_exists('movie_id',$data) || !array_key_exists('genre_id',$data)) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO movie_genre (movie_id, genre_id) VALUES (?,?)",
                                    "ii",$data['movie_id'],$data['genre_id']);
    }

    public function update($data)
    {
        if (!$this->filterExists('movie_genre.id')) throw new Exception("Invalid data",400);
        if (!array_key_exists('movie_id',$data) || !array_key_exists('genre_id',$data)) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE movie_genre SET movie_id=?, genre_id=?",
                                    "ii",$data['movie_id'],$data['genre_id']);
    }

    public function delete()
    {
        if (!$this->filterExists('movie_genre.id') && !$this->filterExists('movie_id') && !$this->filterExists('genre_id')) throw new Exception("Invalid data",400);
        return  $this->mysqlDelete("DELETE FROM movie_genre");
    }
}

?>