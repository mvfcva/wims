<?PHP

$points=null;
$message=null;

$pdo = new PDO('sqlite:'.getcwd().'/db/wims.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql="Select point.id, point.collectionid, point.mapid, collection.name as collection, collection.type, map.name as map, point.name as point, point.level, point.description from point 
join collection on collection.id = point.collectionid 
join map on map.id = point.mapid
order by collection.name, point.name";
try {
	$sth = $pdo->prepare($sql);
	$sth->execute();
	$points = $sth->fetchAll();
	$sth = null;
}
catch (PDOException $e) {
	$message=$e->getMessage();
}

include 'vendor/autoload.php';

try
{
	$loader = new Twig_Loader_Filesystem('templates');
	$twig = new Twig_Environment($loader);
	$template = $twig->loadTemplate('listpoints.html');

	echo $template->render(array (
	'message' => $message,
	'points' => $points
	));

}
catch (Exception $e)
{
	die ('ERROR: ' . $e->getMessage());
}


?>
