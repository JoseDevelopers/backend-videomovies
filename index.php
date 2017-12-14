<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$db = new mysqli('192.168.0.130', 'root', 'Nolasabenidios1*', 'videomovies');

// Configuración de cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

$app->get("/pruebas/:id", function($id) use($app, $db){
	echo "Hola mundo desde Slim PHP " .$id;
});

$app->get("/probando", function() use($app){
	echo "OTRO TEXTO CUALQUIERA";
});

// LISTAR TODOS LAS PELICULAS
$app->get('/peliculas', function() use($db, $app){
	$sql = 'SELECT * FROM peliculas WHERE borrado = 0 ORDER BY id DESC;';
  $db->set_charset("utf8");
	$query = $db->query($sql);

	$peliculas = array();
	while ($pelicula = $query->fetch_assoc()) {
		$peliculas[] = $pelicula;
	}

	$result = array(
			'status' => 'success',
			'code'	 => 200,
			'data' => $peliculas
		);

	echo json_encode($result);
});

// LISTAR TODOS LOS GENEROS
$app->get('/generos', function() use($db, $app){
	$sql = 'SELECT * FROM genero ORDER BY nombre;';
  $db->set_charset("utf8");
	$query = $db->query($sql);

	$generos = array();
	while ($genero = $query->fetch_assoc()) {
		$generos[] = $genero;
	}

	$result = array(
			'status' => 'success',
			'code'	 => 200,
			'data' => $generos
		);

	echo json_encode($result);
});

// DEVOLVER UNA SOLA PELICULA
$app->get('/pelicula/:id', function($id) use($db, $app){
	$sql = 'SELECT * FROM peliculas WHERE id = '.$id;
  $db->set_charset("utf8");
	$query = $db->query($sql);

	$result = array(
		'status' 	=> 'error',
		'code'		=> 404,
		'message' 	=> 'Pelicula no disponible'
	);

	if($query->num_rows == 1){
		$pelicula = $query->fetch_assoc();

		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'data' 	=> $pelicula
		);
	}

	echo json_encode($result);
});

// ELIMINAR UNA PELICULA
$app->get('/delete-pelicula/:id', function($id) use($db, $app){
	$sql = 'DELETE FROM peliculas WHERE id = '.$id;
	$query = $db->query($sql);

	if($query){
		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'message' 	=> 'La película se ha eliminado correctamente!!'
		);
	}else{
		$result = array(
			'status' 	=> 'error',
			'code'		=> 404,
			'message' 	=> 'La película no se ha eliminado!!'
		);
	}

	echo json_encode($result);
});

// ACTUALIZAR UNA PELICULA
$app->post('/update-pelicula/:id', function($id) use($db, $app){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	$sql = "UPDATE peliculas SET ".
		   "nombre = '{$data["nombre"]}', ".
		   "descripcion = '{$data["descripcion"]}', ";

	if(isset($data['imagen'])){
 		$sql .= "imagen = '{$data["imagen"]}', ";
	}

	$sql .=	"precio = '{$data["precio"]}' WHERE id = {$id}";


	$query = $db->query($sql);

	if($query){
		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'message' 	=> 'El producto se ha actualizado correctamente!!'
		);
	}else{
		$result = array(
			'status' 	=> 'error',
			'code'		=> 404,
			'message' 	=> 'El producto no se ha actualizado!!'
		);
	}

	echo json_encode($result);

});

// SUBIR UNA IMAGEN A UN PRODUCTO
$app->post('/upload-file', function() use($db, $app){
	$result = array(
		'status' 	=> 'error',
		'code'		=> 404,
		'message' 	=> 'El archivo no ha podido subirse'
	);

	if(isset($_FILES['uploads'])){
		$piramideUploader = new PiramideUploader();

		$upload = $piramideUploader->upload('image', "uploads", "uploads", array('image/jpeg', 'image/png', 'image/gif'));
		$file = $piramideUploader->getInfoFile();
		$file_name = $file['complete_name'];

		if(isset($upload) && $upload["uploaded"] == false){
			$result = array(
				'status' 	=> 'error',
				'code'		=> 404,
				'message' 	=> 'El archivo no ha podido subirse'
			);
		}else{
			$result = array(
				'status' 	=> 'success',
				'code'		=> 200,
				'message' 	=> 'El archivo se ha subido',
				'filename'  => $file_name
			);
		}
	}

	echo json_encode($result);
});

// GUARDAR PRODUCTOS
$app->post('/productos', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	if(!isset($data['nombre'])){
		$data['nombre']=null;
	}

	if(!isset($data['descripcion'])){
		$data['descripcion']=null;
	}

	if(!isset($data['precio'])){
		$data['precio']=null;
	}

	if(!isset($data['imagen'])){
		$data['imagen']=null;
	}

	$query = "INSERT INTO productos VALUES(NULL,".
			 "'{$data['nombre']}',".
			 "'{$data['descripcion']}',".
			 "'{$data['precio']}',".
			 "'{$data['imagen']}'".
			 ");";

	$insert = $db->query($query);

	$result = array(
		'status' => 'error',
		'code'	 => 404,
		'message' => 'Producto NO se ha creado'
	);

	if($insert){
		$result = array(
			'status' => 'success',
			'code'	 => 200,
			'message' => 'Producto creado correctamente'
		);
	}

	echo json_encode($result);
});

$app->run();
