<?php

class BaseController
{
    public function processRequest()
    {
        // getting request method: GET/POST/PUT/DELETE
        $requestMethod = strtoupper($_SERVER["REQUEST_METHOD"]);

        // getting URI segments
        $uriSegments = $this->getUriSegments();

        // extract IDs or function from URI
        $id = array();
        if (sizeof($uriSegments) == 2 && intval($uriSegments[1]) == 0)
        {
            $id[strtolower($uriSegments[0])]=0;
            // extract function from URI
            $function = $uriSegments[1];
        }
        else
        {
            // extracting IDs from URI
            for ($pair=0; $pair<intdiv(count($uriSegments)+1,2); $pair++)
            {   
                if (isset($uriSegments[$pair*2+1]))
                {
                    $value = intval($uriSegments[$pair*2+1]);
                    if ($value == 0) $this->sendError(400,'Invalid URI');
                    $id[strtolower($uriSegments[$pair*2])] = intval($uriSegments[$pair*2+1]);
                }
                else
                {
                    $id[strtolower($uriSegments[$pair*2])] = 0;
                }
            }
        }

        // getting query parameters
        $param = $this->getQueryParams();

        // getting data (POST)
        $data = json_decode(file_get_contents('php://input'),true);

        try
        {
            switch ($requestMethod)
            {
                case 'GET':
                    if (isset($function))
                    { $this->sendOutput(call_user_func(array($this,$function),$id,$param,$data), array('HTTP/1.1 200 OK')); }
                    else { $this->sendOutput($this->read($id,$param,$data), array('HTTP/1.1 200 OK')); }
                    break;
                case 'POST':
                    if (isset($function))
                    { $this->sendOutput(call_user_func(array($this,$function),$id,$param,$data), array('HTTP/1.1 200 OK')); }
                    else { $this->sendOutput($this->create($id,$param,$data), array('HTTP/1.1 201 Created')); }
                    break;
                case 'PUT':
                    $this->sendOutput($this->update($id,$param,$data), array('HTTP/1.1 200 OK'));
                    break;
                case 'DELETE':
                    $this->sendOutput($this->delete($id,$param,$data), array('HTTP/1.1 200 OK'));
                    break;
                default:
                    throw new Exception("HTTP method {$requestMethod} not supported",405);
            }
        } catch (Exception $e) {
                $this->sendError($e->getCode(),$e->getMessage());
        }
    }

    public function __call($name, $arguments)
    {
        $this->sendError(400,"Method {$name} not supported");
    }
    
    protected function getUriSegments()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uriSegments = explode( '/', $uri );
        array_splice($uriSegments,0,URI_DROP);
        if (end($uriSegments) == '') array_pop($uriSegments);
 
        return $uriSegments;
    }

    protected function getQueryParams()
    {
        parse_str($_SERVER['QUERY_STRING'],$query);
        $params = array();
        foreach ($query as $name => $value)
        if (is_numeric($value))
        {
            if (intval($value)==0)
            {
                if (floatval($value)==0) $params[$name] = 0;
                else $params[$name] = floatval($value);
            }
            else
            {
                $params[$name] = intval($value);
            }
        }
        else
        {
            $params[$name] = $value;
        }
        return $params;
    }

    protected function sendOutput($response, $httpHeaders=array())
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if (is_array($httpHeaders) && count($httpHeaders)) {
            foreach ($httpHeaders as $httpHeader) { header($httpHeader); }
        }
 
        echo json_encode($response);
        exit;
    }

    protected function sendError($code, $message="No message available")
    {
        $response['code'] = $code;
        switch ($code) {
            case 400:
                $response['error'] = 'Bad Request';
                break;
            case 401:
                $response['error'] = 'Unauthorized';
                break;
            case 403:
                $response['error'] = 'Forbidden';
                break;
            case 404:
                $response['error'] = 'Not Found';
                break;
            case 500:
                $response['error'] = 'Internal Server Error';
                break;
            default:
                $response['error'] = 'Unknown Error';
        }
        $response['message'] = $message;
        $httpHeaders = array('Content-Type: application/json', "HTTP/1.1 {$response['code']} {$response['error']}");
        $this->sendOutput($response,$httpHeaders);
    }

    public function addPagination($param,$model)
    {
        if (isset($param['pagesize'])) $pageSize = $param['pagesize'];
        else $pageSize = DEFAULT_PAGE_SIZE;
        $model->setLimit($pageSize);

        if (isset($param['page']) && $param['page']>0) $model->setOffset($pageSize*($param['page']-1));
        else $model->setOffset(0);
    }

    public function addEqualFiltering($pname,$param,$model)
    {
        if (isset($param[$pname])) $model->addEqualFilter($pname,$param[$pname]);
    }

    public function addRangeFiltering($pname,$param,$model)
    {
        if (isset($param[$pname . '_min']))
            {
                if (isset($param[$pname . '_max'])) $model->addRangeFilter($pname,$param[$pname . '_min'],$param[$pname . '_max']);
                else $model->addRangeFilter($pname,$param[$pname . '_min'],null);
            }
            elseif (isset($param[$pname . '_max'])) $model->addRangeFilter($pname,null,$param[$pname . '_max']);
    }

    public function addGrouping($pname,$param,$model)
    {
        if (isset($param['group']) && $param['group']==$pname) $model->addGroup($pname);
    }

    public function addSorting($default_sort,$default_order,$param,$model)
    {
        if (isset($param['sort']))
        {
            $sort = $param['sort'];
            if (isset($param['order'])) $order = $param['order'];
            else $order='asc';
        }
        else
        {
            $sort = $default_sort;
            $order = $default_order;
        }
        if (!is_null($sort) && !is_null($order)) $model->addSort($sort,$order);
    }
}