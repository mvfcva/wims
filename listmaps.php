<?PHP

$maps=null;
$message=null;

$pdo = new PDO('sqlite:'.getcwd().'/db/wims.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$sql = 'SELECT map.id, map.name, map.level, count(DISTINCT point.id) as points FROM map left join point on map.id = point.mapid 
group by map.id';
try {
	$sth = $pdo->prepare($sql);
	$sth->execute();
	$maps = $sth->fetchAll();
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
	$template = $twig->loadTemplate('listmaps.html');

	echo $template->render(array (
	'message' => $message,
	'maps' => $maps
	));

}
catch (Exception $e)
{
	die ('ERROR: ' . $e->getMessage());
}


?>
