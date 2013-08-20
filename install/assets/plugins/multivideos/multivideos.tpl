//<?php
/**
 * MultiVideos
 * 
 * Добавление видео-галереи к странице
 *
 * @category 	plugin
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author      Pathologic (maxx@np.by)
 * @internal	@properties &tvIds=TV Ids;text;&templ=Template;text;&role=Role;text; &w=Preview: width;text; &h=Preview: height;text;120 &thumbUrl=Thumbs folder;text;assets/images/video/
 * @internal	@events OnDocFormRender,OnBeforeDocFormSave
 * @internal    @installset base
 * @internal    @legacy_names MultiVideos
 */
 
//defined('IN_MANAGER_MODE') or die();

global $content,$default_template,$tmplvars;
$tvIds = isset($tvIds) ? $tvIds : 1;
$templ = isset($templ) ? explode(',',$templ) : false;
$role = isset($role) ? explode(',',$role) : false;
$style = (isset($w) || isset($h)) ? "'max-width':'{$w}px','max-height':'{$h}px','cursor':'pointer'" : '';
$site = $modx->config['site_url'];
$cur_templ = isset($_POST['template']) ? $_POST['template'] : (isset($content['template']) ? $content['template'] : $default_template);
$cur_role = $_SESSION['mgrRole'];
$thumbUrl = isset($thumbUrl) ? $thumbUrl : 'assets/images/';
$site_url = $modx->config['site_url'];
if (($templ && !in_array($cur_templ,$templ)) || ($role && !in_array($cur_role,$role))) return;

$lang['insert']='Вставить';
$lang['link']='Превью:';
$lang['title']='Название:';
$lang['video']='Ссылка YouTube, Vimeo, Rutube';

$e = &$modx->Event;
if ($e->name == 'OnDocFormRender') {
require_once(MODX_MANAGER_PATH.'includes/tmplvars.inc.php');
$modx_script = renderFormElement('image',0,'','','');
preg_match('/(<script[^>]*?>.*?<\/script>)/si', $modx_script, $matches);
$output = $matches[0];
$output .= <<< OUT
<!-- MultiVideos -->
<style type="text/css">
.videoitem {border:1px solid #e3e3e3; margin:0 0 5px; padding:2px 5px 5px 5px; position:relative; overflow:hidden; white-space:nowrap; zoom:1}
.videoitem span {display:inline-block; padding-top:3px;}
.videoitem input {line-height:1.1; vertical-align:middle;}
.videoimg {position:absolute; right:0; padding-top:3px;}
</style>
<script type="text/javascript">
window.ie9=window.XDomainRequest && window.performance; window.ie=window.ie && !window.ie9; /* IE9 patch */
var MultiVideos = new Class({
        initialize: function(fid){
                this.name = fid;
                this.fid = $(fid);
                var hpArr = (this.fid.value && this.fid.value!='[]') ? Json.evaluate(this.fid.value) : [null];
                this.fid.setStyle('display','none');
		this.box = new Element('div',{'class':'videoEditor'});
		this.fid.getParent().adopt(this.box);
		this.lastvideo='';
		this.video=0;
		for (var f=0;f<hpArr.length;f++) this.addItem(hpArr[f]);
		if (typeof(SetUrl) != 'undefined') {
			this.OrigSetUrl = SetUrl;				
			SetUrl = function(url, width, height, alt) {
				this.OrigSetUrl(url, width, height, alt);
				if ($(this.lastvideo)!=null) $(this.lastvideo).fireEvent('change');
			}.bind(this)
		}
		this.sort=new Sortables(this.box,{
			onStart: function(el){el.setStyles({'background':'#f0f0f0','opacity':1});},
			onComplete: function(el){el.setStyle('background','none');this.setEditor();}.bind(this)
		});	
		this.box.getElements('div.videoitem').setStyle('cursor','move');
		this.box.getElements('input[type=text]').addEvent('click',function(){this.focus();});
	},
	br: function(){return new Element('br');},
	sp: function(text){return new Element('span').setText(text);},
	addItem: function(values,elem){
		this.video++;
		var f = this.video;
		var rowDiv = new Element('div',{'class':'videoitem'});
		if (elem) {rowDiv.injectAfter(elem);} else {this.box.adopt(rowDiv);}
		if (!values) values=['','','']; 
		var linkURL = new Element('input',{'type':'text','name':'link_'+this.name+'_'+f,'id':'link_'+this.name+'_'+f,'class':'imageField','value':values[1],'events':{
			'change':function(){
        var url = linkURL.value;
				var imgDiv=$('video_'+this.name+'_'+f+'_'+'PrContainer');
				if (imgDiv!=null) imgDiv.remove();
				if (url != "") {
					new Element('div',{'class':'videoimg','id':'video_'+this.name+'_'+f+'_'+'PrContainer','styles':{'width':'{$w}px'}}).injectTop(rowDiv).adopt(
						new Element('img',{'src':'{$site_url}'+url,'styles':{ $style }})
					);
				}
this.setEditor();}.bind(this)
		}});
		var bInsertLink = new Element('input',{'type':'button','value':'{$lang['insert']}','events':{
			'click':function(){this.lastvideo='link_'+this.name+'_'+f; BrowseServer('link_'+this.name+'_'+f)}.bind(this)
		}});
		var imgName = new Element('input',{'type':'text','class':'imageField','value':values[2],'events':{
			'keyup':function(){this.setEditor();documentDirty=true;}.bind(this)
		}});
    var vidURL = new Element('input',{'type':'text','class':'videoField','value':values[0],'events':{
			'keyup':function(){this.setEditor();documentDirty=true;}.bind(this)
		}});
		var bAdd = new Element('input',{'type':'button','value':'+','events': {
			'click':function(){this.addItem(null,rowDiv);}.bind(this)
		}});
		rowDiv.adopt(this.sp('{$lang['video']}'),this.br(),vidURL,this.br());
    rowDiv.adopt(this.sp('{$lang['link']}'),this.br(),linkURL,bInsertLink,this.br());
		rowDiv.adopt(this.sp('{$lang['title']}'),this.br(),imgName,bAdd);
		rowDiv.adopt(new Element('input',{'type':'button','value':'-','events':{
			'click':function(){if (this.box.getElements('div.videoitem').length>1) rowDiv.remove();this.setEditor();}.bind(this)
		}}));
		linkURL.fireEvent('change');
	},
	setEditor: function(){
var hpArr=new Array();
		this.box.getElements('div.videoitem').each(function(item){
			var itemsArr=new Array();
			var inputs=item.getElements('input[type=text]');
			var noempty=false;
			inputs.each(function(item){itemsArr.push(item.value); if (item.value) noempty=true;});
			if (noempty) hpArr.push(itemsArr);
		});
		this.fid.value = Json.toString(hpArr);
	}
});
window.addEvent('domready', function(){
	var tvIds = [$tvIds];
	for (var i=0;i<tvIds.length;i++){
		var fid = 'tv'+ tvIds[i];
		if($(fid)!=null) {var modxMultiVideos=new MultiVideos(fid);}
	}
});
</script>
<!-- /MultiVideos -->
OUT;
$e->output($output);
}
if ($e->name == 'OnBeforeDocFormSave'){
require_once MODX_BASE_PATH.'assets/snippets/multivideos/videothumb.class.php';
$thumb = new videoThumb(array(
	'imagesPath' => MODX_BASE_PATH.'/'.$thumbUrl
  ,'imagesUrl' => $thumbUrl
  ,'emptyImage' => ''
));
$tvIds=explode(',',$tvIds);
foreach ($tvIds as $tvid) {
	$videoArr=json_decode($tmplvars[$tvid][1]);
	foreach ($videoArr as $k=>&$v) {
		if (empty($v[1])) {
      $preview = $thumb->process($v[0]);
      if(!isset($preview['error'])) {$v[1] = $preview['image'];}
	}}
	$tmplvars[$tvid][1]=str_replace('\\/', '/', json_encode($videoArr));
}
}