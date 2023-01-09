<?php

require_once API_ROOT_PATH . "/controllers/basecontroller.php";
require_once API_ROOT_PATH . "/models/creditmodel.php";
require_once API_ROOT_PATH . "/models/moviemodel.php";
require_once API_ROOT_PATH . "/models/personmodel.php";
require_once API_ROOT_PATH . "/models/rolemodel.php";
require_once API_ROOT_PATH . "/models/apikeymodel.php";

class CreditController extends BaseController
{
    //POST /credits
    protected function create($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
    
        if (array_key_exists('credits',$id) && count($id)==1 && $id['credits']==0)
        {
            if (!$keyModel->hasRights('credits.write',$param['apikey'])) throw new Exception('API key invalid',401);

            $model = new CreditModel;
            $insert_id = $model->insert($data);
            $model->addEqualFilter('credit.id',$insert_id);
            $results = $model->select();
            if (empty($results)) throw new Exception("Invalid ID",404);
            // add details here if needed
            return $results[0];
        }
        throw new Exception('Invalid URI',400);
    }
        
    // GET /credits /credits/{id}
    protected function read($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
    
        if (isset($id['credits']) && count($id)==1)
        {
            if (!$keyModel->hasRights('credits.read',$param['apikey'])) throw new Exception('API key invalid',401);

            $model = new CreditModel;
            if ($id['credits']==0)
            {
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
                {
                    $this->addCreditDetails($results[$creditIndex]);
                }
                
                $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                return $response;
            }
            else
            {
                $model->addEqualFilter('credit.id',$id['credits']);
                $results = $model->select();
                if (empty($results)) throw new Exception("Invalid ID",404);
                return $results[0];
            }
        }
        throw new Exception('Invalid URI',400);
    }

    // PUT /credits/{id}
    protected function update($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (isset($id['credits']) && count($id)==1 && $id['credits']!=0)
        {
            if (!$keyModel->hasRights('credits.write',$param['apikey'])) throw new Exception('API key invalid',401);

            $model = new CreditModel;
            $model->addEqualFilter('credit.id',$id['credits']);
            $affected_rows = $model->update($id,$data);
            return $model->select($id['credits']);
        }
        throw new Exception('Invalid URI',400);
    }
 
    // DELETE /credits/{id}
    protected function delete($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (isset($id['credits']) && count($id)==1 && $id['credits']!=0)
        {
            if (!$keyModel->hasRights('credits.write',$param['apikey'])) throw new Exception('API key invalid',401);
            
            $model = new CreditModel;
            $model->addEqualFilter('credit.id',$id['credits']);
            $affected_rows = $model->delete($id);
            return null;
        }
        throw new Exception('Invalid URI',400);
    }

    protected function addCreditDetails(&$credit)
    {
        $movieModel = new MovieModel();
        $personModel = new PersonModel();
        $roleModel = new RoleModel();

        $movieModel->addEqualFilter('movie.id',$credit['movie_id']);
        $movie = $movieModel->select()[0];

        $credit += [ 'movie_title' => $movie['title'] ];
        $credit += [ 'movie_release_year' => $movie['release_year'] ];

        $personModel->addEqualFilter('person.id',$credit['person_id']);
        $person = $personModel->select()[0];
        $credit += [ 'first_name' => $person['first_name'] ];
        $credit += [ 'last_name' => $person['last_name'] ];

        $roleModel->addEqualFilter('role.id',$credit['role_id']);
        $role = $roleModel->select()[0];
        $credit += [ 'role' => $role['role'] ];
    }
}