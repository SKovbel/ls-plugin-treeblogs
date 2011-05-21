<?php

/**
 * Запрещаем напрямую через браузер обращение к этому файлу.
 */
if (!class_exists('Plugin')) {
    die('Hacking attemp!');
}

class PluginTreeblogs extends Plugin
{
    public $aInherits = array(
//        'action' => array(
//            'ActionBlog' => '_ActionBlog'),
        'module' => array(
            'ModuleBlog' => '_ModuleBlog',
            'ModuleTopic' => '_ModuleTopic',
        ),
        'entity' => array(
            'ModuleBlog_EntityBlog' => '_ModuleBlog_EntityBlog',
        ),
        'mapper' => array(
            'ModuleBlog_MapperBlog' => '_ModuleBlog_MapperBlog',
        ),
    );
    /**
     * Активация плагина
     * @return boolean
     */
    public function Activate()
    {
        $resutls = $this->ExportSQL(dirname(__FILE__) . '/activate.sql');
        return $resutls['result'];
    }

    /**
     * Инициализация плагина
     * @return void
     */
    public function Init()
    {

    }

    /**
     * Деактивация плагина
     * @return boolean
     */
    public function Deactivate()
    {
        $resutls = $this->ExportSQL(dirname(__FILE__) . '/deactivate.sql');
        return $resutls['result'];
    }

}
