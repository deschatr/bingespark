<?php

require_once API_ROOT_PATH . "/controllers/basecontroller.php";
require_once API_ROOT_PATH . "/models/usergroupmodel.php";
require_once API_ROOT_PATH . "/models/usermodel.php";
require_once API_ROOT_PATH . "/models/apikeymodel.php";

class UsergroupController extends BaseController
{
    // POST /usergroups /usergroups/{id}/users
    
    // GET /users /usergroups/{id} /usergroups/{id}/users
    protected function read($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (isset($id['usergroups']))
        {
            if (!$keyModel->hasRights('usergroups.read',$param['apikey'])) throw new Exception('API key invalid',401);

            if (count($id)==1)
            {
                $model = new UsergroupModel;
                if ($id['usergroups']==0)
                {
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
                    $model->addEqualFilter('usergroup.id',$id['usergroups']);
                    $results = $model->select();
                    if (empty($results)) throw new Exception("Invalid ID",404);
                    return $results[0];
                }
            } elseif (count($id)==2 && $id['usergroups']!=0 && array_key_exists('users',$id) && $id['users']==0)
            {
                $keyModel = new APIKeyModel;
                if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
                if (!$keyModel->hasRights('users.read',$param['apikey'])) throw new Exception('API key invalid',401);
        
                $model = new UserModel;
                $model->addEqualFilter('usergroup_id',$id['usergroups']);
                $count = $model->count();
                $this->addPagination($param,$model);
                $limit = $model->getLimit();
                $offset = $model->getOffset();

                $results = $model->select();
                $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                return $response;
            }
        }
        throw new Exception('Invalid URI',400);
    }
 
    // UPDATE /usergroups/{id}
 
    // DELETE /usergroups/{id}
 }

?>