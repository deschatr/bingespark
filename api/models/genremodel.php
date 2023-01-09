<?php

require_once API_ROOT_PATH . "/models/basemodel.php";
require_once API_ROOT_PATH . "/models/moviegenremodel.php";

class GenreModel extends BaseModel
{
    public $equalFilters = array('genre.id','movie_id');
    public $likeFilters = array('name');
    public $sorts = array('name');

    public function count()
    {
        $query = "SELECT COUNT(genre.id) AS number FROM genre";
        if ($this->filterExists('movie_id')) $query .= " JOIN movie_genre ON movie_genre.genre_id=genre.id JOIN movie ON movie.id=movie_genre.movie_id";
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = "SELECT genre.id, genre.name FROM genre";
        if ($this->filterExists('movie_id')) $query .= " JOIN movie_genre ON movie_genre.genre_id=genre.id JOIN movie ON movie.id=movie_genre.movie_id";
        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        if (!array_key_exists('name',$data)) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO genre (name) VALUES (?)",
                                    "s",$data['name']);
    }

    public function update($data)
    {
        if (!$this->filterExists('genre.id')) throw new Exception("Invalid data",400);
        if (!array_key_exists('name',$data)) throw new Exception("Invalid data",400);
        
        return $this->mysqlUpdate("UPDATE genre SET name=?",
                                    "s",$data['name']);
    }

    public function delete()
    {
        if (!$this->filterExists('genre.id')) throw new Exception("Invalid data",400);
        $model = new MovieGenreModel;
        $model->addEqualFilter('genre_id',$this->getEqualFilter('genre.id'));
        $model->delete();
        return  $this->mysqlDelete("DELETE FROM genre");
    }
}

?>