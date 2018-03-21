<?PHP

function log_error($e)
{
	error_log(date('c').' '.$e->getFile().':'.$e->getLine().' '.$e->getMessage()."\n",3,'error.log');
	print("oops\n");
}

set_exception_handler('log_error');

$point = null;
$mapid=null;
$collectionid=null;
$message=null;
$id=null;

$pdo = new PDO('sqlite:'.getcwd().'/db/wims.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if(isset($_REQUEST['id']))
{
	$id = filter_input(INPUT_POST,'id',FILTER_SANITIZE_NUMBER_INT);
	if(is_null($id))
	{
	$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
	}
	$query = "SELECT * FROM point WHERE id = $id";
	$point = $pdo->query($query)->fetch();

	if(!is_array($point))
	{
		header("Location: collections.php");
		exit;
	}

	$mapid = $point['mapid'];

	if(isset($_POST['delete']))
	{
		$count = $pdo->exec("DELETE FROM point WHERE id = $id");
		$message = "$count point deleted";
		$point = null;
	}
}

if(!isset($mapid) and isset($_REQUEST['mapid']))
{
	$mapid = filter_input(INPUT_POST,'mapid',FILTER_SANITIZE_NUMBER_INT);
	if(is_null($mapid))
	{
	$mapid = filter_input(INPUT_GET,'mapid',FILTER_SANITIZE_NUMBER_INT);
	}
}

if(!isset($collectionid) and isset($_REQUEST['collectionid']))
{
	$collectionid = filter_input(INPUT_POST,'collectionid',FILTER_SANITIZE_NUMBER_INT);
	if(is_null($collectionid))
	{
	$collectionid = filter_input(INPUT_GET,'collectionid',FILTER_SANITIZE_NUMBER_INT);
	}
}

if(isset($_POST['name'],$_POST['lat'],$_POST['lng'],$_POST['collectionid'],$_POST['mapid'],$_POST['level'],$_POST['description']) and !isset($_POST['delete']))
{
	$name = filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING);
	$description = filter_input(INPUT_POST,'description',FILTER_SANITIZE_STRING);
	$lat = filter_input(INPUT_POST,'lat',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
	$lng = filter_input(INPUT_POST,'lng',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
	$collectionid = filter_input(INPUT_POST,'collectionid',FILTER_SANITIZE_NUMBER_INT);
	$mapid = filter_input(INPUT_POST,'mapid',FILTER_SANITIZE_NUMBER_INT);
	$level = filter_input(INPUT_POST,'level',FILTER_SANITIZE_NUMBER_INT);

	if(is_null($id)) 
	{
		$query = 'INSERT INTO "point" ( "name", "lat", "lng", "collectionid", "mapid", "level", "description" ) VALUES ( ?, ?, ?, ?, ?, ?, ? )';
		$sth = $pdo->prepare($query);
		$sth->execute(array($name,$lat,$lng,$collectionid,$mapid,$level,$description));
		$id = $pdo->lastInsertId();
		$message = "Point created";

	}
	else
	{
		$query = 'UPDATE "point" SET "name" = ?, "lat" = ?, "lng" = ?, "collectionid" = ?, "mapid" = ?, "level" = ?, "description" = ?  WHERE id = ?';
		$sth = $pdo->prepare($query);
		$sth->execute(array($name,$lat,$lng,$collectionid,$mapid,$level,$description,$id));
		$message = "Point updated";
	}

	$query = "SELECT * FROM point WHERE id = $id";
	$point = $pdo->query($query)->fetch();
	$mapid = $point['mapid'];

}

if(isset($mapid))
{
	$map = $pdo->query("SELECT * from map WHERE id = $mapid")->fetch(); 
}
else
{
	header("Location: listmaps.php");
	exit;
}

if(!isset($point))
{
	$lat=($map['lat2']+$map['lat3'])/2;
	$lng=($map['lng2']+$map['lng3'])/2;
	$point = array('lat'=>$lat,'lng'=>$lng);
	if(isset($mapid)){$point['mapid']=$mapid;}
	if(isset($collectionid)){$point['collectionid']=$collectionid;}
}

$collections = $pdo->query("SELECT * from collection")->fetchAll();
$maps = $pdo->query("SELECT * from map")->fetchAll();

include 'vendor/autoload.php';

try
{
	$loader = new Twig_Loader_Filesystem('templates');
	$twig = new Twig_Environment($loader);
	//$twig = new Twig_Environment($loader,array('debug' => true));
	//$twig->addExtension(new Twig_Extension_Debug());
	$template = $twig->loadTemplate('point.html');

	echo $template->render(array (
	'message' => $message,
	'map' => $map,
	'maps' => $maps,
	'collections' => $collections,
	'point' => $point
	));

}
catch (Exception $e)
{
	die ('ERROR: ' . $e->getMessage());
}


?>
