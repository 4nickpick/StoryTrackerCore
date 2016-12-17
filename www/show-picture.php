<?
/*
Version 09.10.15
Version 10.05.14
	- Added 'pictures_id_default' get variable to specify a default image to show instead of id=0
Version 13.03.01
	- Added support for jpeg file format. Works with PictureFiles.inc.php from the same date
*/

include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
ErrorSet::$display=false;

//ErrorSet::$email=ERROR_REPORTING_EMAIL;

$pictures_id=@$_GET['pictures_id'];

$minw=$minh=$maxw=$maxh=NULL;
if(!empty($_GET['w']))
    $minw=$maxw=$_GET['w'];
if(!empty($_GET['h']))
    $minh=$maxh=$_GET['h'];
if(!empty($_GET['minw']))
    $minw=$_GET['minw'];
if(!empty($_GET['minh']))
    $minh=$_GET['minh'];
if(!empty($_GET['maxw']))
    $maxw=$_GET['maxw'];
if(!empty($_GET['maxh']))
    $maxh=$_GET['maxh'];
$pfManger = new PictureFiles();
if(!$picture_file_original=$pfManger->loadOriginal($pictures_id))
{
    if( !$picture_file_original )
    {
        echo $pictures_id;
        die('Picture File Not Found');
    }
}
$size_params=$picture_file_original->calculateDimensions($minw, $minh, $maxw, $maxh);

if(!$picture_file=$pfManger->loadBySize($pictures_id, $size_params['width'], $size_params['height']))
{
    //ini_set('memory_limit', '256M');
    $picture_file=$picture_file_original;
    $picture_file->set_original(false);

    if($picture_file->resize($size_params['srcx'], $size_params['srcy'], $size_params['width'], $size_params['height'], $size_params['srcw'], $size_params['srch']))
        $picture_file->add();
}
unset($picture_file_original);

if($picture_file)
{
    /*$picjpg = fopen(UPLOAD_ROOT . PictureFiles::FILE_PATH . $picture_file->id .'.jpg', 'r');
    if ($picjpg)
        $extension = "jpg";*/
    //die($picture_file->content_type);
    $extension = 'jpg';

    $expires = 60 * 60 * 24 * 3;
    $exp_gmt = gmdate('D, d M Y H:i:s', time() + $expires ).' GMT';

    $mod_gmt = gmdate('D, d M Y H:i:s', @filemtime(UPLOAD_ROOT . PictureFiles::FILE_PATH . $picture_file->get_id() .'.'.$extension)).' GMT';

    if ($picture_file->get_content_type() == 'image/png')
        @header('Content-type: image/png');
    else
        @header('Content-type: image/jpeg');

    @header('Expires: '.$exp_gmt);

    //check's the file last modified date against
    if( getenv('HTTP_IF_MODIFIED_SINCE') == $mod_gmt )
        @header('Last-Modified: '.$mod_gmt, true, 304);
    else
        @header('Last-Modified: '.$mod_gmt);

        if( isset($_GET['download']) )
        {
            @header('Content-Description: File Transfer');
            @header('Content-Disposition: attachment; filename=storytracker-dl-'.$picture_file->get_id().'.'.$extension);
        }


        @header('Cache-Control: public, max-age='.$expires);
     //@header('Content-Length: '. filesize(UPLOAD_ROOT . PictureFiles::FILE_PATH . $picture_file->get_id() .'.'.$extension));

    $picture_file->write();
}
else
    echo('File not found.');
?>