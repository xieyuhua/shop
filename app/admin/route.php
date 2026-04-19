<?php

use think\facade\Route;

Route::group('admin', function () {
    
    Route::post('login', 'admin.Index/login');
    Route::post('logout', 'admin.Index/logout');
    Route::get('getAdminInfo', 'admin.Index/getAdminInfo');
    
    Route::get('dashboard', 'admin.Dashboard/index');
    Route::get('chart', 'admin.Dashboard/chart');

    Route::group('user', function () {
        Route::get('list', 'admin.User/list');
        Route::get('detail', 'admin.User/detail');
        Route::post('update', 'admin.User/update');
        Route::post('delete', 'admin.User/delete');
        Route::post('adjustPoints', 'admin.User/adjustPoints');
        Route::post('adjustBalance', 'admin.User/adjustBalance');
        Route::get('export', 'admin.User/export');
    });

    Route::group('category', function () {
        Route::get('list', 'admin.Category/list');
        Route::get('tree', 'admin.Category/tree');
        Route::get('options', 'admin.Category/options');
        Route::get('detail', 'admin.Category/detail');
        Route::post('create', 'admin.Category/create');
        Route::post('update', 'admin.Category/update');
        Route::post('delete', 'admin.Category/delete');
    });

    Route::group('product', function () {
        Route::get('list', 'admin.Product/list');
        Route::get('detail', 'admin.Product/detail');
        Route::post('create', 'admin.Product/create');
        Route::post('update', 'admin.Product/update');
        Route::post('delete', 'admin.Product/delete');
        Route::post('audit', 'admin.Product/audit');
        Route::post('batchUpdate', 'admin.Product/batchUpdate');
    });

    Route::group('shop', function () {
        Route::get('list', 'admin.Shop/list');
        Route::get('detail', 'admin.Shop/detail');
        Route::post('audit', 'admin.Shop/audit');
        Route::post('update', 'admin.Shop/update');
        Route::post('close', 'admin.Shop/close');
        Route::post('open', 'admin.Shop/open');
        Route::get('statistics', 'admin.Shop/statistics');
    });

    Route::group('order', function () {
        Route::get('list', 'admin.Order/list');
        Route::get('detail', 'admin.Order/detail');
        Route::post('delivery', 'admin.Order/delivery');
        Route::post('cancel', 'admin.Order/cancel');
        Route::post('complete', 'admin.Order/complete');
        Route::get('statistics', 'admin.Order/statistics');
        Route::get('export', 'admin.Order/export');
    });

    Route::group('aftersale', function () {
        Route::get('list', 'admin.Aftersale/list');
        Route::get('detail', 'admin.Aftersale/detail');
        Route::post('audit', 'admin.Aftersale/audit');
        Route::post('refund', 'admin.Aftersale/refund');
        Route::get('statistics', 'admin.Aftersale/statistics');
    });

    Route::group('coupon', function () {
        Route::get('list', 'admin.Coupon/list');
        Route::get('detail', 'admin.Coupon/detail');
        Route::post('create', 'admin.Coupon/create');
        Route::post('update', 'admin.Coupon/update');
        Route::post('delete', 'admin.Coupon/delete');
    });

    Route::get('index', 'admin.Index/index');

});
