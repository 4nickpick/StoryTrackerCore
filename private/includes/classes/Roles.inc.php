<?
class Roles extends LazyTobjects
{
    const ANONYMOUS=0;
    const DEV=1;
    const ADMIN=2;
    const USER=3;

    protected function lazyLoad()
    {
        $this->data=array
        (
            new Role(array
            (
                'id'=>self::ANONYMOUS,
                'name'=>'Anonymous User',
                'hidden'=>true
            )),
            new Role(array
            (
                'id'=>self::DEV,
                'name'=>'Developer',
                'hidden'=>true
            )),
            new Role(array
            (
                'id'=>self::ADMIN,
                'name'=>'Administrator',
                'hidden'=>false
            )),
            new Role(array
            (
                'id'=>self::USER,
                'name'=>'User',
                'hidden'=>false
            ))
        );
    }
}

class Role extends LazyTobject
{
    public $hidden;

    public function get_role()
    {
        return $this->name;
    }

    public function is_hidden()
    {
        return (bool)($this->hidden);
    }

    public function is_developer()
    {
        return $this->get_developer();
    }

    public function get_developer()
    {
        return (bool)($this->id == Roles::DEV);
    }
}
?>