<?PHP

$collections=null;
$message=null;

$pdo = new PDO('sqlite:'.getcwd().'/db/wims.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#$sql = 'SELECT * FROM collection';
$sql = 'SELECT collection.id, collection.name, collection.type, collection.color, count(DISTINCT point.id) as points FROM collection left join point on collection.id = point.collectionid 
group by collection.id';
try {
	$sth = $pdo->prepare($sql);
	$sth->execute();
	$collections = $sth->fetchAll();
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
	$template = $twig->loadTemplate('collections.html');

	echo $template->render(array (
	'message' => $message,
	'collections' => $collections
	));

}
catch (Exception $e)
{
	die ('ERROR: ' . $e->getMessage());
}


?>
