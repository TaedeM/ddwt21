<?php
/**
 * Controller
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt21_week3', 'ddwt21', 'ddwt21');

// Cred ja
$cred = set_cred('ddwt21', 'ddwt21');

/* Create Router instance */
$router = new \Bramus\Router\Router();

// Add routes here

$router->mount('/api', function() use ($router, $db, $cred) {
    http_content_type('application/json');
    $router->before('GET|POST|PUT|DELETE', '/api/.*', function() use($cred) {
        if (!check_cred($cred)){
            $feedback = [
                'type' => 'danger',
                'message' => 'Authentication failed. Please check the credentials.'
            ];
            echo json_encode($feedback, JSON_PRETTY_PRINT);
            exit();
        }
    });

    // will result in '/movies/'
    $router->get('/', function() {
        echo 'api overview /';
    });

    /* GET for reading all series */
    $router->get('/series/', function() use ($cred, $db){
        if (!check_cred($cred)){
            echo json_encode('Authentication required.');
            http_response_code(401);
            die();
        }
        $series = get_series($db);
        echo json_encode($series, JSON_PRETTY_PRINT);
    });

    /* GET for reading individual series */
    $router->get('/series/(\d+)', function($id) use($db) {
        $series = get_series_info($db, $id);
        echo json_encode($series, JSON_PRETTY_PRINT);
    });

    $router->delete('/series/(\d+)', function($id) use($db) {
        $remove = remove_series($db, $id);
        echo json_encode($remove, JSON_PRETTY_PRINT);
    });

    $router->post('/series', function() use ($db) {
        $add = add_series($db, $_POST);
        echo json_encode($add, JSON_PRETTY_PRINT);
    });

    $router->put('/series/(\d+)', function($id) use ($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        $serie_info = $_PUT + ["serie_id" => $id];
        $add = json_encode(update_series($db, $serie_info), JSON_PRETTY_PRINT);
        echo $add;
    });
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    $feedback = [
        'type' => 'danger',
        'message' => 'Error 404, this webpage was not found.'
    ];
    echo json_encode($feedback, JSON_PRETTY_PRINT);
});


/* Run the router */
$router->run();
