<?php
namespace App\Controller\Game;

/**
 *
 */
class GameObject
{
    public static $table;

    public function __construct()
    {
        if (!self::$table) {
            $this->createTable();
        }
    }

    public function createTable()
    {
        self::$table = new \Swoole\Table(1024 * 50);
        self::$table->column('content', \Swoole\Table::TYPE_STRING, 10000);
        self::$table->create();
    }

    public function add($key, $value)
    {
        self::$table->set($key, ['content' => json_encode($value, JSON_UNESCAPED_UNICODE)]);
    }

    public function get($key)
    {
        return json_decode(self::$table->get($key))['content'];
    }

    public function has($key)
    {
        return self::$table->exist($key);
    }

    public function del($key)
    {
        return self::$table->del($key);
    }
}
