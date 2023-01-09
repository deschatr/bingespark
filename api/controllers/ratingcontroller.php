<?php

require_once API_ROOT_PATH . "/controllers/basecontroller.php";
require_once API_ROOT_PATH . "/models/ratingmodel.php";
require_once API_ROOT_PATH . "/models/apikeymodel.php";

class RatingController extends BaseController
{
    //POST /ratings
    protected function create($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('ratings.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('ratings',$id) && count($id)==1 && $id['ratings']==0)
        {
            $model = new RatingModel;
            $insert_id = $model->insert($data);
            $model->addEqualFilter('rating.id',$insert_id);
            $results = $model->select();
            if (empty($results)) throw new Exception("Invalid ID",404);
            // add details here if needed
            return $results[0];
        }
        throw new Exception('Invalid URI',400);
    }
    
    // GET /ratings /ratings/{id}
    protected function read($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('ratings.read',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('ratings',$id) && count($id)==1)
        {
            $model = new RatingModel;
            if ($id['ratings'] == 0)
            {
                $count = $model->count();
                $this->addPagination($param,$model);
                $limit = $model->getLimit();
                $offset = $model->getOffset();

                $this->addSorting(null,null,$param,$model);
    
                $results = $model->select();
                $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                return $response;
            }
            else
            {
                $model->addEqualFilter('rating.id',$id['ratings']);
                $results = $model->select();
                if (empty($results)) throw new Exception("Invalid ID",404);
                return $results[0];    
            }
        }
        throw new Exception('Invalid URI',400);
    }

    // PUT /ratings/{id}
    protected function update($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('ratings.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('ratings',$id) && count($id)==1 && $id['ratings']!=0)
        {
            $model = new RatingModel;
            $model->addEqualFilter('rating.id',$id['ratings']);
            $affected_rows = $model->update($data);
            $results = $model->select();
            return $results[0];
        }
        throw new Exception('Invalid URI',400);
    }
 
    // DELETE /ratings/{id}
    protected function delete($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('ratings.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('ratings',$id) && count($id)==1 && $id['ratings']!=0)
        {
            $model = new RatingModel;
            $model->addEqualFilter('rating.id',$id['ratings']);
            $affected_rows = $model->delete();
            return null;
        }
        throw new Exception('Invalid URI',400);
    }
}