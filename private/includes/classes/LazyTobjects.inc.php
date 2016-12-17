<?
abstract class LazyTobjects
{
    protected $data;

    public function loadAll()
    {
        if(!isset($this->data))
            $this->lazyLoad();

        $data = array();
        foreach($this->data as $dat)
        {
            if(!$dat->hidden)
                $data[] = $dat;
        }
        return $data;
    }

    public function loadById($id)
    {
        if(!isset($this->data))
            $this->lazyLoad();

        for($i=0;$i<count($this->data);$i++)
        {
            if($this->data[$i]->id==$id)
                return $this->data[$i];
        }

        return false;
    }

    protected abstract function lazyLoad();
}

class LazyTobject
{
    public $id, $name;

    function __construct($properties)
    {
        foreach($properties as $property=>$value)
        {
            if(property_exists($this, $property))
                $this->{"$property"}=$value;
        }
    }

    public function get_id()
    {
        return $this->id;
    }

    public function get_name()
    {
        return $this->name;
    }
}
?>