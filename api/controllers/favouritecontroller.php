<?php

require_once API_ROOT_PATH . "/controllers/basecontroller.php";
require_once API_ROOT_PATH . "/models/favouritemodel.php";
require_once API_ROOT_PATH . "/models/apikeymodel.php";

class FavouriteController extends BaseController
{
    //POST /favourites
    protected function create($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('favourites.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (isset($id['favourites']) && count($id)==1 && $id['favourites']==0)
        {
            $model = new FavouriteModel;
            $insert_id = $model->insert($data);
            $model->addEqualFilter('favourite.id',$insert_id);
            $results = $model->select();
            if (empty($results)) throw new Exception("Invalid ID",404);
            // add details here if needed
            return $results[0];
        }
        throw new Exception('Invalid URI',400);
    }
    
    // GET /favourites /favourites/{id}
    protected function read($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (isset($id['favourites']) && count($id) == 1)
        {
            if (!$keyModel->hasRights('favourites.read',$param['apikey'])) throw new Exception('API key invalid',401);

            $model = new FavouriteModel;
            if ($id['favourites'] == 0)
            {
                $count = $model->count();
                $this->addPagination($param,$model);
                $limit = $model->getLimit();
                $offset = $model->getOffset();

                $results = $model->select();
                $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                return $response;
            }
            else
            {
                $model->addEqualFilter('favourite.id',$id['favourites']);
                $results = $model->select();
                if (empty($results)) throw new Exception("Invalid ID",404);
                return $results[0];
            }
        }
        throw new Exception('Invalid URI',400);
    }

    // PUT /favourites/{id}
    protected function update($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (isset($id['favourites']) && count($id)==1 && $id['favourites']!=0)
        {
            if (!$keyModel->hasRights('favourites.write',$param['apikey'])) throw new Exception('API key invalid',401);

            $model = new FavouriteModel;
            $model->addEqualFilter('favourite.id',$id['favourites']);
            $affected_rows = $model->update($id,$data);
            return null;
        }
        throw new Exception('Invalid URI',400);
    }

    // DELETE /favourites/{id}
    protected function delete($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (isset($id['favourites']) && count($id)==1 && $id['favourites']!=0)
        {
            if (!$keyModel->hasRights('favourites.write',$param['apikey'])) throw new Exception('API key invalid',401);

            $model = new FavouriteModel;
            $model->addEqualFilter('favourite.id',$id['favourites']);
            $affected_rows = $model->delete($id);
            return null;
        }
        throw new Exception('Invalid URI',400);
    }
}

?>