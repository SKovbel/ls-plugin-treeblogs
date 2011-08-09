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
 * Маппер Blog модуля Blog плагина Treeblogs
 */
class PluginTreeblogs_ModuleBlog_MapperBlog extends PluginTreeblogs_Inherit_ModuleBlog_MapperBlog
{

	/**
	 * Возвращаем родительский блог
	 *
	 * @param ModuleBlog_EntityBlog $oBlog
	 * @return int BlogId
	 */	
	public function getParentBlogId($blog_id)
	{
		$sql = "
		SELECT b.parent_id
		  FROM " . Config::Get('db.table.blog') . " as b
		 WHERE b.blog_id = ?
		";
		$aRows = $this->oDb->select($sql, $blog_id );
		if (isset($aRows[0])) {
			return $aRows[0]['parent_id'];
		}
		return null;
	}

	/**
	 * Обновление связи блог-блог
	 *
	 * @param ModuleBlog_EntityBlog $oBlog
	 * @return boolean
	 */
	public function UpdateParentId($oBlog)
	{
		$sql = "
		UPDATE " . Config::Get('db.table.blog') . "
		   SET parent_id = ?
		 WHERE blog_id = ?d
		";
		$this->oDb->query($sql, $oBlog->getParentId(), $oBlog->getId());
		return true;
	}

	/**
	 * Получаем дочерние блоги
	 * @param BlogId
	 * @return array
	 */
	public function GetSubBlogs($BlogId)
	{
		$sql = "
		SELECT b.blog_id
		  FROM " . Config::Get('db.table.blog') . " as b
		 WHERE b.parent_id = ?
		 ORDER BY b.blog_rating DESC
		 LIMIT ?d
		";
		$aBlogs = array();
		if ($aRows = $this->oDb->select($sql, $BlogId, Config::Get('plugin.treeblogs.blogs.count'))) {
			foreach ($aRows as $aBlog) {
				$aBlogs[] = $aBlog['blog_id'];
			}
		}
		return $aBlogs;
	}

	/**
	 * Выбираем блоги для меню
	 * @param int $iUserOwnerId
	 * @return array
	 */
	public function GetMenuBlogs($iUserOwnerId=0)
	{
                $sql ="";
                $aRows = array();
                if ($iUserOwnerId==0) {
                    $sql = "
                    SELECT b.blog_id
                      FROM " . Config::Get('db.table.blog') . " as b
                     WHERE b.parent_id IS NULL
                       AND b.blog_type <> 'personal'
                       AND b.blog_type = 'open'
                    ";
                    $aRows = $this->oDb->select($sql);
                } else {
                    $sql = "
                    SELECT 2 main, b.blog_id
                      FROM " . Config::Get('db.table.blog') . " as b
                     WHERE b.blog_type = 'open' AND b.parent_id IS NULL
                     UNION ALL
                    SELECT 1 main, b.blog_id
                      FROM " . Config::Get('db.table.blog') . " as b
                     WHERE b.blog_type = 'personal' AND b.user_owner_id = ?d
                     ORDER BY main ASC
                    ";
                    $aRows = $this->oDb->select($sql, $iUserOwnerId);
                }
		$aBlogs = array();
		if ($aRows) {
			foreach ($aRows as $aBlog) {
				$aBlogs[] = $aBlog['blog_id'];
			}
		}
		return $aBlogs;
	}

	/**
	 * Возвращаем блоги для выбора
	 *
	 * @param int|null $blogId
	 * @return array
	 */
	public function GetBlogsForSelect($blogId = null)
	{
		$sql = "
		SELECT b.blog_id as id, b.blog_title as title, b.parent_id
		  FROM " . Config::Get('db.table.blog') . " as b
		 WHERE b.blog_type<>'personal' AND b.blog_type = 'open'
		";
		if ( $blogId>0 ) {
			$sql .= 'AND b.blog_id <> ' . $blogId;
		}
		$sql .= " ORDER BY b.blog_title";
		$aBlogs = array();
		if ($aRows = $this->oDb->select($sql)) {
			$aBlogs = $aRows;
		}
		return $aBlogs;
	}

	public function GetBlogRelations()
	{
		$sql = "
		SELECT b.blog_id, b.parent_id
		  FROM " . Config::Get('db.table.blog') . " as b
		 WHERE b.blog_type<>'personal' AND b.blog_type = 'open'
		";
		$aBlogs = array();
		if ($aRows = $this->oDb->select($sql)) {
			foreach ($aRows as $aRow) {
				$aBlogs[$aRow['blog_id']] = $aRow['parent_id'];
			}
		}
		return $aBlogs;
	}

}
