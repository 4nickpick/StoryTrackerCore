<?
/*
* Default Controller of Tabmin's MVC approach is in this file.
* 
* All Controllers must extend one of the default controllers. 
*
*/
abstract class Controller
{
	protected $tobject;
	protected $properties;
	protected $required;
	protected $caption = 'record';
	protected $json=array('success'=>false);
	protected $user, $module;
    protected $checkXSRF = true;
	protected $consumeXSRF = false;
	protected $checkPermissions = true;
	
	function __construct($properties, $user, $module)
	{
		$this->properties = $properties;
		$this->user = $user;
		$this->loadTobject($properties);
		$this->module = $module;
	}
	
	protected abstract function loadTobject($properties);

	
	public function get_json()
	{			
		$this->json['alerts'] = AlertSet::$alerts;
		return json_encode($this->json);
	}

	public function get_tobject()
	{
		return $this->tobject;
	}

	public function setTobject($tobject)
	{
		$this->tobject = $tobject;
	}

	/*
	* $req_array must be an associateve array(key->value pairs) of columns names(fields) 
	* and captions descibing the field.
	* ie: $req_array = array('users.first_name'=>'First Name', 'users.last_name'=>'Last Name');
	*/
	public function setAllRequired($req_array)
	{
		$this->required = $req_array;
	}

    public function setCheckXSRF($check)
    {
        $this->checkXSRF = $check;
    }
	
	public function setConsumeXSRF($consume)
	{
		$this->consumeXSRF = $consume;
	}
	
	public function setCheckPermissions($check)
	{
		$this->checkPermissions = $check;
	}
	
	public function addProperty($property, $value, $reloadTobject = false)
	{
		$this->properties[$property] = $value;
		if ($reloadTobject)
			$this->loadTobject($this->properties);	
	}
	public function addProperties($properties, $value, $reloadTobject = false)
	{
		foreach($properties as $property)
			$this->properties[$property] = $value;
		if ($reloadTobject)
			$this->loadTobject($this->properties);	
	}
	
	public function removeProperty($property, $reloadTobject = false)
	{
		if (isset($this->properties[$property]))
		{			
			unset($this->properties[$property]);
		}
		if ($reloadTobject)
			$this->loadTobject($this->properties);	
	}
	
	public function removeProperties($properties, $reloadTobject = false)
	{
		foreach($properties as $property)
		{
			if (isset($this->properties[$property]))
			{			
				unset($this->properties[$property]);
			}
		}
		if ($reloadTobject)
			$this->loadTobject($this->properties);	
	}
	
	public function addRequired($property, $caption)
	{
		$this->required[$property] = $caption;
	}
	
	public function removeRequired($property)
	{
		if (isset($this->required[$property]))
			unset($this->required[$property]);
	}
	
	public function checkEmpty()
	{		
		if( count($this->required) > 0 )
        {
            foreach($this->required as $req=>$caption)
            {
                if (empty($this->properties[$req]))
                {
                    AlertSet::addValidation($caption . ' cannot be blank.');
                }
            }
        }
		return AlertSet::$success;
	}
	
	public function validateFields()
	{
		if ($this->checkEmpty())
		{
			if(!$this->checkXSRF || XSRF::valid($this->consumeXSRF))
			{
				if($this->tobject instanceof Tobject) 
				{
					return true;
				}
				else
					trigger_error('Expecting Tobject, other object is present',  E_USER_ERROR);
			}
			else
				AlertSet::addError(XSRF::GENERIC_ERROR);			
		}
		return false;
	}

	public function add()
	{
		$result = false;
		if ($this->validateFields())
		{
			if((!$this->checkPermissions) || $this->user->hasPermission($this->module, 'add'))
			{				
				$result = $this->tobject->add();
				if ($result)
				{
					$this->json['success']=true;
                    $this->json['new_ids'][]=$this->tobject->get_id();
					AlertSet::addSuccess('The '.$this->caption.' was added successfully.');
				}
				else
					AlertSet::addError('The '.$this->caption.' was not created.');
			}
			else
				AlertSet::addError('You do not have permission to add '.$this->caption.'s');
		}
		return $result;
	}
	
	public function update()
	{
		$result = false;
		if ($this->validateFields())
		{			
			if((!$this->checkPermissions) || $this->user->hasPermission($this->module, 'edit'))
			{					
				$result = $this->tobject->update();
				if ($result)
				{
					$this->json['success']=true;
					AlertSet::addSuccess('The '.$this->caption.' was updated successfully.');
				}
				else
					AlertSet::addError('The '.$this->caption.' was not updated.');					
			}
			else
				AlertSet::addError('You do not have permission to update '.$this->caption.'s');
		}
		return $result;
	}
	
	public function delete()
	{
		$result = false;
		if($this->tobject instanceof Tobject) 
		{
			if(!$this->checkXSRF || XSRF::valid($this->consumeXSRF))
			{
				if((!$this->checkPermissions) || $this->user->hasPermission($this->module, 'delete'))
				{
					$result = $this->tobject->delete();
					if ($result)
					{
						$this->json['success']=true;
						AlertSet::addSuccess('The '.$this->caption.' was deleted successfully.');
					}
					else
						AlertSet::addError('The '.$this->caption.' was not deleted.');
				}
				else
					AlertSet::addError('You do not have permission to delete '.$this->caption.'s');
			}
			else
				AlertSet::addError(XSRF::GENERIC_ERROR);
		}
		else
        {
            trigger_error('Expecting Tobject, other object is present',  E_USER_ERROR);
        }
		return $result;
	}
	
	/*sorting functions*/
	public function listUpdatePriorities($manager, $data, $moved_element)
	{		
		if( !($manager instanceof Tobjects) )
			return false;
		
		if( $manager->updatePriorities($data, $moved_element) )
		{
			$this->json['success'] = true;
			AlertSet::addSuccess('Priorities updated.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to swap your objects. If there is a priority conflict, this should trigger an automatic updater. ');
		}

        return true;
	}


    protected function processPicture($users_id=0)
    {
        if(!empty($this->properties['picture_file']['size']))
        {
            ini_set('memory_limit', '256M');
            $picture=new Picture(array
            (
                'caption'=>@$this->properties['caption'],
                'users_id'=>$users_id
            ));

            if($picture->add())
            {
                $this->tobject->set_id($picture->get_id());
                $this->saveImage($this->properties['picture_file']['type'][0],$this->properties['picture_file']['tmp_name'][0], $picture);
            }
            else
                AlertSet::addError('Error saving picture. Please try again.');
        }
    }

    protected function saveImage($content_type, $img, $picture)
    {
        $picture_file=new PictureFile(array
        (
            'pictures_id'=>$picture->get_id(),
            'original'=>true,
            'content_type'=>$content_type,
            'img_data_path'=>$img
        ));


        if($picture_file->get_valid())
        {
            if(!$picture_file->add())
            {
                AlertSet::addError('Error saving uploaded picture. Please try again.');
                $picture->delete();
            }
        }
        else
        {
            AlertSet::addError('The file you selected is not a valid image or is too large.');
            $picture->delete();
        }

        unset($picture_file);
    }
}


/* 
* TobjectArrayController allows to process arrays of tobjects. Foe instance perform bulk add or update on tobjects.
* TobjectArrayController creates tobjects from the array input in the format ['property'][index], where index is 
* the index of the tobjecy to be created form such array. This particular format happens when submitting a form
* containing an array of inputs.
* Clearly the more efficient way of doing operations suah as adding or deleting would not involve iterating through
* the list of tobjects and doing an action (add, update or delete) on each tobject separately. Instead one needs to 
* create a query that does an action on multiple tobjects (like inserting multiple records) at a time, 
* but this is not what is accomplished here. 
* This is a Controller of MVC, as such it does not interact with database, instead it uses (needs to use) Tobjects 
* and Tobject classes (which represent teh Model of MVC) to perform teh actions. Default Model implementation does
* not provide functionality to insert of update multiple entries at once, but allows for such functionality to
* be implemented at any time.
*
*/
abstract class TobjectArrayController extends Controller
{
	protected $tobjects;
	protected $propertiesArray;
	protected $arrayFromForm;
	/*overrides Controller constructior*/
	function __construct($properties, $user, $module, $isTobjectArray = false, $arrayFromForm = true)
	{
		$this->module = $module;
		$this->user = $user;;
		if (!$isTobjectArray)
		{
			
			//properties array is used for validation and must correspond to the tobject
			$this->properties = $properties;
			$this->loadTobject($properties);
		}
		else
		{
			$this->arrayFromForm = $arrayFromForm;
			$this->tobjects = array();
			$this->propertiesArray = $properties;
			$this->loadTobjects($properties);
		}
	}

	protected function getTobjectFromArray($index)
	{
		$this->tobject = $this->tobjects[$index];		
		$this->properties = array();		
		$this->fillProperties($index);		

	}
	
	protected function fillProperties($index)
	{	
		if ($this->arrayFromForm)
			$this->fillPropertiesFromFormArray(array(), $this->propertiesArray, $index);
		else
			$this->fillPropertiesFromArrayOfProperties($index);
	}
	
	
	/*fills properties from array where Array of Properties = array['index']*/
	protected function fillPropertiesFromArrayOfProperties($index)
	{
		$this->properties = $this->propertiesArray[$index];
	}
	
	protected function fillPropertiesFromFormArray($pkeys, $filler, $index)
	{
		if (is_array($filler))
		{
			foreach ($filler as $key=>$fill)
			{				
				if (!is_int($key))
				{					
					$pkeys[] = $key;
					$this->fillPropertiesFromFormArray($pkeys, $fill, $index);
					$pkeys = array();
				}
				else if ($key == $index)
				{					
					$property = array();
					//AlertSet::addInfo("pkey len: ".count($pkeys));
					for ($i = count($pkeys) - 1; $i >= 0; $i--)
					{
						
						if (($i > 0) || (count($pkeys) == 1))
						{
							if ($i == 0)
								$this->properties[$pkeys[$i]] = $fill;
							else
								$property[$pkeys[$i]] = $fill;
						}
						else if ($i == 0)
						{							
							$this->properties[$pkeys[$i]] = $property;
						}
						else	
						{										
							$property[$pkeys[$i]] = $property;
						}			
					}		
				}
			}			
		}		
	}
	
	protected function getNumberOfTobjectsInProperties($filler)
	{
		if (is_array($filler))
		{
            $key = NULL;
			foreach ($filler as $key=>$fill)
			{				
				if (!is_int($key))
				{						
					$res = $this->getNumberOfTobjectsInProperties($fill);
					if ($res !== false)			
					return $res;					
				}				
			}	
			if (is_int($key))
			{		
				return $key;
			}	
			return -1;	
		}
		else		
			return false;		
	}
	
	protected function loadTobjects($properties)
	{
		$cnt = intval($this->getNumberOfTobjectsInProperties($this->propertiesArray));
	
		for($j = 0; $j <= $cnt; $j++)
		{
			$this->properties = array();						
			$this->fillProperties($j);			
			$this->loadTobject($this->properties);
			$this->tobjects[] = $this->tobject;
		}
	}

	public function setTobjects($tobjects)
	{
		$this->tobjects = $tobjects;
	}
	
	public function bulkAdd()
	{
		foreach($this->tobjects as $i=>$tobject)
		{		
			$this->getTobjectFromArray($i);
			if( !$this->add() )
                return false;
		}

        return true;
	}
	
	public function addOne($index)
	{
		$this->getTobjectFromArray($index);
		$this->add();
	}
	
	public function bulkUpdate()
	{
		foreach ($this->tobjects as $i=>$tobject)
		{
			$this->getTobjectFromArray($i);
			$this->update();
		}

        return true;
	}
	
	public function updateOne($index)
	{
		$this->getTobjectFromArray($index);
		$this->update();
	}
	
	public function bulkDelete()
	{
		foreach ($this->tobjects as $i=>$tobject)
		{
			$this->getTobjectFromArray($i);
			$this->delete();
		}

        return true;
	}
	
	public function deleteOne($index)
	{
		$this->getTobjectFromArray($index);
		$this->delete();
	}
	
}
?>