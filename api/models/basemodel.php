<?php

class BaseModel
{
    protected $mysqli = null;

    protected $_equalFilters = array();
    protected $_rangeFilters = array();
    protected $_likeFilters = array();
    protected $_groups = array();
    protected $_sorts = array();
    protected $limit = null;
    protected $offset = null;
 
    public function __construct()
    {
        try
        {
            $this->mysqli = new mysqli();
            //$this->mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
            $this->mysqli->real_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME);
            $this->resetFilters();
            $this->resetSorts();
            $this->resetLimit();
        }
        catch (Exception $e) { throw New Exception($e->getMessage(), 500); }
    }

    public function getDataType( $var )
    {
        switch (gettype($var)) {
            case 'integer': return 'i';
            case 'string' : return 's';
            case 'double' : return 'd';
            default : return null;
        }
    }

    public function pushParams(&$params,...$param) {
        foreach ($param as $value) {
            $params[0] .= $this->getDataType($value);;
            array_push($params,$value);
        }
    }
 
    public function mysqlSelect($query = "" , ...$params)
    {
        [$query,$params] = $this->addFiltering($query,...$params);
        [$query,$params] = $this->addGrouping($query,...$params);
        [$query,$params] = $this->addSorting($query,...$params);
        [$query,$params] = $this->addLimiting($query,...$params);

        $stmt = $this->executeStatement($query, ...$params);
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
 
        return $result;
    }

    public function mysqlInsert($query = "" , ...$params)
    {
        $stmt = $this->executeStatement($query, ...$params);
        $result = $stmt->insert_id;
        $stmt->close();
 
        return $result;
    }
 
    public function mysqlUpdate($query = "" , ...$params)
    {
        [$query,$params] = $this->addFiltering($query,...$params);
        $stmt = $this->executeStatement($query, ...$params);
        $result = $stmt->affected_rows;
        $stmt->close();
 
        return $result;
    }

    public function mysqlDelete($query = "", ...$params)
    {
        [$query,$params] = $this->addFiltering($query,...$params);
        $stmt = $this->executeStatement($query, ...$params);
        $result = $stmt->affected_rows;
        $stmt->close();
 
        return $result;
    }

    private function executeStatement($query = "", ...$params)
    {
        $stmt = $this->mysqli->prepare($query);
        if ($stmt === false) { throw New Exception("Unable to do prepared statement: " . $query,500); }
 
        if (sizeof($params) > 1) { $stmt->bind_param(...$params); }
        $stmt->execute();
        return $stmt;
    }

    protected function addFiltering($query,...$params)
    {
        $filtering = false;
        $rangeFiltering = false;
        $likeFiltering = false;

        if (count($params) == 0) $params=array('');

        foreach ( $this->_equalFilters as $name => $values ) {
            foreach ($values as $value) {
                if (!$filtering) { $query .=  ' WHERE '; $filtering = true; }
                else { $query .= ' AND '; }
                $query .= "{$name} = ?";
                $this->pushParams($params,$value);
            }
        }

        foreach ( $this->_rangeFilters as $name => $range )
        {
            if (array_anykey_exists($range,'min','max')) {
                if (!$filtering) { $query .= ' WHERE '; $filtering = true; }
                else { $query .= ' AND '; }
                if (!array_key_exists('min',$range)) {
                    $query .= "{$name} <= ?";
                    $this->pushParams($params,$range['max']);
                } elseif (!array_key_exists('max',$range)) {
                    $query .= "{$name} >= ?";
                    $this->pushParams($params,$range['min']);
                } else {
                    $query .= "{$name} BETWEEN ? AND ?";
                    $this->pushParams($params,$range['min'],$range['max']);
                }
            }
        }

        foreach ( $this->_likeFilters as $name => $likes )
        {
            foreach ($likes as $like) {
                if (!$filtering) { $query .=  ' WHERE ('; $filtering = true; $likeFiltering = true; }
                elseif (!$likeFiltering) { $query .= ' AND ('; $likeFiltering = true; }
                else { $query .= ' OR '; }
                $query .= "{$name} LIKE ?";
                $this->pushparams($params,'%' . $like . '%');
            }
        }
        if ($likeFiltering) $query .= ")";

        return array( $query, $params );
    }

    protected function addGrouping($query,...$params)
    {
        $grouping = false;
        foreach ($this->_groups as $group)
        {
            if ($grouping) $query .= ", ";
            else { $query .= ' GROUP BY '; $grouping = true; }
            $query .= $group;
        }
        return array( $query, $params );
    }

    protected function addSorting($query,...$params)
    {
        $sorting = false;
        foreach ( $this->_sorts as $sort => $order )
            if (!is_null($order)) {
                if ($sorting) $query .= " AND ";
                else { $query .= ' ORDER BY '; $sorting = true; }
                $query .= "{$sort} {$order}";
            }

        return array( $query, $params );
    }

    protected function addLimiting($query,...$params)
    {
        if (!is_null($this->limit)) {
            $query .= " LIMIT ?";
            if (!is_null($this->offset)) {
                $query .= ",?";
                $this->pushParams($params,$this->offset);
            }
            $this->pushParams($params,$this->limit);
        }

        return array( $query, $params );
    }

    public function resetFilters()
    {
        $this->_equalFilters = array();
        if (isset($this->equalFilters)) foreach ($this->equalFilters as $filter) $this->_equalFilters[$filter] = array();
        $this->_rangeFilters = array();
        if (isset($this->rangeFilters)) foreach ($this->rangeFilters as $filter) $this->_rangeFilters[$filter] = array();
        $this->_likeFilters = array();
        if (isset($this->likeFilters)) foreach ($this->likeFilters as $filter) $this->_likeFilters[$filter] = array();
    }

    public function addEqualFilter($name,$value)
    {
        if (!is_null($value) && array_key_exists($name,$this->_equalFilters)) $this->_equalFilters[$name] = array_merge($this->_equalFilters[$name], [ $value ]);
    }

    public function getEqualFilter($name)
    {
        if (array_key_exists($name,$this->_equalFilters)) return $this->_equalFilters[$name][0];
    }

    public function addRangeFilter($name,$min,$max)
    {
        if (array_key_exists($name,$this->_rangeFilters)) {
            if (!is_null($min)) $this->_rangeFilters[$name]['min'] = $min;
            if (!is_null($max)) $this->_rangeFilters[$name]['max'] = $max;
        }
    }

    public function addLikeFilter($name,$value)
    {
        if (!is_null($value) && array_key_exists($name,$this->_likeFilters)) $this->_likeFilters[$name] = array_merge($this->_likeFilters[$name], [$value]);
    }

    public function filterExists($filter)
    {
        foreach (array_keys($this->_equalFilters) as $key)
            if ($key==$filter)
            {
                if (count($this->_equalFilters[$key])>0) return true;
                else return false;
            }
        foreach (array_keys($this->_rangeFilters) as $key)
            if ($key==$filter)
            {
                if (count($this->_rangeFilters[$key])>0) return true;
                else return false;
            }
        foreach (array_keys($this->_likeFilters) as $key)
            if ($key==$filter)
            {
                if (count($this->_likeFilters[$key])>0) return true;
                else return false;
            }

        return false;
    }

    public function filtersExist(...$filters)
    {
        foreach ($filters as $filter) if (!$this->filterExists($filter)) return false;
        return true;
    }

    public function filtersExistAny(...$filters)
    {
        foreach ($filters as $filter) if ($this->filterExists($filter)) return true;
        return false;
    }

    public function resetGroups()
    {
        $this->_groups = array();
    }

    public function addGroup($group)
    {
        if (isset($this->groups) && in_array($group,$this->groups)) $this->_groups = array_merge( $this->_groups, [ $group ]);
    }

    public function groupExists($group)
    {
        return in_array($group,$this->_groups);
    }

    public function groupsExist(...$groups)
    {
        foreach($groups as $group) if (in_array($group,$this->_groups)) return true;
        return false;
    }
    
    public function resetSorts()
    {
        $this->_sorts = array();
        if (isset($this->sorts)) foreach ($this->sorts as $sort) $this->_sorts = array_merge( $this->_sorts, [$sort => null]);
    }

    public function addSort($name,$value)
    {
        if ((strtolower($value) == 'asc' || strtolower($value) == 'desc') && array_key_exists($name,$this->_sorts)) $this->_sorts[$name]=$value;
    }

    public function sortExists($sort)
    {
        foreach (array_keys($this->_sorts) as $key)
        if ($key==$sort) return !is_null($this->_sorts[$key]);
        return false;
    }

    public function sortsExist(...$sorts)
    {
        foreach ($sorts as $sort) if (!$this->sortExists($sort)) return false;
        return true;
    }

    public function sortsExistAny(...$sorts)
    {
        foreach ($sorts as $sort) if ($this->sortExists($sort)) return true;
        return false;
    }

    public function resetLimit()
    {
        $this->limit = null;
        $this->offset = null;
    }

    public function setLimit($value) {
        $this->limit = $value;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function resetOffset()
    {
        $this->offset = null;
    }

    public function setOffset($value)
    {
        $this->offset = $value;
    }

    public function getOffset()
    {
        return $this->offset;
    }


}
