<?PHP

$collection = null;
$message=null;
$id=null;

$pdo = new PDO('sqlite:'.getcwd().'/db/wims.sqlite');


if(isset($_REQUEST['id']))
{
	$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
	if(is_null($id))
	{
	$id = filter_input(INPUT_POST,'id',FILTER_SANITIZE_NUMBER_INT);
	}
	$query = "SELECT * FROM collection WHERE id = $id";
	$collection = $pdo->query($query)->fetch();

	if(!is_array($collection))
	{
		header("Location: collections.php");
		exit;
	}

	if(isset($_POST['delete']))
	{
		$count = $pdo->exec("DELETE FROM collection WHERE id = $id");
		$message = "$count collection deleted";
		$collection = null;
	}
}

if(isset($_POST['name'],$_POST['type'],$_POST['color']) and !isset($_POST['delete']))
{
	$name = filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING);
	$type = filter_input(INPUT_POST,'type',FILTER_SANITIZE_STRING);
	$color = filter_input(INPUT_POST,'color',FILTER_SANITIZE_STRING);

	if(is_null($id)) 
	{
		$query = 'INSERT INTO "collection" ( "name", "type", "color" ) VALUES ( ?, ?, ? )';
		$sth = $pdo->prepare($query);
		$sth->execute(array($name,$type,$color));
		$id = $pdo->lastInsertId();
		$message = "Collection created";

	}
	else
	{
		$query = 'UPDATE "collection" SET "name" = ?, "type" = ?, "color" = ? WHERE id = ?';
		$sth = $pdo->prepare($query);
		$sth->execute(array($name,$type,$color,$id));
		$message = "Collection updated";
	}

	$query = "SELECT * FROM collection WHERE id = $id";
	$collection = $pdo->query($query)->fetch();

}


include 'vendor/autoload.php';

#try
#{
	$loader = new Twig_Loader_Filesystem('templates');
	$twig = new Twig_Environment($loader);
	$template = $twig->loadTemplate('collection.html');

	echo $template->render(array (
	'message' => $message,
	'collection' => $collection
	));

#}
#catch (Exception $e)
#{
#	die ('ERROR: ' . $e->getMessage());
#}


?>
