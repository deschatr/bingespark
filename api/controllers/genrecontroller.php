<?php

require_once API_ROOT_PATH . "/controllers/basecontroller.php";
require_once API_ROOT_PATH . "/models/genremodel.php";
require_once API_ROOT_PATH . "/models/moviemodel.php";
require_once API_ROOT_PATH . "/models/rolemodel.php";
require_once API_ROOT_PATH . "/models/personmodel.php";
require_once API_ROOT_PATH . "/models/ratingmodel.php";
require_once API_ROOT_PATH . "/models/apikeymodel.php";

class GenreController extends BaseController
{
    // POST /genre
    protected function create($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('genres.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('genres',$id) && count($id)==1 && $id['genres']==0)
        {
            $model = new GenreModel;
            $insert_id = $model->insert($data);
            $model->addEqualFilter('genre.id',$insert_id);
            $results = $model->select();
            return $results[0];
        }
        throw new Exception('Invalid URI',400);
    }

    // GET /genre /genre/{id} /genres/{id}/movies
    protected function read($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (array_key_exists('genres',$id))
        {
            if (count($id)==1) {

                if (!$keyModel->hasRights('genres.read',$param['apikey'])) throw new Exception('API key invalid',401);
        
                $model = new GenreModel;
                if ($id['genres']==0)
                {
                    $this->addEqualFiltering('name',$param,$model);
                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();
                    $this->addSorting('name','asc',$param,$model);
    
                    $results = $model->select();
                    $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                    return $response;
                }
                else
                {
                    $model->addEqualFilter('genre.id',$id['genres']);
                    $results = $model->select();
                    if (empty($results)) throw new Exception("Invalid ID",404);
                    return $results[0];
                }
                return $model->select();
            }
            elseif (count($id)==2 && $id['genres']!=0)
            {
                if (isset($id['movies']) && $id['movies']==0)
                {
                    if (!$keyModel->hasRights('movies.read',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new MovieModel;
                    $model->addEqualFilter('genre_id',$id['genres']);

                    $this->addEqualFiltering('release_year',$param,$model);
                    $this->addEqualFiltering('person_id',$param,$model);
                    $this->addRangeFiltering('release_year',$param,$model);

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

                    return $results;
                }
            }
        }
        throw new Exception('Invalid URI',400);       
    }

    // PUT /genres/{id}
    protected function update($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('genres.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (isset($id['genres']) && count($id)==1 && $id['genres']!=0)
        {
            $model = new GenreModel;
            $model->addEqualFilter('genre.id',$id['genres']);
            $affected_rows = $model->update($data);
            return $model->select()[0];
        }
        throw new Exception('Invalid URI',400);
    }

    // DELETE /genres/{id}
    protected function delete($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('genres.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (isset($id['genres']) && count($id)==1 && $id['genres']!=0)
        {
            $model = new GenreModel;
            $model->addEqualFilter('genre.id',$id['genres']);
            $affected_rows = $model->delete();
            return null;
        }
        throw new Exception('Invalid URI',400);
    }

    protected function addMovieDetails(&$movie)
    {
        $ratingModel = new RatingModel;
        $genreModel = new GenreModel;
        $roleModel = new RoleModel;
        $personModel = new PersonModel;

        $movie += ['rating' => $ratingModel->selectAverage($movie['id'])];

        $genreModel->resetFilters();
        $genreModel->addEqualFilter('movie_id',$movie['id']);
        $genreModel->addSort('name','ASC');
        $movie['genres'] = $genreModel->select();
    }
}

?>