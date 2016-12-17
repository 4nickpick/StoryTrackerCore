<?
class RelationshipTypes
{
    const ALLY=1;
    const CONFLICT=2;
    const ROMANTIC=3;

    protected function lazyLoad()
    {
        $this->data=array
        (
            new RelationshipType(array
            (
                'id'=>self::ALLY,
                'name'=>'Ally',
                'color'=>'#0F0'
            )),
            new RelationshipType(array
            (
                'id'=>self::CONFLICT,
                'name'=>'Conflict',
                'color'=>'#F00'
            )),
            new RelationshipType(array
            (
                'id'=>self::ROMANTIC,
                'name'=>'Conflict',
                'color'=>'#0FF'
            ))
        );
    }
}

class RelationshipType
{
    public $color;
}
?>
