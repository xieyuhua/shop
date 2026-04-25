<?php
use think\facade\Route;

Route::group('admin', function () {
    Route::get('login', 'admin.Login/index');
    Route::post('login/login', 'admin.Login/login');
    Route::get('logout', 'admin.Login/logout');
    
    Route::get('/', 'admin.Index/index');
    Route::get('index', 'admin.Index/index');
    Route::get('index/dashboard', 'admin.Index/dashboard');
    
    Route::get('user', 'admin.User/index');
    Route::get('user/add', 'admin.User/add');
    Route::post('user/save', 'admin.User/save');
    Route::get('user/edit', 'admin.User/edit');
    Route::post('user/update', 'admin.User/update');
    Route::post('user/delete', 'admin.User/delete');
    Route::post('user/status', 'admin.User/status');
    
    Route::get('product', 'admin.Product/index');
    Route::get('product/add', 'admin.Product/add');
    Route::post('product/save', 'admin.Product/save');
    Route::get('product/edit', 'admin.Product/edit');
    Route::post('product/update', 'admin.Product/update');
    Route::post('product/delete', 'admin.Product/delete');
    Route::post('product/status', 'admin.Product/status');
    Route::post('product/recommend', 'admin.Product/recommend');
    
    Route::get('category', 'admin.Category/index');
    Route::get('category/add', 'admin.Category/add');
    Route::post('category/save', 'admin.Category/save');
    Route::get('category/edit', 'admin.Category/edit');
    Route::post('category/update', 'admin.Category/update');
    Route::post('category/delete', 'admin.Category/delete');
    Route::post('category/status', 'admin.Category/status');
    
    Route::get('order', 'admin.Order/index');
    Route::get('order/detail', 'admin.Order/detail');
    Route::post('order/ship', 'admin.Order/ship');
    Route::post('order/cancel', 'admin.Order/cancel');
    Route::post('order/refund', 'admin.Order/refund');
    Route::post('order/complete', 'admin.Order/complete');
    
    Route::get('statistics', 'admin.Statistics/index');
    Route::get('statistics/chart', 'admin.Statistics/chart');
    Route::get('statistics/export', 'admin.Statistics/export');
    
    Route::get('config', 'admin.Config/index');
    Route::post('config/save', 'admin.Config/save');
})->middleware(\app\middleware\SessionInit::class);