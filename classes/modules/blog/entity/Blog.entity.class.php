<?php

class PluginTreeblogs_ModuleBlog_EntityBlog extends PluginTreeblogs_Inherit_ModuleBlog_EntityBlog
{

	public function getParentId()
	{
		if (isset($this->_aData['parent_id']) && strlen($this->_aData['parent_id'])) {
			return $this->_aData['parent_id'];
		}
		return null;
	}

	public function setParentId($data)
	{
		if ($data == 0) {
			$this->_aData['parent_id'] = null;
		} else {
			$this->_aData['parent_id'] = $data;
		}
	}

}
