<?php

require_once API_ROOT_PATH . "/controllers/basecontroller.php";
require_once API_ROOT_PATH . "/models/usermodel.php";
require_once API_ROOT_PATH . "/models/reviewmodel.php";
require_once API_ROOT_PATH . "/models/favouritemodel.php";
require_once API_ROOT_PATH . "/models/ratingmodel.php";
require_once API_ROOT_PATH . "/models/moviemodel.php";
require_once API_ROOT_PATH . "/models/apikeymodel.php";

class UserController extends BaseController
{
    // POST /users /users/{id}/favourites
    protected function create($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (isset($id['users']))
        {
            if (!$keyModel->hasRights('users.write',$param['apikey'])) throw new Exception('API key invalid',401);

            if (count($id)==1 && $id['users']==0)
            {
                $model = new UserModel;
                $insert_id = $model->insert($data);
                $model->addEqualFilter('user.id',$insert_id);
                $results = $model->select();
                if (empty($results)) throw new Exception("Invalid ID",404);
                // add details here if needed
                return $results[0];
            }
            elseif (count($id)==2 && $id['users']!=0 && isset($id['favourites']) && $id['favourites']==0)
            {
                $keyModel = new APIKeyModel;
                if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
                if (!$keyModel->hasRights('favourites.write',$param['apikey'])) throw new Exception('API key invalid',401);
                
                $model = new FavouriteModel;
                $data['user_id'] = $id['users'];
                $insert_id = $model->insert($data);
                $model->addEqualFilter('favourite.id',$insert_id);
                $results = $model->select();
                return $results[0];
            }
        }
        throw new Exception('Invalid URI',400);
    }
    
    //GET /users /users/{id} /users/{id}/favourites /users/{id}/reviews /users/{id}/ratings
    protected function read($id,$param,$data)
    {
        if (isset($id['users']))
        {
            if (count($id) == 1)
            {
                $keyModel = new APIKeyModel;
                if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
                if (!$keyModel->hasRights('users.read',$param['apikey'])) throw new Exception('API key invalid',401);

                $model = new UserModel;

                if ($id['users']==0)
                {
                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();

                    $this->addSorting('updated_at','desc',$param,$model);

                    $results = $model->select();
                    $response = [ 'page' => intdiv($offset,$limit)+1, intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                    return $response;
                }
                else
                {
                    $model->addEqualFilter('user.id',$id['users']);
                    $results = $model->select();
                    if (empty($results)) throw new Exception("Invalid ID",404);
                    return $results[0];
                }
            }
            elseif (count($id)==2 && $id['users']!=0)
            {
                if (isset($id['favourites']) && $id['favourites']==0)
                {
                    $keyModel = new APIKeyModel;
                    if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
                    if (!$keyModel->hasRights('favourites.read',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new FavouriteModel;
                    $model->addEqualFilter('user_id',$id['users']);

                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();

                    $this->addSorting('updated_at','desc',$param,$model);

                    $results = $model->select();

                    for ($favouriteIndex=0; $favouriteIndex<count($results); $favouriteIndex++)
                        $this->addFavouriteDetails($results[$favouriteIndex]);

                    $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                    return $response;
                }
                elseif (isset($id['reviews']) && $id['reviews']==0)
                {
                    $keyModel = new APIKeyModel;
                    if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
                    if (!$keyModel->hasRights('reviews.read',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new ReviewModel;
                    $model->addEqualFilter('user_id',$id['users']);

                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();

                    $this->addSorting('updated_at','desc',$param,$model);
    
                    $results = $model->select();
                    
                    for ($reviewIndex=0; $reviewIndex<count($results); $reviewIndex++)
                        $this->addReviewDetails($results[$reviewIndex]);

                    $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                    return $response;
                }
                elseif (isset($id['ratings']) && $id['ratings']==0)
                {
                    $keyModel = new APIKeyModel;
                    if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
                    if (!$keyModel->hasRights('ratings.read',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new RatingModel;
                    $model->addEqualFilter('user_id',$id['users']);

                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();

                    $this->addSorting('updated_at','desc',$param,$model);
    
                    $results = $model->select();
                    
                    for ($ratingIndex=0; $ratingIndex<count($results); $ratingIndex++)
                        $this->addRatingDetails($results[$ratingIndex]);

                    $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=> $results];
                    return $response;
                }
            }
        }
        throw new Exception('Invalid ID',400);
    }
 
    // PUT /users/{id}
    protected function update($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('users.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (isset($id['users']) && count($id)==1 && $id['users']!=0)
        {
            $model = new UserModel;
            $model->addEqualFilter('user.id',$id['users']);
            $affected_rows = $model->update($data);
            $results = $model->select();
            return $results[0];
        }
        throw new Exception('Invalid URI',400);
    }
 
    // DELETE /users/{id}
    protected function delete($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (isset($id['users']) && $id['users']!=0)
        {
            if (!$keyModel->hasRights('users.write',$param['apikey'])) throw new Exception('API key invalid',401);
            
            if (count($id)==1)
            {
                $model = new UserModel;
                $model->addEqualFilter('user.id',$id['users']);
                $affected_rows = $model->delete($id);
                return null;
            }
            elseif ($count($id)==2 && isset($id['favourites']) && $id['favourites']!=0)
            {
                $model = new FavouriteModel;
                $model->addEqualFilter('favourite.id',$id['favourites']);
                $affected_rows = $model->delete($id);
                return null;
            }
        }
        throw new Exception('Invalid URI',400);
    }

    // GET /users/login
    protected function login($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('users.read',$param['apikey'])) throw new Exception('API key invalid',401);

        if (!isset($data['username']) || !isset($data['password'])) throw new Exception('Invalid data',400);

        $model = new UserModel;

        if ($model->verifyPassword($data['username'],$data['password']))
        {
            $model->addEqualFilter('username',$data['username']);
            $results = $model->select();
            if (empty($results)) throw new Exception("Invalid ID",404);
            return $results[0];
        }

        return null;

    }

    protected function addFavouriteDetails(&$favourite)
    {
        $movieModel = new MovieModel;
        $movieModel->addEqualFilter('movie.id',$favourite['movie_id']);
        $movie = $movieModel->select()[0];
        $favourite['movie_title'] = $movie['title'];
        $favourite['movie_release_year'] = $movie['release_year'];
        unset($favourite['user_id']);
    }

    protected function addReviewDetails(&$review)
    {
        $movieModel = new MovieModel;
        $movieModel->addEqualFilter('movie.id',$review['movie_id']);
        $movie = $movieModel->select()[0];
        $review['movie_title'] = $movie['title'];
        $review['movie_release_year'] = $movie['release_year'];
        unset($review['user_id']);
    }

    protected function addRatingDetails(&$rating)
    {
        $movieModel = new MovieModel;
        $movieModel->addEqualFilter('movie.id',$rating['movie_id']);
        $movie = $movieModel->select()[0];
        $rating['movie_title'] = $movie['title'];
        $rating['movie_release_year'] = $movie['release_year'];
        unset($rating['user_id']);
    }
}

?>