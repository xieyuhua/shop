#!/bin/bash

# B2B2C Mall 安装脚本

echo "====================================="
echo "  B2B2C 多用户商城系统安装脚本"
echo "====================================="

# 检查PHP
if ! command -v php &> /dev/null; then
    echo "错误: PHP未安装"
    exit 1
fi

echo "PHP版本: $(php -v | head -n1)"

# 检查Composer
if ! command -v composer &> /dev/null; then
    echo "错误: Composer未安装"
    exit 1
fi

echo "Composer版本: $(composer --version)"

# 安装依赖
echo ""
echo "正在安装Composer依赖..."
composer install

if [ $? -ne 0 ]; then
    echo "错误: Composer依赖安装失败"
    exit 1
fi

echo ""
echo "依赖安装完成!"

# 检查目录权限
echo ""
echo "检查目录权限..."
dirs=("runtime" "public/uploads" "extend")

for dir in "${dirs[@]}"; do
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
    fi
    chmod -R 777 "$dir" 2>/dev/null || true
done

echo "目录权限设置完成!"

# 复制环境配置文件
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo ""
        echo "请编辑 .env 文件配置数据库连接"
    fi
fi

echo ""
echo "====================================="
echo "  安装完成!"
echo "====================================="
echo ""
echo "下一步操作:"
echo "1. 创建数据库 (CREATE DATABASE mall_b2b2c)"
echo "2. 编辑 .env 文件配置数据库"
echo "3. 导入 database/install.sql 数据库结构"
echo "4. 启动开发服务器: php think run"
echo ""
