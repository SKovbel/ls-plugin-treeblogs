<?php

/**
 * Маппер Topic модуля Topic плагина Treeblogs
 */
class PluginTreeblogs_ModuleTopic_MapperTopic extends PluginTreeblogs_Inherit_ModuleTopic_MapperTopic
{
	
	public function GetTopicSubBlogs($topicid) 
    {
        $sql = "
        	SELECT
				a.blog_id
			FROM
				" . Config::Get('db.table.topic_blog') . " a
			WHERE
					a.topic_id = ?
            ORDER BY a.blog_id ASC 
				";
         
        $aBlogs = array();
        if ($aRows = $this->oDb->select($sql, $topicid)) {
            foreach ($aRows as $aBlog) {
                $aBlogs[] = $aBlog['blog_id'];
            }
        }
        return $aBlogs;
        
    }
	
    public function DeleteTopicFromSubBlog($blog_id, $topic_id) 
    {
        $sql = "
        	DELETE 
        	  FROM " . Config::Get('db.table.topic_blog') . "
			WHERE blog_id = ?d AND topic_id = ?d
			";
        $this->oDb->query($sql, $blog_id, $topic_id);
        return true;
    }
    
	
    public function AddTopicToSubBlog($blog_id, $topic_id) 
    {
        $sql = "
        	INSERT
        	  INTO " . Config::Get('db.table.topic_blog') . "
			(`blog_id`, `topic_id`)
			VALUES (?d, ?d)
			";
        $this->oDb->query($sql, $blog_id, $topic_id);
        return true;
    }
    
    
    
	protected function buildFilter2($aFilter) {
 		
		$sWhere='';
		if (isset($aFilter['topic_publish'])) {
			$sWhere.=" AND t.topic_publish =  ".(int)$aFilter['topic_publish'];
		}	
		if (isset($aFilter['topic_rating']) and is_array($aFilter['topic_rating'])) {
			$sPublishIndex='';
			if (isset($aFilter['topic_rating']['publish_index']) and $aFilter['topic_rating']['publish_index']==1) {
				$sPublishIndex=" or topic_publish_index=1 ";
			}
			if ($aFilter['topic_rating']['type']=='top') {
				$sWhere.=" AND ( t.topic_rating >= ".(float)$aFilter['topic_rating']['value']." {$sPublishIndex} ) ";
			} else {
				$sWhere.=" AND ( t.topic_rating < ".(float)$aFilter['topic_rating']['value']."  ) ";
			}			
		}
		if (isset($aFilter['topic_new'])) {
			$sWhere.=" AND t.topic_date_add >=  '".$aFilter['topic_new']."'";
		}
		if (isset($aFilter['user_id'])) {
			$sWhere.=is_array($aFilter['user_id'])
				? " AND t.user_id IN(".implode(', ',$aFilter['user_id']).")"
				: " AND t.user_id =  ".(int)$aFilter['user_id'];
		}
		if (isset($aFilter['blog_id'])) {
			if(!is_array($aFilter['blog_id'])) {
				$aFilter['blog_id']=array($aFilter['blog_id']);
			}
			$sWhere.=" AND tb.blog_id IN ('".join("','",$aFilter['blog_id'])."')";
		}
		if (isset($aFilter['blog_type']) and is_array($aFilter['blog_type'])) {
			$aBlogTypes = array();
			foreach ($aFilter['blog_type'] as $sType=>$aBlogId) {
				/**
				 * Позиция вида 'type'=>array('id1', 'id2')
				 */
				if(!is_array($aBlogId) && is_string($sType)){
					$aBlogId=array($aBlogId);
				}
				/**
				 * Позиция вида 'type'
				 */
				if(is_string($aBlogId) && is_int($sType)) {
					$sType=$aBlogId;
					$aBlogId=array();
				}
				
				$aBlogTypes[] = (count($aBlogId)==0) 
					? "(b.blog_type='".$sType."')"
					: "(b.blog_type='".$sType."' AND t.blog_id IN ('".join("','",$aBlogId)."'))";
			}
			$sWhere.=" AND (".join(" OR ",(array)$aBlogTypes).")";
		}
		return $sWhere;
	}
	
	public function GetTopics($aFilter,&$iCount,$iCurrPage,$iPerPage) {
		$sWhere=$this->buildFilter($aFilter);
		$sWhere2=$this->buildFilter2($aFilter);
		
		if(isset($aFilter['order']) and !is_array($aFilter['order'])) {
			$aFilter['order'] = array($aFilter['order']);
		} else {
			$aFilter['order'] = array('topic_date_add desc');
		}
		$sql = "
				SELECT 
						t.topic_id, t.topic_date_add							
					FROM 
						".Config::Get('db.table.topic')." as t,	
						".Config::Get('db.table.blog')." as b			
					WHERE 
						1=1					
						".$sWhere."
						AND
						t.blog_id=b.blog_id				
				UNION
				SELECT 
						t.topic_id, t.topic_date_add					
					FROM 
						".Config::Get('db.table.topic')." as t,	
						".Config::Get('db.table.blog')." as b,			
						".Config::Get('db.table.topic_blog')." as tb			
					WHERE 
						1=1					
						".$sWhere2."
						AND tb.blog_id=b.blog_id				
						and t.topic_id=tb.topic_id				
					ORDER BY ".implode(', ', $aFilter['order']) ."
					LIMIT ?d, ?d";		
				//echo $sql;
				$aTopics=array();
		if ($aRows=$this->oDb->selectPage($iCount,$sql,($iCurrPage-1)*$iPerPage, $iPerPage)) {			
			foreach ($aRows as $aTopic) {
				$aTopics[]=$aTopic['topic_id'];
			}
		}				
		return $aTopics;
	}
    
    
}
