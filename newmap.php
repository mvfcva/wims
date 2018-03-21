<?PHP

$message = "Please load a map picture and select its position";
$id=null;

if(isset($_FILES['image']))
{

$uploaddir = 'image/';
$uploadfile = $uploaddir . basename($_FILES['image']['tmp_name']) . "-" . basename($_FILES['image']['name']);

	if (move_uploaded_file($_FILES['image']['tmp_name'], getcwd()."/".$uploadfile)) 
	{
		$message = "File ".basename($_FILES['image']['name'])." successfully uploaded.";

		$lat = filter_input(INPUT_POST,'lat',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
		$lng = filter_input(INPUT_POST,'lng',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);

		$R=6378137; // Earth’s radius, sphere
		$offset = 100; // meters

		// offset in radians
		$lato=$offset/$R;
		$lngo=$offset/($R*cos(pi()*$lat/180));

		$lat1 = $lat + $lato * 180/pi();
		$lat2 = $lat + $lato * 180/pi();
		$lat3 = $lat - $lato * 180/pi();

		$lng1 = $lng - $lngo * 180/pi();
		$lng2 = $lng + $lngo * 180/pi();
		$lng3 = $lng - $lngo * 180/pi();

		$pdo = new PDO('sqlite:'.getcwd().'/db/wims.sqlite');

		$sth = $pdo->prepare('INSERT INTO "map" ("name","file","type","lat1","lat2","lat3","lng1","lng2","lng3") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');

		$sth->execute(array(basename($_FILES['image']['name']),$uploadfile,$_FILES['image']['type'],$lat1,$lat2,$lat3,$lng1,$lng2,$lng3));
		$id = $pdo->lastInsertId();

	}
	else
	{
		$message = "Error : " . codeToMessage($_FILES['image']['error']);
	}

}

include 'vendor/autoload.php';

try 
{
	$loader = new Twig_Loader_Filesystem('templates');
	$twig = new Twig_Environment($loader);
	$template = $twig->loadTemplate('newmap.html');

	echo $template->render(array (
	'message' => $message,
	'id' => $id
	));

} 
catch (Exception $e) 
{
	die ('ERROR: ' . $e->getMessage());
}

function codeToMessage($code)
{
switch ($code) {
case UPLOAD_ERR_INI_SIZE:
$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
		break;
		case UPLOAD_ERR_FORM_SIZE:
		$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
		break;
		case UPLOAD_ERR_PARTIAL:
		$message = "The uploaded file was only partially uploaded";
		break;
		case UPLOAD_ERR_NO_FILE:
		$message = "No file was uploaded";
		break;
		case UPLOAD_ERR_NO_TMP_DIR:
		$message = "Missing a temporary folder";
		break;
		case UPLOAD_ERR_CANT_WRITE:
		$message = "Failed to write file to disk";
		break;
		case UPLOAD_ERR_EXTENSION:
		$message = "File upload stopped by extension";
		break;

		default:
		$message = "Unknown upload error";
		break;
	}
	return $message;
} 
?>
