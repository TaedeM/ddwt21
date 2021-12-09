<?php
/**
 * Controller
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt21_week2', 'ddwt21','ddwt21');

/* Redundant code cleanup */
$nbr_series = count_series($db);
$nbr_users = count_users($db);
$right_column = use_template('cards');
$navigation_list = [
    '1' => ['name' => 'Home', 'url' => '/DDWT21/week2/'],
    '2' => ['name' => 'Overview', 'url' => '/DDWT21/week2/overview/'],
    '3' => ['name' => 'My Account', 'url' => '/DDWT21/week2/myaccount/'],
    '4' => ['name' => 'Add Series', 'url' => '/DDWT21/week2/add/'],
    '5' => ['name' => 'Registration', 'url' => '/DDWT21/week2/register/'],
    '6' => ['name' => 'Log in', 'url' => '/DDWT21/week2/login']];
/* Landing page */
if (new_route('/DDWT21/week2/', 'get')) {
    /* Page info */
    $page_title = 'Home';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Home' => na('/DDWT21/week2/', True)
    ]);
    $navigation = get_navigation($navigation_list, '1');
    if (isset($_GET['error_msg'])) {$error_msg = get_error($_GET['error_msg']); };

    /* Page content */
    $page_subtitle = 'The online platform to list your favorite series';
    $page_content = 'On Series Overview you can list your favorite series. You can see the favorite series of all Series Overview users. By sharing your favorite series, you can get inspired by others and explore new series.';

    /* Choose Template */
    include use_template('main');
}

/* Overview page */
elseif (new_route('/DDWT21/week2/overview/', 'get')) {
    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Overview' => na('/DDWT21/week2/overview', True)
    ]);
    $navigation = get_navigation($navigation_list, '2');
    if (isset($_GET['error_msg'])) {$error_msg = get_error($_GET['error_msg']); };
    /* Page content */
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $left_content = get_series_table($db, get_series($db));

    /* Choose Template */
    include use_template('main');
}

/* Single Series */
elseif (new_route('/DDWT21/week2/series/', 'get')) {
    /* Get series from db */
    $series_id = $_GET['series_id'];
    $series_info = get_series_info($db, $series_id);

    /* Page info */
    $page_title = $series_info['name'];
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Overview' => na('/DDWT21/week2/overview/', False),
        $series_info['name'] => na('/DDWT21/week2/series/?series_id='.$series_id, True)
    ]);
    $navigation = get_navigation($navigation_list, '2');
    $display_buttons = false;
    /* Check if logged in user is uploader of the series */
    session_start();
    if (isset($_SESSION['user_id']) and $series_info['user'] == $_SESSION['user_id']) {
        $display_buttons = true;
    } 

    /* Page content */
    $page_subtitle = sprintf("Information about %s", $series_info['name']);
    $page_content = $series_info['abstract'];
    $nbr_seasons = $series_info['seasons'];
    $creators = $series_info['creator'];
    $usr = get_user_name($db, $series_info['user']);
    $added_by = $usr['firstname'].' '.$usr['lastname'];
    /* Choose Template */
    include use_template('series');
}

/* Add series GET */
elseif (new_route('/DDWT21/week2/add/', 'get')) {
    /* Check if logged in */
    if (!check_login() ) {
        redirect('/DDWT21/week2/login/');
    }
    /* Page info */
    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Add Series' => na('/DDWT21/week2/new/', True)
    ]);
    $navigation = get_navigation($navigation_list, '4');
    if (isset($_GET['error_msg'])) {$error_msg = get_error($_GET['error_msg']); };

    /* Page content */
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    $submit_btn = "Add Series";
    $form_action = '/DDWT21/week2/add/';

    /* Choose Template */
    include use_template('new');
}

/* Add series POST */
elseif (new_route('/DDWT21/week2/add/', 'post')) {
    /* Check if logged in */
    if (!check_login() ) {
        redirect('/DDWT21/week2/login/');
    }
    $feedback = add_series($db, $_POST);
    redirect(sprintf('/DDWT21/week2/add/?error_msg=%s',urlencode(json_encode($feedback))));
}

/* Edit series GET */
elseif (new_route('/DDWT21/week2/edit/', 'get')) {
    /* Check if logged in */
    if (!check_login() ) {
        redirect('/DDWT21/week2/login/');
    }

    /* Get series info from db */
    $series_id = $_GET['series_id'];
    $series_info = get_series_info($db, $series_id);
    if (isset($_GET['error_msg'])) {$error_msg = get_error($_GET['error_msg']); };

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        sprintf("Edit Series %s", $series_info['name']) => na('/DDWT21/week2/new/', True)
    ]);
    $navigation = get_navigation($navigation_list, '-1');

    /* Page content */
    $page_subtitle = sprintf("Edit %s", $series_info['name']);
    $page_content = 'Edit the series below.';
    $submit_btn = "Edit Series";
    $form_action = '/DDWT21/week2/edit/';

    /* Choose Template */
    include use_template('new');
}

/* Edit series POST */
elseif (new_route('/DDWT21/week2/edit/', 'post')) {
    /* Check if logged in */
    if (!check_login() ) {
        redirect('/DDWT21/week2/login/');
    }

    /* Update series in database */
    $feedback = update_series($db, $_POST);
    /* Get series info from db */
    $series_id = $_POST['series_id'];
    $series_info = get_series_info($db, $series_id);

    redirect(sprintf('/DDWT21/week2/edit/?series_id='.$series_id.'&error_msg=%s',urlencode(json_encode($feedback))));
}

/* Remove series */
elseif (new_route('/DDWT21/week2/remove/', 'post')) {
    /* Check if logged in */
    if (!check_login() ) {
        redirect('/DDWT21/week2/login/');
    }

    /* Remove series in database */
    $series_id = $_POST['series_id'];
    $feedback = remove_series($db, $series_id);
    redirect(sprintf('/DDWT21/week2/overview/?error_msg=%s',urlencode(json_encode($feedback))));
}

elseif (new_route('/DDWT21/week2/myaccount', 'get')) {
    /* Check if logged in */
    if (!check_login() ) {
        redirect('/DDWT21/week2/login/');
    }
    $page_title = 'My account';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'My account' => na('/DDWT21/week2/myaccount/', True)
    ]);
    $navigation = get_navigation($navigation_list, '3');
    if (isset($_GET['error_msg'])) {$error_msg = get_error($_GET['error_msg']); };

    /* Page content */
    $user = get_user_name($db, $_SESSION['user_id'])['firstname'].' '.get_user_name($db, $_SESSION['user_id'])['lastname'];
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    include use_template('account');
}

elseif (new_route('/DDWT21/week2/register', 'get')) {
    if (check_login() ) {
        redirect('/DDWT21/week2/myaccount/');
    }

    $page_title = 'Register account';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Register' => na('/DDWT21/week2/register/', True)
    ]);
    $navigation = get_navigation($navigation_list, '5');
    if (isset($_GET['error_msg'])) {$error_msg = get_error($_GET['error_msg']); };

    /* Page content */
    $page_subtitle = 'Register your account';
    
    /* Choose Template */
    include use_template('register');
}

elseif (new_route('/DDWT21/week2/register', 'post')) {
    /* Register user */
    $feedback = register_user($db, $_POST);
    
    if($feedback['type'] == 'error') {
        /* Redirect to register form */
        redirect(sprintf('/DDWT21/week2/register/?error_msg=%s', json_encode($feedback)));
    } else {
        /* Redirect to My Account page */
        redirect(sprintf('/DDWT21/week2/myaccount/?error_msg=%s', json_encode($feedback)));
    }
}

/* Login GET */
elseif (new_route('/DDWT21/week2/login/', 'get')){
    /* Check if logged in */
    if (check_login() ) {
        redirect('/DDWT21/week2/myaccount/');
    }

    /* Page info */
    $page_title = 'Login';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Login' => na('/DDWT21/week2/login/', True)
    ]);
    $navigation = get_navigation($navigation_list, '6');
    if (isset($_GET['error_msg'])) {$error_msg = get_error($_GET['error_msg']); };

    /* Page content */
    $page_subtitle = 'Use your username and password to login';
    
    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) { $error_msg = get_error($_GET['error_msg']); }
    
    /* Choose Template */
    include use_template('login');
}

elseif (new_route('/DDWT21/week2/login', 'post')) {
    $feedback = login_user($db, $_POST);
    if($feedback['type'] == 'error') {
        /* Redirect to register form */
        redirect(sprintf('/DDWT21/week2/login/?error_msg=%s', json_encode($feedback)));
    } else {
        /* Redirect to My Account page */
        redirect(sprintf('/DDWT21/week2/myaccount/?error_msg=%s', json_encode($feedback)));
    }

}

elseif (new_route('/DDWT21/week2/logout', 'get')) {
    $feedback = logout_user();
    redirect(sprintf('/DDWT21/week2/?error_msg=%s', json_encode($feedback)));
}

else {
    http_response_code(404);
    echo '404 Not Found';
}
