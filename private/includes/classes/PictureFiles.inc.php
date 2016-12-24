<?php
/*
Prior Versions unknown

Version 13.03.01
	- Added support for jpeg file format. Works with show-picture.php from the same date
*/
//PictureFiles::$imagick=extension_loaded('imagick');
//PictureFiles::$gd=extension_loaded('gd');

class PictureFiles extends Tobjects
{
	const FILE_PATH='pictures/';
	public static $imagick=false;
	public static $gd=false;
	
	function __construct()
	{
		$this->search_keys = array();
		$this->table_name = 'picture_files';
		$this->imagick = extension_loaded('imagick');
		$this->gd = extension_loaded('gd');
	}
	
	
	
	public function loadOriginal($pictures_id)
	{
		$where=
			'pictures_id='.intval($pictures_id).'
			AND
			picture_files.original=TRUE';
		return $this->load($where);
	}
	
	public function loadBySize($pictures_id, $width, $height)
	{
		$where=
			'pictures_id='.intval($pictures_id).'
			AND
			width='.intval($width).'
			AND
			height='.intval($height);
		$data = $this->load($where);
        return $data;
	}
		
	
	protected function load($where='', $values = NULL, $return_array=false, $order_by='', $limit='')
	{
		if($where!='')
			$where='WHERE '.$where;
		
		if($order_by!='')
			$order_by='ORDER BY '.$order_by;
		
		$sql =
			'SELECT
				id,
				pictures_id,
				original,
				width,
				height,
				content_type
			FROM
				picture_files
			'.$where.'
			'.$order_by;


        $this->found_rows=0;
        if($rs = pdologged_preparedQuery($sql, $values))
        {
            $this->found_rows=$rs->rowCount();
			$data=array();
			while($row = $rs->fetch(PDO::FETCH_ASSOC))
			{
				$data[]=new PictureFile(array(
					'id'=>$row['id'],
					'pictures_id'=>$row['pictures_id'],
					'original'=>$row['original'],
					'width'=>$row['width'],
					'height'=>$row['height'],
					'content_type'=>$row['content_type']
				));
				
				if(!$return_array)
				{
					//mysql_free_result($rs);
					return $data[0];
				}
			}
			
			//mysql_free_result($rs);
			return $data;
		}
		
		return false;
	}
}

class PictureFile extends Tobject
{
	protected $id, $pictures_id, $valid, $original, $width, $height, $filename;
	private $im, $png, $jpg;
	protected $content_type, $isPNG;
	
	function __construct($properties)
	{
		// default to jpeg. can be overriden by setting content-type in the properties
		// supports image/jpeg and image/png
		$this->content_type = 'image/jpeg';
		$this->valid=true;
		parent::__construct('picture_files',$properties);
		
		if(!PictureFiles::$imagick)
			ini_set('memory_limit', '256M');
		//else
		//	ini_set('memory_limit', '64M');

		if(isset($properties['img_data_path']))
		{
			if(PictureFiles::$imagick)
			{
				try
				{
					$this->im=new Imagick($properties['img_data_path']);
					$this->width=$this->im->getImageWidth();
					$this->height=$this->im->getImageHeight();
					if ($this->content_type == 'image/png')
					{
						$this->im->setImageFormat('png');
						$this->isPNG = true;
					}
					else
					{
						$this->im->setImageFormat('jpg');
						$this->isPNG = false;
					}
					//$this->png=$img->getImageBlob();
				}
				catch(Exception $ex)
				{
                    AlertSet::addError('Failed to upload file to server.');
					$this->valid=false;
				}
			}
			else
			{
				if(($gd_img = imagecreatefromstring(file_get_contents($properties['img_data_path'])))!==false)
				{
					$this->width = imagesx($gd_img);
					$this->height = imagesy($gd_img);
					
					ob_start();
					if ($this->content_type == 'image/png')
                    {
                        imagepng($gd_img);
                        $this->isPNG = false;
                    }
					else
                    {
                        imagejpeg($gd_img);
                        $this->isPNG = false;
                    }
					$this->jpg = ob_get_clean();
				}
				else
                {
                    AlertSet::addError('Failed to upload file to server.');
                    $this->valid=false;
                }
			}
		}
		
	}
	
	
	public function get_id()
	{
		return intval($this->id);
	}
	
	public function get_valid()
	{
		return intval($this->valid);
	}
	
	public function get_width()
	{
		return intval($this->width);
	}
	
	public function get_height()
	{
		return intval($this->height);
	}
	
	public function get_filename()
	{
		return ($this->filename);
	}
	
	public function get_content_type()
	{
		return ($this->content_type);
	}

    public function set_original($original)
    {
        $this->original = $original;
    }
	
	public function add()
	{
		if($this->valid)
		{
			$sql =
				'INSERT INTO picture_files
				(
					pictures_id,
					original,
					width,
					height,
					content_type
				)
				VALUES
				(
					'.intval($this->pictures_id).',
					'.intval($this->original).',
					"'.addslashes($this->width).'",
					"'.addslashes($this->height).'",
					"'.addslashes($this->content_type).'"
				)';
			if(pdologged_preparedQuery($sql, array()))
			{
				$this->id=Tabmin::$db->lastInsertId();
				
				if(PictureFiles::$imagick)
				{					
					if ($this->content_type == 'image/png')
						$this->im->writeImage(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.png');
					else
						$this->im->writeImage(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.jpg');
					
				}
				else
				{
					if ($this->isPNG)
						file_put_contents(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.png', $this->png);
					else
						file_put_contents(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.jpg', $this->jpg);
				}
				
				return true;
			}
		}
		return false;
	}
	
	public function update()
	{
		$sql =
			'UPDATE picture_files SET
				pictures_id='.intval($this->pictures_id).',
				original='.intval($this->original).',
				width="'.intval($this->width).'",
				height="'.intval($this->height).'"
			WHERE id='.intval($this->id);
		
		if(pdologged_exec($sql))
		{
			if(PictureFiles::$imagick)
			{
				if(isset($this->im))
				{
					if ($this->content_type == 'image/png')
						$this->im->writeImage(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.png');
					else
						$this->im->writeImage(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.jpg');
				}
			}
			else
			{
				if(!empty($this->png) && $this->isPNG)
					file_put_contents(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.png', $this->png);
				else if (!empty($this->jpg) && !$this->isPNG)
					file_put_contents(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.jpg', $this->jpg);
			}
			
			return true;
		}
		return false;
	}
	
	public function delete()
	{
		$sql =
			'DELETE FROM picture_files
			WHERE
				id='.intval($this->id).'
			LIMIT 1';
		if(pdologged_exec($sql))
		{
			if ($this->content_type == 'image/png')
				unlink(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.png');
			else
				unlink(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.jpg');
			return true;
		}
		
		return false;
	}
	
	public function calculateDimensions($minw, $minh, $maxw, $maxh)
	{
		$w=$this->width;
		$h=$this->height;
		
		$srcx = 0;
		$srcy = 0;
		$srcw = $w;
		$srch = $h;
		
		if($minw==$maxw && $minh==$maxh && $minh !== NULL && $minw !== NULL)
		{
			if($minw/$minh < $w/$h)
			{
				$srcx =	intval(($w - $minw*($h/$minh))/2);
				$srcw = intval($w - $srcx*2);
			}
			else if($minw/$minh > $w/$h)
			{
				$srcy =	intval(($h - $minh*($w/$minw))/2);
				$srch = intval($h - $srcy*2);
			}
			$w2 = $minw;
			$h2 = $minh;
		}
		else
		{
			if($minh && $minw)
			{
				if($minw/$minh < $w/$h)
				{
					$h2=($h>$minh ? $h : $minh);
					$w2=intval(($h2/$h)*$w);
				}
				else
				{
					$w2=($w>$minw ? $w : $minw);
					$h2=intval(($w2/$w)*$h);
				}
			}
			else if($minw)
			{
				$w2=($w>$minw ? $w : $minw);
				$h2=intval(($w2/$w)*$h);
			}
			else if($minh)
			{
				$h2=($h>$minh ? $h : $minh);
				$w2=intval(($h2/$h)*$w);
			}
			
			if(!empty($h2))
				$h=$h2;
			if(!empty($w2))
				$w=$w2;
			
			if($maxh && $maxw)
			{
				if($maxw/$maxh > $w/$h)
				{
					$h2=($h<$maxh ? $h : $maxh);
					$w2=intval(($h2/$h)*$w);
				}
				else
				{
					$w2=($w<$maxw ? $w : $maxw);
					$h2=intval(($w2/$w)*$h);
				}
			}
			else if($maxw)
			{
				$w2=($w<$maxw ? $w : $maxw);
				$h2=intval(($w2/$w)*$h);
			}
			else if($maxh)
			{
				$h2=($h<$maxh ? $h : $maxh);
				$w2=intval(($h2/$h)*$w);
			}
			if(!isset($h2))
				$h2=$h;
			if(!isset($w2))
				$w2=$w;
		}
		
		return array
		(
			'srcx'=>$srcx,
			'srcy'=>$srcy,
			'srcw'=>$srcw,
			'srch'=>$srch,
			'width'=>$w2,
			'height'=>$h2
		);
	}
	
	public function resize($srcx, $srcy, $w, $h, $srcw, $srch)
	{
		if(PictureFiles::$imagick)
		{
			
			if(empty($this->im))
			{
				if ($this->content_type == 'image/png')
				{
					$this->im=new Imagick(UPLOAD_ROOT . PictureFiles::FILE_PATH.$this->id.'.png');
					$this->im->setImageOpacity(1.0);
					$this->im->setImageFormat('png');
				}
				else
				{
					$this->im=new Imagick(UPLOAD_ROOT . PictureFiles::FILE_PATH.$this->id.'.jpg');
					//$this->im->setImageOpacity(1.0);
					$this->im->setImageFormat('jpg');
				}
			}
			
			if($this->im->cropImage($srcw, $srch, $srcx, $srcy) && $this->im->resizeImage($w, $h, imagick::FILTER_CATROM, 1.0))
			{
				$this->width=$this->im->getImageWidth();
				$this->height=$this->im->getImageHeight();
				//$this->png=$this->im->getImageBlob();
				
				return true;
			}
		}
		else
		{
			if ($this->content_type == 'image/png')
				$gd = imagecreatefrompng(UPLOAD_ROOT . PictureFiles::FILE_PATH.$this->id.'.jpg');
			else
				$gd = imagecreatefromjpeg(UPLOAD_ROOT . PictureFiles::FILE_PATH.$this->id.'.jpg');
				
			if($gd!==false)
			{
				$resized=imagecreatetruecolor($w, $h);
				
				if(imagecopyresampled($resized, $gd, 0, 0, $srcx, $srcy, $w, $h, $srcw, $srch))
				{
					$this->width = imagesx($resized);
					$this->height = imagesy($resized);
					
					ob_start();
					if ($this->content_type == 'image/png')
					{
						imagepng($resized);
						$this->png = ob_get_clean();
					}
					else
					{
						imagejpeg($resized);
						$this->jpg = ob_get_clean();
					}
					
					return true;
				}
			}
			
			
		}
		return false;
	}
	
	public function write()
	{
		if ($this->content_type == 'image/png')
		{
			if(!empty($this->png))
				echo $this->png;
			else
				readfile(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.png');
		}
		else
		{
			if(!empty($this->jpg))
				echo $this->jpg;
			else
				readfile(UPLOAD_ROOT . PictureFiles::FILE_PATH . $this->id .'.jpg');
		}
	}
}
