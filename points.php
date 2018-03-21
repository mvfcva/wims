<?PHP

$message=null;

$pdo = new PDO('sqlite:'.getcwd().'/db/wims.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$filters=array('collectionid'=>FILTER_SANITIZE_NUMBER_INT,'mapid'=>FILTER_SANITIZE_NUMBER_INT,'id'=>FILTER_SANITIZE_NUMBER_INT);
$input_type=($_SERVER['REQUEST_METHOD']=='POST') ? INPUT_POST : INPUT_GET;
$inputs=filter_input_array($input_type,$filters);

$conditions=array();

foreach(array_keys($filters) as $key)
{
	if(isset($inputs[$key]))
	{
		array_push($conditions,'"'.$key.'" = "'.$inputs[$key].'"');
	}
}

if(count($conditions)<1)
{
	header("Location: collections.php");
	exit;
}

$sql="SELECT * FROM point WHERE ".join(' and ',$conditions);
$points = $pdo->query($sql)->fetchAll();

$mapids=array("0"); // mapid can be null
foreach($points as $point)
{
	if(isset($point['mapid'])){array_push($mapids,'id="'.$point['mapid'].'"');}
}

$sql="SELECT * FROM map WHERE ".join(' or ',$mapids);
$maps = $pdo->query($sql)->fetchAll();

$collectionids=array("0"); // collectionid can be null
foreach($points as $point)
{
	if(isset($point['collectionid'])){array_push($collectionids,'id="'.$point['collectionid'].'"');}
}

$sql="SELECT * FROM collection WHERE ".join(' or ',$collectionids);
$collections = $pdo->query($sql)->fetchAll();

include 'vendor/autoload.php';

try
{
	$loader = new Twig_Loader_Filesystem('templates');
	$twig = new Twig_Environment($loader);
	$template = $twig->loadTemplate('points.html');

	echo $template->render(array (
	'message' => $message,
	'points' => $points,
	'maps' => $maps,
	'collections' => $collections,
	));

}
catch (Exception $e)
{
	die ('ERROR: ' . $e->getMessage());
}


?>
