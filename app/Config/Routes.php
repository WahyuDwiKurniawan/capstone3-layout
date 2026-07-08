<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::login');
$routes->get('logout', 'AuthController::logout');

$routes->group('produk', ['filter' => 'auth'], function ($routes) { 
    $routes->get('', 'ProdukController::index');
    $routes->post('', 'ProdukController::create');
    $routes->post('edit/(:any)', 'ProdukController::edit/$1');
    $routes->get('delete/(:any)', 'ProdukController::delete/$1');
    $routes->get('download', 'ProdukController::download');
});


$routes->group('keranjang', ['filter' => 'auth'], function ($routes) {
    //Rute ini digunakan untuk menampilkan isi keranjang belanja
    $routes->get('', 'TransaksiController::index');

    //Rute ini digunakan untuk menambah produk ke keranjang belanja
    $routes->post('', 'TransaksiController::cart_add');

    //Rute ini digunakan untuk mengubah jumlah produk pada keranjang belanja
    $routes->post('edit', 'TransaksiController::cart_edit');

    //Rute ini digunakan untuk menghapus produk dari keranjang belanja
    $routes->get('delete/(:any)', 'TransaksiController::cart_delete/$1');

    //Rute ini digunakan untuk mengosongkan keranjang belanja
    $routes->get('clear', 'TransaksiController::cart_clear');
    });
    
$routes->get('checkout', 'TransaksiController::checkout', ['filter' => 'auth']);
$routes->post('buy', 'TransaksiController::buy', ['filter' => 'auth']);
$routes->get('history', 'TransaksiController::history', ['filter' => 'auth']);

$routes->get('ajax/destinations','TransaksiController::destinations', ['filter' => 'auth']);
$routes->get('ajax/costs','TransaksiController::costs', ['filter' => 'auth']);
$routes->get('ajax/rincian','TransaksiController::hitungRincian', ['filter' => 'auth']);

$routes->resource('api/products', ['controller' => 'Api\ProdukController']);

$routes->get('api/transactions', 'Api\TransaksiController::index');


$routes->get('profile', 'ProfileController::index', ['filter' => 'auth']);