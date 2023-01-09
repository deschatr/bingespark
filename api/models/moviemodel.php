<?php

require_once API_ROOT_PATH . "/models/basemodel.php";
require_once API_ROOT_PATH . "/models/moviegenremodel.php";
require_once API_ROOT_PATH . "/models/creditmodel.php";
require_once API_ROOT_PATH . "/models/reviewmodel.php";
require_once API_ROOT_PATH . "/models/ratingmodel.php";
require_once API_ROOT_PATH . "/models/favouritemodel.php";

class MovieModel extends BaseModel
{
    public $equalFilters = array('movie.id','movie_id','release_year','genre_id','person_id','role_id');
    public $rangeFilters = array('rating','release_year','runtime','revenue');
    public $likeFilters = array('title','overview');
    public $sorts = array('title','release_year','revenue','runtime','rating');

    public function count()
    {
        $query = "SELECT COUNT(movie.id) AS number FROM movie";

        if ($this->filterExists('genre_id')) $query .= " JOIN movie_genre ON movie.id=movie_genre.movie_id JOIN genre ON movie_genre.genre_id=genre.id";
        if ($this->filterExists('person_id')) $query .= " JOIN credit ON movie.id=credit.movie_id JOIN person ON person.id=credit.person_id";

        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = "SELECT movie.id, movie.title, movie.release_year, movie.TMDB_poster_path, movie.overview, movie.runtime, movie.revenue FROM movie";

        if ($this->filterExists('genre_id')) $query .= " JOIN movie_genre ON movie.id=movie_genre.movie_id JOIN genre ON movie_genre.genre_id=genre.id";
        if ($this->filterExists('person_id')) $query .= " JOIN credit ON movie.id=credit.movie_id JOIN person ON person.id=credit.person_id";

        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        if (!array_keys_exist($data,'title','release_year','overview','runtime','revenue')) throw new Exception("Invalid data",400);
        return $this->mysqlInsert("INSERT INTO movie (title,release_year,overview,runtime,revenue) VALUES (?,?,?,?,?)",
                                    'sisid',$data['title'],$data['release_year'],$data['overview'],$data['runtime'],$data['revenue']);
    }

    public function update($data)
    {
        if (!$this->filterExists('movie.id')) throw new Exception("Invalid data",400);
        if (!array_keys_exist($data,'title','release_year','overview','runtime','revenue')) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE movie SET title=?, release_year=?, overview=?, runtime=?, revenue=?",
                                    'sisid',$data['title'],$data['release_year'],$data['overview'],$data['runtime'],$data['revenue']);
    }

    public function delete()
    {
        if (!$this->filterExists('movie.id')) throw new Exception("Invalid data",400);
        $model = new MovieGenreModel;
        $model->addEqualFilter('movie_id',$this->getEqualFilter('movie.id'));
        $model->delete();
        $model = new CreditModel;
        $model->addEqualFilter('movie_id',$this->getEqualFilter('movie.id'));
        $model->delete();
        $model = new ReviewModel;
        $model->addEqualFilter('movie_id',$this->getEqualFilter('movie.id'));
        $model->delete();
        $model = new RatingModel;
        $model->addEqualFilter('movie_id',$this->getEqualFilter('movie.id'));
        $model->delete();
        $model = new FavouriteModel;
        $model->addEqualFilter('movie_id',$this->getEqualFilter('movie.id'));
        $model->delete();
        return $this->mysqlDelete("DELETE FROM movie");
    }
}