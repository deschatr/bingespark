<?php

require_once API_ROOT_PATH . "/controllers/basecontroller.php";
require_once API_ROOT_PATH . "/models/apikeymodel.php";
require_once API_ROOT_PATH . "/models/apiscopemodel.php";
require_once API_ROOT_PATH . "/models/apikeyscopemodel.php";

class APIKeyController extends BaseController
{
    // POST /apikeys
    protected function create($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (isset($id['apikeys']))
        {
            if (!$keyModel->hasRights('apikeys.write',$param['apikey'])) throw new Exception('API key invalid',401);

            if (count($id)==1 && $id['apikeys']==0)
            {
                $model = new APIKeyModel;
    
                $apikey = $model->generate();
                $data += array( 'apikey' => $apikey );
                $insert_id = $model->insert($data);

                $model->addEqualFilter('api_key.id',$insert_id);
                $results = $model->select();
                if (empty($results)) throw new Exception("Invalid ID",404);

                $results[0]['apikey'] = $apikey; // the only time this will be communicated!

                return $results[0];
            }
            elseif (count($id)==2 && $id['apikeys']!=0 && isset($id['apiscopes']) && $id['apiscopes']==0)
            {
                $model = new APIKeyScopeModel;
                
                if (isset($data['scope_id']) || isset($data['scope']))
                {
                    $data['key_id']=$id['apikeys'];
                    if (!isset($data['scope_id']))
                    {
                        $scopeModel = new APIScopeModel;
                        $scopeModel->addEqualFilter('scope',$data['scope']);
                        $result = $scopeModel->select();
                        $data['scope_id'] = $result[0]['id'];
                        unset($data['scope']);
                    }
                    $insert_id = $model->insert($data);

                    $model->addEqualFilter('api_key_scope.id',$insert_id);
                    $results = $model->select();

                    if (empty($results)) throw new Exception("Invalid ID",404);

                    return $results[0];
                }
            }
        }
        throw new Exception('Invalid URI',400);
    }

    protected function read($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
    
        if (isset($id['apikeys']))
        {
            if (!$keyModel->hasRights('apikeys.read',$param['apikey'])) throw new Exception('API key invalid',401);

            if (count($id)==1)
            {
                $model = new APIKeyModel;
                if ($id['apikeys']==0)
                {
                    $this->addEqualFiltering('user_id',$param,$model);

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
                    $model->addEqualFilter('api_key.id',$id['apikeys']);
                    $results = $model->select();
                    if (empty($results)) throw new Exception("Invalid ID",404);
                    return $results[0];
                }
            }
            elseif (count($id)==2 && $id['apikeys']!=0)
            {
                if (array_key_exists('apiscopes',$id) && $id['apiscopes']==0)
                {
                    $model = new APIKeyScopeModel;
                    $model->addEqualFilter('key_id',$id['apikeys']);
                    $this->addEqualFiltering('user_id',$param,$model);

                    $count = $model->count();
                    $this->addPagination($param,$model);
                    $limit = $model->getLimit();
                    $offset = $model->getOffset();

                    $this->addSorting(null,null,$param,$model);

                    $results = $model->select();
                    $response = [ 'page' => intdiv($offset,$limit)+1, 'total_pages'=> intval(ceil($count/$limit)), 'total_results' => $count, 'results'=>$results];
                    return $response;
                }
            }
        }
        throw new Exception('Invalid URI',400);
    }

    // GET /apikeys/search
    protected function search($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);
        if (!$keyModel->hasRights('apikeys.read',$param['apikey'])) throw new Exception('API key invalid',401);

        if (array_key_exists('query',$param))
        {
            $results = $keyModel->select();
            foreach($results as $result) if (password_verify($param['query'],$result['encrypted_key'])) return $result;
        }
        else
        {
            throw new Exception('No query string provided',400);
        }
        throw new Exception('API key not found',404);
    }

    // DELETE /apikeys/{id}
    protected function delete($id,$param,$data)
    {
        $keyModel = new APIKeyModel;
        if (!array_key_exists('apikey',$param)) throw new Exception('API key missing',401);

        if (isset($id['apikeys']) && $id['apikeys']!=0)
        {
            if (!$keyModel->hasRights('apikeys.write',$param['apikey'])) throw new Exception('API key invalid',401);
            
            if (count($id)==1)
            {
                $model = new APIKeyModel;
                $model->addEqualFilter('api_key.id',$id['apikeys']);
                $affected_rows = $model->delete();
                return null;
            }
            elseif (count($id)==2 && isset($id['apiscopes']) && $id['apiscopes']!=0)
            {
                $model = new APIKeyScopeModel;
                $model->addEqualFilter('key_id',$id['apikeys']);
                $model->addEqualFilter('scope_id',$id['apiscopes']);
                $affected_rows = $model->delete();
                return null;
            }
        }
        throw new Exception('Invalid URI',400);
    }

}

?>