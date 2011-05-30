<?php

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
		SELECT 
			b.parent_id
		FROM 
			" . Config::Get('db.table.blog') . " as b
		WHERE
			b.blog_id = ?
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
		SELECT 
			b.blog_id
		FROM 
			" . Config::Get('db.table.blog') . " as b
		WHERE 
			b.parent_id = ?
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
	 *
	 * @return array
	 */
	public function GetMenuBlogs()
	{
		$sql = "
		SELECT
			b.blog_id
		FROM
			" . Config::Get('db.table.blog') . " as b
		WHERE
			b.blog_type<>'personal'
			AND b.parent_id IS NULL
				";
		$aBlogs = array();
		if ($aRows = $this->oDb->select($sql)) {
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
		SELECT
			b.blog_id as id, b.blog_title as title, b.parent_id
		FROM
			" . Config::Get('db.table.blog') . " as b
		WHERE
			b.blog_type<>'personal'";
		if (!is_null($blogId)) {
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
		SELECT
			b.blog_id, b.parent_id
		FROM
			" . Config::Get('db.table.blog') . " as b
		WHERE
			b.blog_type<>'personal'";
		$aBlogs = array();
		if ($aRows = $this->oDb->select($sql)) {
			foreach ($aRows as $aRow) {
				$aBlogs[$aRow['blog_id']] = $aRow['parent_id'];
			}
		}
		return $aBlogs;
	}

}
