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

/* Create Router instance */
$router = new \Bramus\Router\Router();

// Add routes here

/* Run the router */
$router->run();
