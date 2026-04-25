<?php
use think\facade\Route;

Route::post('admin/login', 'api.Login/login');
Route::post('admin/logout', 'api.Login/logout');
Route::get('admin/user/info', 'api.Login/info');

Route::group('api', function () {
    Route::get('admin/dict', 'api.Dict/index');
    
    Route::get('admin/user', 'api.User/index');
    Route::post('admin/user', 'api.User/save');
    Route::put('admin/user', 'api.User/update');
    Route::delete('admin/user/:id', 'api.User/delete');
    
    Route::get('admin/product', 'api.Product/index');
    Route::post('admin/product', 'api.Product/save');
    Route::put('admin/product', 'api.Product/update');
    Route::delete('admin/product/:id', 'api.Product/delete');
    
    Route::get('admin/category', 'api.Category/index');
    Route::post('admin/category', 'api.Category/save');
    Route::put('admin/category', 'api.Category/update');
    Route::delete('admin/category/:id', 'api.Category/delete');
    
    Route::get('admin/order', 'api.Order/index');
    Route::get('admin/order/:id', 'api.Order/detail');
    Route::post('admin/order/ship', 'api.Order/ship');
    Route::post('admin/order/:id/cancel', 'api.Order/cancel');
    Route::post('admin/order/:id/refund', 'api.Order/refund');
    
    Route::get('admin/statistics', 'api.Statistics/index');
    Route::get('admin/statistics/chart', 'api.Statistics/chart');
    
    Route::get('admin/config', 'api.Config/index');
    Route::post('admin/config', 'api.Config/save');
    
    Route::post('admin/file/upload', 'api.File/upload');
    Route::get('admin/file', 'api.File/index');
    Route::delete('admin/file/:id', 'api.File/delete');
})->middleware(\app\api\middleware\AuthMiddleware::class);