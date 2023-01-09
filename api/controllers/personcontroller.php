<?php

require_once API_ROOT_PATH . "/controllers/basecontroller.php";
require_once API_ROOT_PATH . "/models/moviemodel.php";
require_once API_ROOT_PATH . "/models/personmodel.php";
require_once API_ROOT_PATH . "/models/creditmodel.php";
require_once API_ROOT_PATH . "/models/rolemodel.php";
require_once API_ROOT_PATH . "/models/apikeymodel.php";

class PersonController extends BaseController
{
    // POST /persons /persons/{id}/credits
    protected function create($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('persons.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('persons',$id))
        {
            if (count($id)==1 && $id['persons']==0)
            {
                $model = new PersonModel;

                $insert_id = $model->insert($data);
                $model->addEqualFilter('person.id',$insert_id);
                $results = $model->select();
                if (empty($results)) throw new Exception("Invalid ID",404);
                // add details here if needed
                return $results[0];
            }
            elseif (count($id)==2 && array_key_exists('credits',$id) && $id['credits']==0)
            {
                if (!$keyModel->hasRights('credits.write',$param['apikey'])) throw new Exception('API key invalid',401);

                $model = new CreditModel;
                $data += [ 'person_id'=>$id['persons']];
                $model->addEqualFilter('credit.id',$insert_id);
                $results = $model->select();
                if (empty($results)) throw new Exception("Invalid ID",404);
                // add details here if needed
                return $results[0];
            }
        }
        throw new Exception('Invalid URI',400);
    }
    
    // GET /persons /persons/{id} /persons/{id}/credits
    protected function read($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (array_key_exists('persons',$id))
        {
            if (count($id)==1)
            {
                if (!$keyModel->hasRights('persons.read',$param['apikey'])) throw new Exception('API key invalid',401);
        
                $model = new PersonModel;
                
                if ($id['persons'] == 0)
                {
                    $this->addEqualFiltering('last_name',$param,$model);
                    $this->addEqualFiltering('first_name',$param,$model);
                    $this->addEqualFiltering('movie_id',$param,$model);
                    $this->addEqualFiltering('role_id',$param,$model);
                    $this->addEqualFiltering('role',$param,$model);
    
                    $this->addSorting('last_name','asc',$param,$model);

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
                    $model->addEqualFilter('person.id',$id['persons']);
                    $results = $model->select();
                    if (empty($results)) throw new Exception("Invalid ID",404);
                    return $results[0];
                }
                
            }
            elseif (count($id)==2)
            {
                if (array_key_exists('credits',$id) && $id['credits']==0)
                {
                    if (!$keyModel->hasRights('credits.read',$param['apikey'])) throw new Exception('API key invalid',401);
            
                    $model = new CreditModel;
                    $model->addEqualFilter('person_id',$id['persons']);

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
            }
            throw new Exception('Invalid URI',400); }
    }
 
    // PUT /persons /persons/{id}
    protected function update($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('persons.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (isset($id['persons']) && count($id)==1 && $id['persons']!=0)
        {
            $model = new PersonModel;
            $model->addEqualFilter('person.id',$id['persons']);
            $affected_rows = $model->update($data);
            $results = $model->select();
            return $results[0];
        }
        throw new Exception('Invalid URI',400);
    }
 
    // DELETE /persons /persons/{id}
    protected function delete($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('persons.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('persons',$id) && count($id)==1 && $id['persons']!=0)
        {
            $model = new PersonModel;
            $model->addEqualFilter('person.id',$id['persons']);
            $affected_rows = $model->delete();
            return null;
        }

        throw new Exception('Invalid URI',400);
    }

    // GET /persons/search
    protected function search($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('persons.read',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('query',$param))
        {
            $model = new PersonModel;

            $this->addEqualFiltering('last_name',$param,$model);
            $this->addEqualFiltering('first_name',$param,$model);
            $this->addEqualFiltering('movie_id',$param,$model);
            $this->addEqualFiltering('role_id',$param,$model);
            $this->addEqualFiltering('role',$param,$model);
    
            $model->addLikeFilter('first_name',$param['query']);
            $model->addLikeFilter('last_name',$param['query']);

            $this->addSorting('last_name','asc',$param,$model);

            $count = $model->count();
            $this->addPagination($param,$model);
            $limit = $model->getLimit();
            $offset = $model->getOffset();

            $results = $model->select();
            $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
            return $response;
        }
        throw new Exception('Invalid URI',400);
    }

    protected function addCreditDetails(&$credit)
    {
        $movieModel = new MovieModel;
        $movieModel->addEqualFilter('movie.id',$credit['movie_id']);
        $movie=$movieModel->select()[0];
        $credit['movie_title']=$movie['title'];

        $roleModel = new RoleModel;
        $roleModel->addEqualFilter('role.id',$credit['role_id']);
        $role=$roleModel->select()[0];
        $credit['role']=$role['role'];

        unset($credit['person_id']);
    }

}

?>