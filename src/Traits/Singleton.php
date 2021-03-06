<?php
declare(strict_types=1);

namespace Cxx\Codetool\Traits;

/**
 * 单例模式
 * 可以通过trait机制快速实现一个单例模式（没有做严格的防止复制功能，但实现了缓存功能）
 */
trait Singleton
{
    protected static $_instance;

    /**
     * 获取单例
     *
     * @return static
     */
    final public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    private function __construct()
    {
        $this->init();
    }

    /**
     * 初始化，需要实现此方法进行第一次初始化
     *
     * @return void
     */
    protected function init()
    {
    }
}
