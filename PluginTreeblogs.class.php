<?php
/* ---------------------------------------------------------------------------
 * @Plugin Name: Treeblogs
 * @Plugin Id: Treeblogs
 * @Plugin URI:
 * @Description: Дерево блогов
 * @Author: mackovey@gmail.com
 * @Author URI: http://stfalcon.com
 * @LiveStreet Version: 0.4.2
 * @License: GNU GPL v2, http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * ----------------------------------------------------------------------------
 */
/**
 * Запрещаем напрямую через браузер обращение к этому файлу.
 */
if (!class_exists('Plugin')) {
    die('Hacking attemp!!');
}

class PluginTreeblogs extends Plugin
{
    /*Переопределяем шаблоны из базовой комплектации*/ 
    protected $aDelegates=array(
            'template'=>array(
                    'topic.tpl', 
                    'topic_list.tpl',
                    'actions/ActionTopic/add.tpl',
                    'block.treeblogs.tpl',
                    'treeblogs-level.tpl',
            ),       
    );
	
    public $aInherits = array(
//        'action' => array(
//            'ActionBlog' => '_ActionBlog'),
        'module' => array(
            'ModuleBlog' => '_ModuleBlog',
            'ModuleTopic' => '_ModuleTopic',
        ),
        'entity' => array(
            'ModuleBlog_EntityBlog' 	=> '_ModuleBlog_EntityBlog',
            'ModuleTopic_EntityTopic' 	=> '_ModuleTopic_EntityTopic',
        ),
        'mapper' => array(
            'ModuleBlog_MapperBlog' 	=> '_ModuleBlog_MapperBlog',
            'ModuleTopic_MapperTopic' 	=> '_ModuleTopic_MapperTopic',
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
