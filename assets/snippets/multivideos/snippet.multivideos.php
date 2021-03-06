<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
$tvname = isset($tvname) ? $tvname : 'video';
$outerTpl = isset($outerTpl) ? $modx->getChunk($outerTpl) : '<div class="thumbs">[+videos+]</div>';
$rowTpl = isset($rowTpl) ? $modx->getChunk($rowTpl) : '<a href="[+embed+]" id="thumb_[+num+]"><img src="[+thumb+]" alt="" title="[+title+]" /></a>';
$fid = isset($fid) ? $fid : false;
$reverse = isset($reverse) ? $reverse : false;
$limit = isset ($limit) ? $limit : 0;

if (isset($id)) {
	$tvf = $modx->getTemplateVar($tvname,'*',$id);
	$tvv = $tvf['value'];
} else {
	$id = $modx->documentObject['id']; 
	$tvf = $modx->documentObject[$tvname];
	$tvv = $tvf[1];
}
if (!$tvv || $tvv=='[]') return;
$fotoArr=json_decode($tvv);
if ($reverse) $fotoArr = array_reverse($fotoArr);
$fotoRes=array();
$num=1;
if (!class_exists('videoThumb')) include_once(MODX_BASE_PATH.'assets/snippets/multivideos/videothumb.class.php');
$video = new videoThumb();
foreach ($fotoArr as $v) {
	if ($limit && $limit+1 == $num) break;
	$embed = $video->process($v[0],false);
	$fields = array ('[+video+]','[+thumb+]','[+title+]','[+embed+]','[+num+]');
	$values = array ($v[0],$v[1],$v[2],$embed['video'],$num);
	$fotoRes[$num] = str_replace($fields, $values, $rowTpl);
	$num++;
}
#################### PAGINATION ####################
$count_per_page = isset($display) ? $display : 10;
$tplLinkNext = '<a href="[+link+]">>></a>';
$tplLinkPrev = '<a href="[+link+]"><<</a>';
$tplLinkNav = '<div class="mp_pages">[+linkprev+] [+pages+] [+linknext+]</div>';
$mp_pagecount=ceil(count($fotoRes)/$count_per_page);
if (!empty($pagination) && $mp_pagecount > 1){
	$mp_currentpage = isset($_GET["page"]) ? intval($_GET["page"]): 1;
	if ($mp_currentpage > $mp_pagecount || $mp_currentpage < 1) { $mp_currentpage = 1; }
	$char = ($modx->config['friendly_urls'] == 0) ? "&" : "?";
	$url = $modx->makeurl($modx->documentObject["id"],'',$char.'page=');
	$prevpage = $mp_currentpage-1;	$nextpage = $mp_currentpage+1;
	$linkprev = ($prevpage>0) ? str_replace("[+link+]",$url.$prevpage,$tplLinkPrev) : '';
	$linknext = ($nextpage>$mp_pagecount) ? '' : str_replace("[+link+]",$url.$nextpage,$tplLinkNext);
	$tplPages = str_replace("[+linkprev+]",$linkprev,$tplLinkNav);
	$tplPages = str_replace("[+linknext+]",$linknext,$tplPages);
	$pages='';
	for ($i=1;$i<=$mp_pagecount;$i++){
		$pages .= ($i==$mp_currentpage) ? '<span class="mp_currentpage">'.$i.'</span>' : '<a class="mp_page" href="'.$url.$i.'">'.$i.'</a>';
		$pages .= ($i==$mp_pagecount) ? '' : ' | ';
	}
	$tplPages=str_replace("[+pages+]",$pages,$tplPages);
	$fotoRes=array_slice($fotoRes,$count_per_page*$mp_currentpage-$count_per_page,$count_per_page);
	$outerTpl .= $tplPages;
}
#####################################################
$output = $fid ? $fotoRes[$fid] : implode('',$fotoRes);
if (isset($random)) $output = $fotoRes[array_rand($fotoRes)];
if ($output) return str_replace('[+videos+]',$output,$outerTpl);
?>