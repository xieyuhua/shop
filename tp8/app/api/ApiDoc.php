<?php
declare(strict_types=1);

/**
 * @OA\Info(
 *     title="Mall API",
 *     version="1.0",
 *     description="商城后台API接口文档"
 * )
 *
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

/**
 * @OA\Post(
 *     path="/admin/login",
 *     summary="管理员登录",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"username", "password"},
 *             @OA\Property(property="username", type="string", description="用户名"),
 *             @OA\Property(property="password", type="string", description="密码")
 *         )
 *     ),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Get(
 *     path="/admin/user",
 *     summary="获取用户列表",
 *     tags={"User"},
 *     security={"bearerAuth": {}},
 *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="keyword", in="query", @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Post(
 *     path="/admin/user",
 *     summary="添加用户",
 *     tags={"User"},
 *     security={"bearerAuth": {}},
 *     @OA\RequestBody(required=true),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Get(
 *     path="/admin/product",
 *     summary="获取商品列表",
 *     tags={"Product"},
 *     security={"bearerAuth": {}},
 *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="keyword", in="query", @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Post(
 *     path="/admin/product",
 *     summary="添加商品",
 *     tags={"Product"},
 *     security={"bearerAuth": {}},
 *     @OA\RequestBody(required=true),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Get(
 *     path="/admin/order",
 *     summary="获取订单列表",
 *     tags={"Order"},
 *     security={"bearerAuth": {}},
 *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="status", in="query", @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Post(
 *     path="/admin/order/ship",
 *     summary="订单发货",
 *     tags={"Order"},
 *     security={"bearerAuth": {}},
 *     @OA\RequestBody(required=true),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Get(
 *     path="/admin/statistics",
 *     summary="获取统计数据",
 *     tags={"Statistics"},
 *     security={"bearerAuth": {}},
 *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string"), description="today/7days/30days"),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Get(
 *     path="/admin/file",
 *     summary="获取文件列表",
 *     tags={"File"},
 *     security={"bearerAuth": {}},
 *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Post(
 *     path="/admin/file/upload",
 *     summary="上传文件",
 *     tags={"File"},
 *     security={"bearerAuth": {}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="file", type="string", format="binary")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Get(
 *     path="/admin/dict",
 *     summary="获取数据字典",
 *     tags={"System"},
 *     security={"bearerAuth": {}},
 *     @OA\Parameter(name="group", in="query", @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Get(
 *     path="/admin/config",
 *     summary="获取系统配置",
 *     tags={"System"},
 *     security={"bearerAuth": {}},
 *     @OA\Parameter(name="group", in="query", @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="成功")
 * )
 *
 * @OA\Post(
 *     path="/admin/config",
 *     summary="保存系统配置",
 *     tags={"System"},
 *     security={"bearerAuth": {}},
 *     @OA\RequestBody(required=true),
 *     @OA\Response(response=200, description="成功")
 * )
 */
class ApiDoc
{
}