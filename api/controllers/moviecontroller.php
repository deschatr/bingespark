<?php

require_once API_ROOT_PATH . "/controllers/basecontroller.php";
require_once API_ROOT_PATH . "/models/moviemodel.php";
require_once API_ROOT_PATH . "/models/genremodel.php";
require_once API_ROOT_PATH . "/models/moviegenremodel.php";
require_once API_ROOT_PATH . "/models/rolemodel.php";
require_once API_ROOT_PATH . "/models/personmodel.php";
require_once API_ROOT_PATH . "/models/creditmodel.php";
require_once API_ROOT_PATH . "/models/reviewmodel.php";
require_once API_ROOT_PATH . "/models/ratingmodel.php";
require_once API_ROOT_PATH . "/models/favouritemodel.php";
require_once API_ROOT_PATH . "/models/apikeymodel.php";

class MovieController extends BaseController
{
    // POST /movies /movies/{id}/credits /movies/{id}/reviews
    protected function create($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (array_key_exists('movies',$id))
        {
            if (count($id)==1 && $id['movies']==0)
            {
                if (!$keyModel->hasRights('movies.write',$param['apikey'])) throw new Exception('API key invalid',401);
        
                $model = new MovieModel;
                $insert_id = $model->insert($data);
                $model->addEqualFilter('movie.id',$insert_id);
                $results = $model->select();
                if (empty($results)) throw new Exception("Invalid ID",404);
                $this->addMovieDetails($results[0]);
                return $results[0];
            }
            elseif (count($id) == 2 && $id['movies']!=0)
            {
                if (array_key_exists('genres',$id) && $id['genres']==0)
                {
                    if (!$keyModel->hasRights('movies.write',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new MovieGenreModel;
                    $data+=['movie_id' => $id['movies']];
                    $insert_id = $model->insert($data);
                    $model->addEqualFilter('movie_genre.id',$insert_id);
                    $results = $model->select();
                    $this->addMovieGenreDetails($results[0]);
                    return $results[0];
                }
                elseif (array_key_exists('credits',$id) && $id['credits']==0)
                {
                    if (!$keyModel->hasRights('credits.write',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new CreditModel;
                    $data+=['movie_id' => $id['movies']];
                    $insert_id = $model->insert($data);
                    $model->addEqualFilter('credit.id',$insert_id);
                    $results = $model->select();
                    $this->addCreditDetails($results[0]);
                    return $results[0];
                }
                elseif (array_key_exists('reviews',$id) && $id['reviews']==0)
                {
                    if (!$keyModel->hasRights('reviews.write',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new ReviewModel;
                    $data+=['movie_id' => $id['movies']];
                    $insert_id = $model->insert($data);
                    $model->addEqualFilter('review.id',$insert_id);
                    $results = $model->select();
                    $this->addReviewDetails($results[0]);
                    return $results[0];
                }
                elseif (array_key_exists('ratings',$id) && $id['ratings']==0)
                {
                    if (!$keyModel->hasRights('ratings.write',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new RatingModel;
                    $data+=['movie_id' => $id['movies']];
                    $insert_id = $model->insert($data);
                    $model->addEqualFilter('rating.id',$insert_id);
                    return $model->select();
                    $this->addRatingDetails($results[0]);
                    return $results[0];
                }
            }
        }
        throw new Exception('Invalid URI',400);
    }
    
    // GET /movies /movies/{id} /movies/{id}/credits /movies/{id}/reviews /movies/{id}/ratings
    protected function read($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (array_key_exists('movies',$id))
        {

            if (count($id) == 1)
            {
                if (!$keyModel->hasRights('movies.read',$param['apikey'])) throw new Exception('API key invalid',401);

                $model = new MovieModel;

                if ($id['movies']==0)
                {
                    $this->addEqualFiltering('genre_id',$param,$model);
                    $this->addEqualFiltering('release_year',$param,$model);
                    $this->addEqualFiltering('person_id',$param,$model);
                    $this->addEqualFiltering('role_id',$param,$model);
    
                    $this->addRangefiltering('release_year',$param,$model);
                    $this->addRangefiltering('revenue',$param,$model);
                    $this->addRangefiltering('runtime',$param,$model);
                    $this->addRangefiltering('average_rating',$param,$model);

                    if (isset($param['search_query']) && !isset($param['search_scope'])) $param['search_scope']='all';
                    
                    if (isset($param['search_scope']) && ($param['search_scope']=='title' || $param['search_scope']=='all'))
                    $model->addLikeFilter('title',$param['search_query']);
                    if (isset($param['search_scope']) && ($param['search_scope']=='overview' || $param['search_scope']=='all'))
                    $model->addLikeFilter('overview',$param['search_query']);
    
                    $this->addSorting('title','asc',$param,$model);

                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();
    
                    $results = $model->select();
                    for ($movieIndex=0; $movieIndex<count($results); $movieIndex++)
                    {
                        $results[$movieIndex]['overview'] = htmlspecialchars($results[$movieIndex]['overview']);
                        $this->addMovieDetails($results[$movieIndex]);
                    }
                    $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                    return $response;
                }
                else
                {
                    $model->addEqualFilter('movie.id',$id['movies']);
                    $results = $model->select();
                    if (empty($results)) throw new Exception("Invalid ID",404);
                    $this->addMovieDetails($results[0]);
                    return $results[0];
                }
            }
            elseif (count($id)==2 && $id['movies']!=0)
            {
                if (array_key_exists('genres',$id) && $id['genres']==0)
                {
                    if (!$keyModel->hasRights('genres.read',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new MovieGenreModel;
                    $model->addEqualFilter('movie_id',$id['movies']);

                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();
                    $this->addSorting('name','asc',$param,$model);
    
                    $results = $model->select();

                    for ($movieGenreIndex=0; $movieGenreIndex<count($results); $movieGenreIndex++)
                        $this->addMovieGenreDetails($results[$movieGenreIndex]);

                    $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                    return $response;
               }
                elseif (array_key_exists('credits',$id) && $id['credits']==0)
                {
                    if (!$keyModel->hasRights('credits.read',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new CreditModel;
                    $model->addEqualFilter('movie_id',$id['movies']);

                    $this->addEqualFiltering('role_id',$param,$model);
                    $this->addEqualFiltering('role',$param,$model);
                    $this->addEqualFiltering('movie_id',$param,$model);
                    $this->addEqualFiltering('person_id',$param,$model);
    
                    $this->addGrouping('person_id',$param,$model);
    
                    $this->addSorting(null,null,$param,$model);

                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();
    
                    $results = $model->select();

                    for ($creditIndex=0; $creditIndex<count($results); $creditIndex++)
                        $this->addCreditDetails($results[$creditIndex]);
    
                    $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                    return $response;

                }
                elseif (array_key_exists('reviews',$id) && $id['reviews']==0)
                {
                    if (!$keyModel->hasRights('reviews.read',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new ReviewModel;
                    $model->addEqualFilter('movie_id',$id['movies']);

                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();

                    $this->addSorting(null,null,$param,$model);
    
                    $results = $model->select();

                    for ($reviewIndex=0; $reviewIndex<count($results); $reviewIndex++)
                        $this->addReviewDetails($results[$reviewIndex]);
                    
                    $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                    return $response;
                }
                elseif (array_key_exists('ratings',$id) && $id['ratings']==0)
                {
                    if (!$keyModel->hasRights('ratings.read',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new RatingModel;
                    $model->addEqualFilter('movie_id',$id['movies']);

                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();

                    $this->addSorting(null,null,$param,$model);
    
                    $results = $model->select();

                    for ($ratingIndex=0; $ratingIndex<count($results); $ratingIndex++)
                        $this->addRatingDetails($results[$ratingIndex]);

                    $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                    return $response;
                }
            }
        }
        throw new Exception('Invalid ID',400); 
    }
 
    // PUT /movies/{id}
    protected function update($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('movies.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('movies',$id) && count($id)==1 && $id['movies']!=0)
        {
            $model = new MovieModel;
            $model->addEqualFilter('movie.id',$id['movies']);
            $affected_rows = $model->update($data);
            return $model->select();
        }
        throw new Exception('Invalid URI',400);
    }
 
    // DELETE /movies/{id}
    protected function delete($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (array_key_exists('movies',$id) && $id['movies']!=0)
            if (count($id)==1)
            {
                if (!$keyModel->hasRights('movies.write',$param['apikey'])) throw new Exception('API key invalid',401);

                $model = new MovieModel;
                $model->addEqualFilter('movie.id',$id['movies']);
                $affected_rows = $model->delete();
                return null;
            }
            elseif (count($id)==2)
            {
                if (array_key_exists('genres',$id) && $id['genres']!=0)
                {
                    if (!$keyModel->hasRights('genres.write',$param['apikey'])) throw new Exception('API key invalid',401);

                    $model = new MovieGenreModel;
                    $model->addEqualFilter('movie_id',$id['movies']);
                    $model->addEqualFilter('genre_id',$id['genres']);
                    $affected_rows = $model->delete();
                    return null;
                }
                elseif (array_key_exists('credits',$id) && $id['credits']!=0)
                {
                    if (!$keyModel->hasRights('credits.write',$param['apikey'])) throw new Exception('API key invalid',401);

                    $model = new CreditModel;
                    $model->addEqualFilter('credit.id',$id['credits']);
                    $affected_rows = $model->delete();
                    return null;
                }
                elseif (array_key_exists('reviews',$id) && $id['reviews']!=0)
                {
                    if (!$keyModel->hasRights('reviews.write',$param['apikey'])) throw new Exception('API key invalid',401);

                    $model = new ReviewModel;
                    $model->addEqualFilter('review.id',$id['reviews']);
                    $affected_rows = $model->delete();
                    return null;
                }
                elseif (array_key_exists('ratings',$id) && $id['ratings']!=0)
                {
                    if (!$keyModel->hasRights('ratings.write',$param['apikey'])) throw new Exception('API key invalid',401);

                    $model = new RatingModel;
                    $model->addEqualFilter('rating.id',$id['ratings']);
                    $affected_rows = $model->delete();
                    return null;
                }
            }
        throw new Exception('Invalid URI',400);
    }

    protected function addMovieDetails(&$movie)
    {
        $ratingModel = new RatingModel;
        $favouriteModel = new FavouriteModel;
        $genreModel = new GenreModel;
        $roleModel = new RoleModel;
        $personModel = new PersonModel;

        $ratingModel->addEqualFilter("movie_id",$movie['id']);
        $movie += ['average_rating' => $ratingModel->selectAverage()];
        $movie += ['total_ratings' => $ratingModel->count()];
        $favouriteModel->addEqualFilter("movie_id",$movie['id']);
        $movie += ['favourites' => $favouriteModel->count()];

        $genreModel->resetFilters();
        $genreModel->addEqualFilter('movie_id',$movie['id']);
        $genreModel->addSort('name','ASC');
        $movie['genres'] = $genreModel->select();
    }

    protected function addMovieGenreDetails(&$movieGenre)
    {
        $genreModel = new GenreModel();

        $genreModel->addEqualFilter('genre.id',$movieGenre['genre_id']);
        $genre = $genreModel->select()[0];
        $movieGenre += [ 'name' => $genre['name'] ];

        unset($movieGenre['id']);
        unset($movieGenre['movie_id']);
    }

    protected function addCreditDetails(&$credit)
    {
        $personModel = new PersonModel();
        $roleModel = new RoleModel();

        $personModel->addEqualFilter('person.id',$credit['person_id']);
        $person = $personModel->select()[0];
        $credit += [ 'first_name' => $person['first_name'] ];
        $credit += [ 'last_name' => $person['last_name'] ];

        $roleModel->addEqualFilter('role.id',$credit['role_id']);
        $role = $roleModel->select()[0];
        $credit += [ 'role' => $role['role'] ];

        unset($credit['movie_id']);
    }

    protected function addReviewDetails(&$review)
    {
        unset($review['movie_id']);
    }

    protected function addRatingDetails(&$rating)
    {
        unset($rating['movie_id']);
    }
}

?>