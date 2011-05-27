var nxtGroup=0;
var treepluginsurl='/plugins/treeblogs/include/ajax/treeblogs.php';

function delGroup(groupidx){
	var group 	= 'g'+groupidx;
    $(group).destroy();
}



function addGroup(){
	var nextGroup 	= parseInt(nxtGroup)+1; 
	var newGroup 	= 'g'+nxtGroup;
	var blogId 	 	= -1;
	var groupIdx	= nxtGroup;
    
   	JsHttpRequest.query(
   	'POST '+DIR_WEB_ROOT+treepluginsurl,
   	{groupIdx:groupIdx, action:'newgroup', security_ls_key: LIVESTREET_SECURITY_KEY },
   	function (result, errors){
   		if (!result.noValue){
   			var temp=new Element('div');
   			temp.set('html',result.select);
   	   	   	$('groups').adopt(temp.childNodes);
   	   	   	nxtGroup++;
   	   	   	doBlogsRequest('level', blogId, newGroup, 0);
   	   	}
   	},true);
}


function removeSelects(group, fromlevel){
	var i = fromlevel;
	while(true){
		if ( $(group).getElement('#'+group+"_"+i) )
			$(group).getElement('#'+group+"_"+i).destroy();
		else
			break;
		i++;
	}
}

function changeBlogSelector(e){
	var blogid		= this.getElement(':selected').get('value');
	var prevLevel	= parseInt(this.name)-1;
	var currLevel	= parseInt(this.name);
	var nextLevel	= parseInt(this.name)+1;
	var group		= this.getParent().id;
	var groupIdx	= parseInt(getGropuIdx(group));
	
	removeSelects(group, nextLevel);
	doBlogsRequest('children', blogid, group, nextLevel);
	
    if (blogid==-1){
        if (prevLevel==-1) { /*first element in second group*/
        	setBlogId(group, -1);
        } else {
        	setBlogId(group, $(group).getElement('#'+group+"_"+prevLevel).get('value'));
        }
    } else {
		setBlogId(group, blogid);
    }
	
}

function populateSelector(select, group, nextLevel){
 	var sel = $(group).getElement('#'+group+"_"+nextLevel);
	if (sel){
		sel.destroy();
	}
    var temp=new Element('div');
    temp.set('html',select);
    $(group).adopt(temp.childNodes);
    sel = $(group).getElement('#'+group+"_"+nextLevel);	
	$(group).getElements('select').addEvent('change', changeBlogSelector);
	var blogid	= sel.get('value');
	if (blogid > 0){
		setBlogId(group, blogid);
	}
}



function doBlogsRequest(action, blogid, group, nextLevel){
   	JsHttpRequest.query(
   	'POST '+DIR_WEB_ROOT+treepluginsurl,
   	{blogid:blogid, action:action, nextlevel:nextLevel, groupid:group, security_ls_key: LIVESTREET_SECURITY_KEY },
   	function (result, errors){
   	   	if (!result.noValue){
   	   		populateSelector(result.select, group, nextLevel);
   	   	} else {
   	   		//removeSelects(group, nextLevel);	
   	   	}
   	},
   	true);
}

function getGropuIdx(group){
	var reg=/\d+/ 
	return parseInt(reg.exec(group));
}

function setBlogId(group, blogid){
	var groupIdx=parseInt(getGropuIdx(group));
	if (groupIdx == 0){
		$(group).getElement('#blog_id').set('value', blogid);
	} else {
		$(group).getElement('#subblog_id_'+groupIdx+'').set('value', blogid);
	}
}
