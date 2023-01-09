<?php

require_once API_ROOT_PATH . "/controllers/basecontroller.php";
require_once API_ROOT_PATH . "/models/rolemodel.php";
require_once API_ROOT_PATH . "/models/apikeymodel.php";

class RoleController extends BaseController
{
    // POST /roles
    protected function create($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('roles.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('roles',$id) && count($id)==1)
        {
            $model = new RoleModel;
            $insert_id = $model->insert($data);
            $model->addEqualFilter('role.id',$insert_id);
            $results = $model->select();
            if (empty($results)) throw new Exception("Invalid ID",404);
            // add details here if needed
            return $results[0];
        }
        throw new Exception('Invalid URI',400);
    }
    
    // GET /roles /roles/{id}
    protected function read($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('roles.read',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('roles',$id) && count($id)==1)
        {
            $model = new RoleModel;
            if ($id['roles']==0)
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
                $model->addEqualFilter('role.id',$id['roles']);
                $results = $model->select();
                if (empty($results)) throw new Exception("Invalid ID",404);
                return $results[0];
            }
        }
        throw new Exception('Invalid URI',400);
    }
 
    // UPDATE /roles/{id}
    protected function update($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('roles.write',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('roles',$id) && count($id)==1 && $id['roles']!=0)
        {
            $model = new RoleModel;
            $model->addEqualFilter('role.id',$id['roles']);
            $affected_rows = $model->update($data);
            $results = $model->select();
            return $results[0];
        }
        throw new Exception('Invalid URI',400);
    }
 
    // DELETE /roles/{id}
    protected function delete($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('roles.write',$param['apikey'])) throw new Exception('API key invalid',401);
        
        if (array_key_exists('roles',$id) && count($id)==1 && $id['roles']!=0)
        {
            $model = new RoleModel;
            $model->addEqualFilter('role.id',$id['roles']);
            $affected_rows = $model->delete();
            return null;
        }
        throw new Exception('Invalid URI',400);
    }
 }

?>