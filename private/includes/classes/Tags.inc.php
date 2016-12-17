<?php
class Tags
{
    const CHARACTER     = 1;
    const SETTING       = 2;
    const PLOT_EVENT    = 3;

    static $found_rows;

    public static function loadAll()
    {
        global $currentStory;
        $series_id = $currentStory->get_series()->get_id();

        $character_tags = self::loadCharacterTags($series_id);
        $setting_tags = self::loadSettingTags($series_id);
        $plot_event_tags = self::loadPlotEventTags($series_id);

        self::$found_rows = count($character_tags) + count($setting_tags) + count($plot_event_tags);

        return array_merge($character_tags, $setting_tags, $plot_event_tags);
    }

    public static function loadCharacterTags($series_id)
    {
        $sql_calc_found_rows='';

        $sql=
            'SELECT DISTINCT '.$sql_calc_found_rows.'
				characters.id AS object_id,
				full_name AS object_name,
				:object_type AS object_type
			FROM
				pictures_to_characters
			LEFT JOIN characters ON pictures_to_characters.characters_id=characters.id
			WHERE characters.series_id=:series_id
			ORDER BY characters.full_name';

        $data=array();
        $values=array(
            ':series_id'=>$series_id,
            ':object_type'=>self::CHARACTER
        );
        if($rs = pdologged_preparedQuery($sql, $values))
        {
            if(!empty($limit))
            {
                $rs_count = pdologged_query('SELECT FOUND_ROWS()');
                if($row = $rs_count->fetch(PDO::FETCH_NUM))
                    self::$found_rows+=$row[0];
            }
            else
                self::$found_rows += $rs->rowCount();

            while($row = $rs->fetch(PDO::FETCH_ASSOC))
            {
                $tag = new Tag(array
                (
                    'object_id'=>$row['object_id'],
                    'object_name'=>$row['object_name'],
                    'object_type'=>$row['object_type']
                ));

                $data[] = $tag;
            }

            return $data;
        }
        return false;
    }

    public static function loadSettingTags($series_id)
    {
        $sql_calc_found_rows='';

        $sql=
            'SELECT DISTINCT '.$sql_calc_found_rows.'
				settings.id AS object_id,
				full_name AS object_name,
				:object_type AS object_type
			FROM
				pictures_to_settings
			LEFT JOIN settings ON pictures_to_settings.settings_id=settings.id
			WHERE settings.series_id=:series_id
			ORDER BY settings.full_name';

        $data=array();
        $values=array(
            ':series_id'=>$series_id,
            ':object_type'=>self::SETTING
        );
        if($rs = pdologged_preparedQuery($sql, $values))
        {
            if(!empty($limit))
            {
                $rs_count = pdologged_query('SELECT FOUND_ROWS()');
                if($row = $rs_count->fetch(PDO::FETCH_NUM))
                    self::$found_rows+=$row[0];
            }
            else
                self::$found_rows += $rs->rowCount();

            while($row = $rs->fetch(PDO::FETCH_ASSOC))
            {
                $tag = new Tag(array
                (
                    'object_id'=>$row['object_id'],
                    'object_name'=>$row['object_name'],
                    'object_type'=>$row['object_type']
                ));

                $data[] = $tag;
            }

            return $data;
        }
        return false;
    }

    public static function loadPlotEventTags($series_id)
    {
        $sql_calc_found_rows='';

        $sql=
            'SELECT DISTINCT '.$sql_calc_found_rows.'
				plot_events.id AS object_id,
				event AS object_name,
				:object_type AS object_type
			FROM
				pictures_to_plot_events
			LEFT JOIN plot_events ON pictures_to_plot_events.plot_events_id=plot_events.id
			WHERE plot_events.series_id=:series_id
			ORDER BY plot_events.series_id';

        $data=array();
        $values=array(
            ':series_id'=>$series_id,
            ':object_type'=>self::SETTING
        );
        if($rs = pdologged_preparedQuery($sql, $values))
        {
            if(!empty($limit))
            {
                $rs_count = pdologged_query('SELECT FOUND_ROWS()');
                if($row = $rs_count->fetch(PDO::FETCH_NUM))
                    self::$found_rows+=$row[0];
            }
            else
                self::$found_rows += $rs->rowCount();

            while($row = $rs->fetch(PDO::FETCH_ASSOC))
            {
                $tag = new Tag(array
                (
                    'object_id'=>$row['object_id'],
                    'object_name'=>$row['object_name'],
                    'object_type'=>$row['object_type']
                ));

                $data[] = $tag;
            }

            return $data;
        }
        return false;
    }
}

class Tag {
    public $object_id, $object_name, $object_type;

    public function __construct($properties)
    {
        if( is_array($properties) )
        {
            foreach($properties as $property=>$value)
            {
                if(property_exists($this, $property))
                {
                    $this->{"$property"}=$value;
                }
            }
        }
    }

    public function get_object_id()
    {
        return $this->object_id;
    }

    public function get_object_name()
    {
        return $this->object_name;
    }

    public function get_object_type()
    {
        return $this->object_type;
    }
}
?>