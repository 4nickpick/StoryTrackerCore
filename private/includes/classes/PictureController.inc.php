<?php

class PictureController extends Controller
{
    protected $caption = 'Picture';

    protected function loadTobject($properties)
    {
        $this->tobject = new Picture($properties);

    }

    public function add()
    {
        if( $this->processPicture() )
        {
            $this->json['id'] = $this->tobject->get_id();
            return true;
        }
        return false;
    }

    public function update()
    {
        if( parent::update() )
            $this->json['id'] = $this->tobject->get_id();
    }

    public function processPicture()
    {
        parent::processPicture($this->properties['users_id']);
        return AlertSet::$success;
    }


    public function addRelationship($table_name, $column_name, $related_object_id)
    {
        if( $this->tobject->addRelationship($table_name, $column_name, $related_object_id) ){
            $this->json['id'] = $this->tobject->get_id();
            $this->json['success'] = true;
            return true;
        }
        else {
            AlertSet::addError('Unable to add relationship');
            return false;
        }

    }

    public function removeRelationship($table_name, $column_name, $related_object_id)
    {
        if( $this->tobject->removeRelationship($table_name, $column_name, $related_object_id) ){
            $this->json['id'] = $this->tobject->get_id();
            $this->json['success'] = true;
            return true;
        }
        else {
            AlertSet::addError('Unable to remove relationship');
            return false;
        }

    }

    public function makeCoverPhoto($table_name, $column_name, $related_object_id)
    {
        if( $this->tobject->setCoverPhoto($table_name, $column_name, $related_object_id) ){
            $this->json['id'] = $this->tobject->get_id();
            $this->json['success'] = true;
            AlertSet::addSuccess('Cover Photo was set successfully.');
            return true;
        }
        else {
            AlertSet::addError('Unable to set cover photo');
            return false;
        }

    }


}