<?php

use think\facade\Route;

Route::group('api', function () {
    
    Route::group('auth', function () {
        Route::post('register', 'api.Auth/register');
        Route::post('login', 'api.Auth/login');
        Route::post('logout', 'api.Auth/logout');
        Route::get('userInfo', 'api.Auth/getUserInfo');
        Route::post('updateUserInfo', 'api.Auth/updateUserInfo');
        Route::post('changePassword', 'api.Auth/changePassword');
        Route::post('bindMobile', 'api.Auth/bindMobile');
    });

    Route::group('user', function () {
        Route::get('index', 'api.User/index');
        Route::get('pointsLog', 'api.User/pointsLog');
        Route::get('balanceLog', 'api.User/balanceLog');
        Route::post('recharge', 'api.User/recharge');
    });

    Route::group('address', function () {
        Route::get('list', 'api.Address/list');
        Route::get('detail', 'api.Address/detail');
        Route::post('create', 'api.Address/create');
        Route::post('update', 'api.Address/update');
        Route::post('delete', 'api.Address/delete');
        Route::post('setDefault', 'api.Address/setDefault');
    });

    Route::group('cart', function () {
        Route::get('list', 'api.Cart/list');
        Route::post('add', 'api.Cart/add');
        Route::post('update', 'api.Cart/update');
        Route::post('delete', 'api.Cart/delete');
        Route::post('clear', 'api.Cart/clear');
        Route::post('selected', 'api.Cart/selected');
        Route::get('getSelectedTotal', 'api.Cart/getSelectedTotal');
    });

    Route::group('order', function () {
        Route::post('create', 'api.Order/create');
        Route::get('list', 'api.Order/list');
        Route::get('detail', 'api.Order/detail');
        Route::post('cancel', 'api.Order/cancel');
        Route::post('pay', 'api.Order/pay');
        Route::post('receive', 'api.Order/receive');
        Route::post('comment', 'api.Order/comment');
        Route::get('getCount', 'api.Order/getCount');
    });

    Route::group('aftersale', function () {
        Route::post('apply', 'api.Aftersale/apply');
        Route::get('detail', 'api.Aftersale/detail');
        Route::get('list', 'api.Aftersale/list');
        Route::post('cancel', 'api.Aftersale/cancel');
        Route::post('returnGoods', 'api.Aftersale/returnGoods');
    });

    Route::group('product', function () {
        Route::get('list', 'api.Product/list');
        Route::get('detail', 'api.Product/detail');
        Route::get('comment', 'api.Product/comment');
        Route::get('category', 'api.Product/category');
        Route::get('categoryTree', 'api.Product/categoryTree');
        Route::get('recommend', 'api.Product/recommend');
        Route::get('newArrival', 'api.Product/newArrival');
    });

    Route::group('shop', function () {
        Route::get('list', 'api.Shop/list');
        Route::get('detail', 'api.Shop/detail');
        Route::get('products', 'api.Shop/products');
        Route::post('apply', 'api.Shop/apply');
        Route::get('info', 'api.Shop/info');
        Route::get('statistics', 'api.Shop/statistics');
    });

    Route::group('coupon', function () {
        Route::get('list', 'api.Coupon/list');
        Route::get('myList', 'api.Coupon/myList');
        Route::post('receive', 'api.Coupon/receive');
        Route::get('available', 'api.Coupon/available');
    });

    Route::get('index', 'api.Index/index');
    Route::get('config', 'api.Index/config');

})->middleware(\app\api\middleware\CorsMiddleware::class);
