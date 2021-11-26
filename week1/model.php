<?php
/**
 * Model
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Check if the route exists
 * @param string $route_uri URI to be matched
 * @param string $request_type Request method
 * @return bool
 *
 */
function new_route($route_uri, $request_type){
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
        return True;
    } else {
        return False;
    }
}
/**
 * Add a serie to the database
 */
function add_series($db, $input_name, $input_creator, $input_seasons, $input_abstract) {
    
    /* Check data type */
    if (!is_numeric($input_seasons)) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }
    /* check if all fields are set*/
    if (
        empty($input_name) or
        empty($input_creator) or
        empty($input_seasons) or
        empty($input_abstract)
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    /* Check if book already exists */
    $stmt = $db->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$input_name]);
    $book = $stmt->rowCount();
    if ($book){
        return [
            'type' => 'danger',
            'message' => 'This book was already added.'
        ];
    }
    /* Add book */
    $stmt = $db->prepare("INSERT INTO series (name, creator, seasons, abstract) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $input_name,
        $input_creator,
        $input_seasons,
        $input_abstract
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Book '%s' added to Books Overview.", $input_name)
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The book was not added. Try it again.'
        ];
    }
}

/**
 * connect to the database
 */
function connect_db($host, $db, $user, $pass)
{
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        echo sprintf("Failed to connect. %s", $e->getMessage());
    }
    return $pdo;
}

/**
 * Count the amount of items in the database
 */
function count_series($db)
{
    $test = $db->prepare("SELECT id FROM series");
    $test->execute();
    $rowcount = $test->rowCount();
    return $rowcount;
}

/**
 * return all series in the database
 */
function get_series($db)
{
    $stmt = $db->prepare('SELECT * FROM series');
    $stmt->execute();
    $books = $stmt->fetchAll();
    $books_exp = Array();
    /* Create array with htmlspecialchars */
    foreach ($books as $key => $value){
        foreach ($value as $user_key => $user_input) {
            $books_exp[$key][$user_key] = htmlspecialchars($user_input);
        }
    }
    return $books_exp;
}

/**
 * transform the serie object into a html table
 */
function get_series_table($series){
    $table_exp = '
    <table class="table table-hover">
    <thead
    <tr>
    <th scope="col">Books</th>
    <th scope="col"></th>
    </tr>
    </thead>
    <tbody';
    foreach($series as $key => $value){
        $table_exp .= '
        <tr>
        <th scope="row">'.$value['name'].'</th>
        <td><a href="/DDWT21/week1/series/?serie_id='.$value['id'].'" role="button" class="btn btn-primary">More info</a></td>
        </tr>
        ';
    }
    $table_exp .= '
    </tbody>
    </table>
    ';
    return $table_exp;
}

/**
 * Get all info on a single serie
 */
function get_series_info($db, $id){
    $serie = $db->prepare('SELECT * FROM series WHERE id = ?');
    $serie->execute([$id]);
    $serie_info = $serie->fetch();
    $serie_info_exp = Array();

    foreach ($serie_info as $key => $value){
        $serie_info_exp[$key] = htmlspecialchars($value);
    }
    return $serie_info_exp;
}

/**
 * Update a serie in the database
 */
function update_series($db, $serie_info) {    
    //echo $serie_id;
    if (
        empty($serie_info['Name']) or
        empty($serie_info['Creator']) or
        empty($serie_info['Seasons']) or
        empty($serie_info['Abstract']) or
        empty($serie_info['inputId'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }
    /* Check data type */
    if (!is_numeric($serie_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Editions.'
        ];
    }
    /* Get current serie name */
    $stmt = $db->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$serie_info['inputId']]);
    $serie = $stmt->fetch();
    $current_name = $serie['name'];

    /* Check if serie already exists */
    $stmt = $db->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$serie_info['Name']]);
    $serie = $stmt->fetch();
    if ($serie_info['Name'] == $serie['name'] and $serie['name'] != $current_name) {
        return [
            'type' => 'danger',
            'message' => sprintf("The name of the book cannot be changed. %s already exists.", $book_info['Name'])
        ];
    }
    /* Update database*/
    $stmt = $db->prepare("UPDATE series SET name = ?, creator = ?, seasons = ?, abstract = ? WHERE id = ?");
    $stmt->execute([
        $serie_info['Name'],
        $serie_info['Creator'],
        $serie_info['Seasons'],
        $serie_info['Abstract'],
        $serie_info['inputId']
    ]);

    $updated = $stmt->rowCount();
    if ($updated == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Book '%s' was edited!", $serie_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'The book was not edited. No changes were detected'
        ];
    }
}

/**
 * remove a series from the database
 */
function remove_series($db, $id) {
    /* Get book info */
    $series_info = get_series_info($db, $id);

    /* Delete book */
    $stmt = $db->prepare("DELETE FROM series WHERE id = ?");
    $stmt->execute([$id]);
    $deleted = $stmt->rowCount();
    if ($deleted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Book '%s' was removed!", $serie_info['name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'An error occurred. The book was not removed.'
        ];
    }
}

/**
 * Creates a new navigation array item using URL and active status
 * @param string $url The URL of the navigation item
 * @param bool $active Set the navigation item to active or inactive
 * @return array
 */
function na($url, $active){
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template Filename of the template without extension
 * @return string
 */
function use_template($template){
    return sprintf("views/%s.php", $template);
}

/**
 * Creates breadcrumbs HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding URL
 * @return string HTML code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '<nav aria-label="breadcrumb">';
    $breadcrumbs_exp .= '<ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]){
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        } else {
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '</ol>';
    $breadcrumbs_exp .= '</nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation bar HTML code using given array
 * @param array $navigation Array with as Key the page name and as Value the corresponding URL
 * @return string HTML code that represents the navigation bar
 */
function get_navigation($navigation){
    $navigation_exp = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
    $navigation_exp .= '<a class="navbar-brand">Series Overview</a>';
    $navigation_exp .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $navigation_exp .= '<span class="navbar-toggler-icon"></span>';
    $navigation_exp .= '</button>';
    $navigation_exp .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $navigation_exp .= '<ul class="navbar-nav mr-auto">';
    foreach ($navigation as $name => $info) {
        if ($info[1]){
            $navigation_exp .= '<li class="nav-item active">';
        } else {
            $navigation_exp .= '<li class="nav-item">';
        }
        $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '</ul>';
    $navigation_exp .= '</div>';
    $navigation_exp .= '</nav>';
    return $navigation_exp;
}

/**
 * Pretty Print Array
 * @param $input
 */
function p_print($input){
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Creates HTML alert code with information about the success or failure
 * @param array $feedback Associative array with keys type and message
 * @return string
 */
function get_error($feedback){
    return '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
}
