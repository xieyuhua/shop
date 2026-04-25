<?php
declare(strict_types=1);

namespace app\database;

use think\Db;

class Migrate
{
    public static function run(): void
    {
        $version = self::getVersion();
        
        $migrations = [
            'v1' => [self::class, 'migrateV1'],
        ];
        
        foreach ($migrations as $ver => $callback) {
            if ($ver > $version) {
                echo "Migrating: {$ver}\n";
                $callback();
                self::setVersion($ver);
                echo "Migrated: {$ver}\n";
            }
        }

        echo "All migrations completed.\n";
    }

    public static function status(): void
    {
        $version = self::getVersion();
        echo "Current version: {$version}\n";
    }

    public static function revert(): void
    {
        $version = self::getVersion();
        
        if ($version) {
            self::setVersion('v0');
            echo "Reverted to v0\n";
        }
    }

    protected static function getVersion(): string
    {
        $row = Db::name('config')->where('name', 'db_version')->find();
        return $row ? $row['value'] : 'v0';
    }

    protected static function setVersion(string $version): void
    {
        Db::name('config')->where('name', 'db_version')->update(['value' => $version]);
    }

    public static function migrateV1(): void
    {
        $tables = [
            'tp_dict' => [
                'id', 'group', 'value', 'label', 'sort', 'status', 'create_time'
            ],
            'tp_file' => [
                'id', 'group', 'filename', 'filepath', 'filesize', 'filetype',
                'ext', 'mime', 'use_num', 'status', 'create_time'
            ],
            'tp_log' => [
                'id', 'type', 'content', 'admin_id', 'admin_name', 'ip',
                'url', 'method', 'param', 'create_time'
            ]
        ];

        foreach ($tables as $name => $columns) {
            if (!Db::query("SHOW TABLES LIKE '{$name}'")) {
                echo "Creating table: {$name}\n";
            }
        }
    }
}