<?php
 
 require_once API_ROOT_PATH . "/models/basemodel.php";
 require_once API_ROOT_PATH . "/models/creditmodel.php";

class PersonModel extends BaseModel
{

    protected $equalFilters = array('person.id','first_name','last_name','movie_id','role_id','role');
    protected $likeFilters = array('first_name','last_name');
    protected $sorts = array('last_name','COUNT(movie_id)');
    protected $groups = array('person.id');

    public function count()
    {
        $query = 'SELECT COUNT(person.id) AS number FROM person';
        if ($this->filtersExistAny('movie_id','role_id','role') || $this->groupExists('movie_id')) $query.=" JOIN credit ON credit.person_id=person.id";
        if ($this->filterExists('role')) $query.=" JOIN role ON role.id=credit.role_id";
        $result = $this->mysqlSelect($query);
        return $result[0]['number'];
    }

    public function select()
    {
        $query = 'SELECT person.id, first_name, last_name FROM person';
        if ($this->filtersExistAny('movie_id','role_id') || $this->groupExists('movie_id')) $query.=" JOIN credit ON credit.person_id=person.id";
        return $this->mysqlSelect($query);
    }

    public function insert($data)
    {
        return $this->mysqlInsert("INSERT INTO person (first_name, last_name) VALUES (?,?)",
                                "ss",$data['first_name'],$data['last_name']);
    }

    public function update($data)
    {
        if (!$this->filterExists('person.id')) throw new Exception("Invalid data",400);
        if (!array_keys_exist($data,'first_name','last_name')) throw new Exception("Invalid data",400);
        return $this->mysqlUpdate("UPDATE person SET first_name=?, last_name=?",
                                    'ss',$data['first_name'],$data['last_name']);
    }

    public function delete()
    {
        if (!$this->filterExists('person.id')) throw new Exception("Invalid data",400);
        $model = new CreditModel;
        $model->addEqualFilter('person_id',$this->getEqualFilter('person.id'));
        $model->delete();
        return $this->mysqlDelete('DELETE FROM person');
    }

}
