<?PHP

$map = null;
$message=null;

if(isset($_POST['id'],$_POST['name']))
{
	$name = filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING);
	$id = filter_input(INPUT_POST,'id',FILTER_SANITIZE_NUMBER_INT);
	$level = filter_input(INPUT_POST,'level',FILTER_SANITIZE_NUMBER_INT);
	$lat1 = filter_input(INPUT_POST,'lat1',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
	$lat2 = filter_input(INPUT_POST,'lat2',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
	$lat3 = filter_input(INPUT_POST,'lat3',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
	$lng1 = filter_input(INPUT_POST,'lng1',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
	$lng2 = filter_input(INPUT_POST,'lng2',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
	$lng3 = filter_input(INPUT_POST,'lng3',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);

	$pdo = new PDO('sqlite:'.getcwd().'/db/wims.sqlite');

	$sth = $pdo->prepare('UPDATE "map" SET "name"=?,"level"=?,"lat1"=?,"lat2"=?,"lat3"=?,"lng1"=?,"lng2"=?,"lng3"=? WHERE "id"=?');
	$sth->execute(array($name,$level,$lat1,$lat2,$lat3,$lng1,$lng2,$lng3,$id)) and $message = "Map updated";

	$query = "SELECT * FROM map WHERE id = $id";
	$map = $pdo->query($query)->fetch();
}
elseif(isset($_GET['id']))
{
	$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
	$pdo = new PDO('sqlite:'.getcwd().'/db/wims.sqlite');

	$query = "SELECT * FROM map WHERE id = $id";
	$map = $pdo->query($query)->fetch();
}

if(!is_array($map))
{
	header("Location: listmaps.php");
	exit;
}

include 'vendor/autoload.php';

try
{
	$loader = new Twig_Loader_Filesystem('templates');
	$twig = new Twig_Environment($loader);
	$template = $twig->loadTemplate('editmap.html');

	echo $template->render(array (
	'message' => $message,
	'map' => $map
	));

}
catch (Exception $e)
{
	die ('ERROR: ' . $e->getMessage());
}


?>
