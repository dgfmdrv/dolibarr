<?php
/* Copyright (C) 2017 Laurent Destailleur	<eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *      \file       htdocs/core/lib/website2.lib.php
 *      \ingroup    website
 *      \brief      Library for website module (rare functions not required for execution of website)
 */



/**
 * Save content of a page on disk
 *
 * @param	string		$filemaster			Full path of filename master.inc.php for website to generate
 * @return	boolean							True if OK
 */
function dolSaveMasterFile($filemaster)
{
	global $conf;

	// Now generate the master.inc.php page
	dol_syslog("We regenerate the master file");
	dol_delete_file($filemaster);

	$mastercontent = '<?php'."\n";
	$mastercontent.= '// File generated to link to the master file - DO NOT MODIFY - It is just an include'."\n";
	$mastercontent.= "if (! defined('USEDOLIBARRSERVER') && ! defined('USEDOLIBARREDITOR')) require_once '".DOL_DOCUMENT_ROOT."/master.inc.php';\n";
	$mastercontent.= '?>'."\n";
	$result = file_put_contents($filemaster, $mastercontent);
	if (! empty($conf->global->MAIN_UMASK))
		@chmod($filemaster, octdec($conf->global->MAIN_UMASK));

		return $result;
}

/**
 * Save content of a page on disk
 *
 * @param	string		$filealias			Full path of filename to generate
 * @param	Website		$object				Object website
 * @param	WebsitePage	$objectpage			Object websitepage
 * @return	boolean							True if OK
 */
function dolSavePageAlias($filealias, $object, $objectpage)
{
	global $conf;

	// Now create the .tpl file (duplicate code with actions updatesource or updatecontent but we need this to save new header)
	dol_syslog("dolSavePageAlias We regenerate the alias page filealias=".$filealias);

	$aliascontent = '<?php'."\n";
	$aliascontent.= "// File generated to wrap the alias page - DO NOT MODIFY - It is just a wrapper to real page\n";
	$aliascontent.= 'global $dolibarr_main_data_root;'."\n";
	$aliascontent.= 'if (empty($dolibarr_main_data_root)) require \'./page'.$objectpage->id.'.tpl.php\'; ';
	$aliascontent.= 'else require $dolibarr_main_data_root.\'/website/\'.$website->ref.\'/page'.$objectpage->id.'.tpl.php\';'."\n";
	$aliascontent.= '?>'."\n";
	$result = file_put_contents($filealias, $aliascontent);
	if (! empty($conf->global->MAIN_UMASK)) {
		@chmod($filealias, octdec($conf->global->MAIN_UMASK));
	}

	return ($result?true:false);
}


/**
 * Save content of a page on disk
 *
 * @param	string		$filetpl			Full path of filename to generate
 * @param	Website		$object				Object website
 * @param	WebsitePage	$objectpage			Object websitepage
 * @return	boolean							True if OK
 */
function dolSavePageContent($filetpl, $object, $objectpage)
{
	global $conf;

	// Now create the .tpl file (duplicate code with actions updatesource or updatecontent but we need this to save new header)
	dol_syslog("We regenerate the tpl page filetpl=".$filetpl);

	dol_delete_file($filetpl);

	$shortlangcode = '';
	if ($objectpage->lang) $shortlangcode=preg_replace('/[_-].*$/', '', $objectpage->lang);		// en_US or en-US -> en

	$tplcontent ='';
	$tplcontent.= "<?php // BEGIN PHP\n";
	$tplcontent.= '$websitekey=basename(__DIR__); if (empty($websitepagefile)) $websitepagefile=__FILE__;'."\n";
	$tplcontent.= "if (! defined('USEDOLIBARRSERVER') && ! defined('USEDOLIBARREDITOR')) { require_once './master.inc.php'; } // Not already loaded"."\n";
	$tplcontent.= "require_once DOL_DOCUMENT_ROOT.'/core/lib/website.lib.php';\n";
	$tplcontent.= "require_once DOL_DOCUMENT_ROOT.'/core/website.inc.php';\n";
	$tplcontent.= "ob_start();\n";
	$tplcontent.= "// END PHP ?>\n";
	if (! empty($conf->global->WEBSITE_FORCE_DOCTYPE_HTML5))
	{
		$tplcontent.= "<!DOCTYPE html>\n";
	}
	$tplcontent.= '<html'.($shortlangcode ? ' lang="'.$shortlangcode.'"':'').'>'."\n";
	$tplcontent.= '<head>'."\n";
	$tplcontent.= '<title>'.dol_string_nohtmltag($objectpage->title, 0, 'UTF-8').'</title>'."\n";
	$tplcontent.= '<meta charset="utf-8">'."\n";
	$tplcontent.= '<meta http-equiv="content-type" content="text/html; charset=utf-8" />'."\n";
	$tplcontent.= '<meta name="robots" content="index, follow" />'."\n";
	$tplcontent.= '<meta name="viewport" content="width=device-width, initial-scale=1.0">'."\n";
	$tplcontent.= '<meta name="keywords" content="'.dol_string_nohtmltag($objectpage->keywords).'" />'."\n";
	$tplcontent.= '<meta name="title" content="'.dol_string_nohtmltag($objectpage->title, 0, 'UTF-8').'" />'."\n";
	$tplcontent.= '<meta name="description" content="'.dol_string_nohtmltag($objectpage->description, 0, 'UTF-8').'" />'."\n";
	$tplcontent.= '<meta name="generator" content="'.DOL_APPLICATION_TITLE.' '.DOL_VERSION.' (https://www.dolibarr.org)" />'."\n";
	$tplcontent.= '<meta name="dolibarr:pageid" content="'.dol_string_nohtmltag($objectpage->id).'" />'."\n";
	$tplcontent.= '<link href="/'.(($objectpage->id == $object->fk_default_home) ? '' : ($objectpage->pageurl.'.php')).'" rel="canonical" />'."\n";
	$tplcontent.= '<!-- Include link to CSS file -->'."\n";
	$tplcontent.= '<link rel="stylesheet" href="styles.css.php?website=<?php echo $websitekey; ?>" type="text/css" />'."\n";
	$tplcontent.= '<!-- Include HTML header from common file -->'."\n";
	$tplcontent.= '<?php print preg_replace(\'/<\/?html>/ims\', \'\', file_get_contents(DOL_DATA_ROOT."/website/".$websitekey."/htmlheader.html")); ?>'."\n";
	$tplcontent.= '<!-- Include HTML header from page header block -->'."\n";
	$tplcontent.= preg_replace('/<\/?html>/ims', '', $objectpage->htmlheader)."\n";
	$tplcontent.= '</head>'."\n";

	$tplcontent.= '<!-- File generated by Dolibarr website module editor -->'."\n";
	$tplcontent.= '<body id="bodywebsite" class="bodywebsite bodywebpage-'.$objectpage->ref.'">'."\n";
	$tplcontent.= $objectpage->content."\n";
	$tplcontent.= '</body>'."\n";
	$tplcontent.= '</html>'."\n";

	$tplcontent.= '<?php // BEGIN PHP'."\n";
	$tplcontent.= '$tmp = ob_get_contents(); ob_end_clean(); dolWebsiteOutput($tmp, "html", '.$objectpage->id.');'."\n";
	$tplcontent.= "// END PHP ?>"."\n";

	//var_dump($filetpl);exit;
	$result = file_put_contents($filetpl, $tplcontent);
	if (! empty($conf->global->MAIN_UMASK))
		@chmod($filetpl, octdec($conf->global->MAIN_UMASK));

		return $result;
}


/**
 * Save content of the index.php and wrapper.php page
 *
 * @param	string		$pathofwebsite			Path of website root
 * @param	string		$fileindex				Full path of file index.php
 * @param	string		$filetpl				File tpl to index.php page redirect to
 * @param	string		$filewrapper			Full path of file wrapper.php
 * @return	boolean								True if OK
 */
function dolSaveIndexPage($pathofwebsite, $fileindex, $filetpl, $filewrapper)
{
	global $conf;

	$result1=false;
	$result2=false;

	dol_mkdir($pathofwebsite);

	dol_delete_file($fileindex);
	$indexcontent = '<?php'."\n";
	$indexcontent.= "// BEGIN PHP File generated to provide an index.php as Home Page or alias redirector - DO NOT MODIFY - It is just a generated wrapper.\n";
	$indexcontent.= '$websitekey=basename(__DIR__); if (empty($websitepagefile)) $websitepagefile=__FILE__;'."\n";
	$indexcontent.= "if (! defined('USEDOLIBARRSERVER') && ! defined('USEDOLIBARREDITOR')) { require_once './master.inc.php'; } // Load master if not already loaded\n";
	$indexcontent.= 'if (! empty($_GET[\'pageref\']) || ! empty($_GET[\'pagealiasalt\']) || ! empty($_GET[\'pageid\'])) {'."\n";
	$indexcontent.= "	require_once DOL_DOCUMENT_ROOT.'/core/lib/website.lib.php';\n";
	$indexcontent.= "	require_once DOL_DOCUMENT_ROOT.'/core/website.inc.php';\n";
	$indexcontent.= '	redirectToContainer($_GET[\'pageref\'], $_GET[\'pagealiasalt\'], $_GET[\'pageid\']);'."\n";
	$indexcontent.= "}\n";
	$indexcontent.= "include_once './".basename($filetpl)."'\n";
	$indexcontent.= '// END PHP ?>'."\n";
	$result1 = file_put_contents($fileindex, $indexcontent);
	if (! empty($conf->global->MAIN_UMASK))
		@chmod($fileindex, octdec($conf->global->MAIN_UMASK));

		dol_delete_file($filewrapper);

		$wrappercontent=file_get_contents(DOL_DOCUMENT_ROOT.'/website/samples/wrapper.html');

		$result2 = file_put_contents($filewrapper, $wrappercontent);
		if (! empty($conf->global->MAIN_UMASK))
			@chmod($filewrapper, octdec($conf->global->MAIN_UMASK));

			return ($result1 && $result2);
}


/**
 * Save content of a page on disk
 *
 * @param	string		$filehtmlheader		Full path of filename to generate
 * @param	string		$htmlheadercontent	Content of file
 * @return	boolean							True if OK
 */
function dolSaveHtmlHeader($filehtmlheader, $htmlheadercontent)
{
	global $conf, $pathofwebsite;

	dol_syslog("Save html header into ".$filehtmlheader);

	dol_mkdir($pathofwebsite);
	$result = file_put_contents($filehtmlheader, $htmlheadercontent);
	if (! empty($conf->global->MAIN_UMASK))
		@chmod($filehtmlheader, octdec($conf->global->MAIN_UMASK));

	return $result;
}

/**
 * Save content of a page on disk
 *
 * @param	string		$filecss			Full path of filename to generate
 * @param	string		$csscontent			Content of file
 * @return	boolean							True if OK
 */
function dolSaveCssFile($filecss, $csscontent)
{
	global $conf, $pathofwebsite;

	dol_syslog("Save css file into ".$filecss);

	dol_mkdir($pathofwebsite);
	$result = file_put_contents($filecss, $csscontent);
	if (! empty($conf->global->MAIN_UMASK))
		@chmod($filecss, octdec($conf->global->MAIN_UMASK));

	return $result;
}

/**
 * Save content of a page on disk
 *
 * @param	string		$filejs				Full path of filename to generate
 * @param	string		$jscontent			Content of file
 * @return	boolean							True if OK
 */
function dolSaveJsFile($filejs, $jscontent)
{
	global $conf, $pathofwebsite;

	dol_syslog("Save js file into ".$filejs);

	dol_mkdir($pathofwebsite);
	$result = file_put_contents($filejs, $jscontent);
	if (! empty($conf->global->MAIN_UMASK))
		@chmod($filejs, octdec($conf->global->MAIN_UMASK));

	return $result;
}

/**
 * Save content of a page on disk
 *
 * @param	string		$filerobot			Full path of filename to generate
 * @param	string		$robotcontent		Content of file
 * @return	boolean							True if OK
 */
function dolSaveRobotFile($filerobot, $robotcontent)
{
	global $conf, $pathofwebsite;

	dol_syslog("Save robot file into ".$filerobot);

	dol_mkdir($pathofwebsite);
	$result = file_put_contents($filerobot, $robotcontent);
	if (! empty($conf->global->MAIN_UMASK))
		@chmod($filerobot, octdec($conf->global->MAIN_UMASK));

	return $result;
}

/**
 * Save content of a page on disk
 *
 * @param	string		$filehtaccess		Full path of filename to generate
 * @param	string		$htaccess			Content of file
 * @return	boolean							True if OK
 */
function dolSaveHtaccessFile($filehtaccess, $htaccess)
{
	global $conf, $pathofwebsite;

	dol_syslog("Save htaccess file into ".$filehtaccess);

	dol_mkdir($pathofwebsite);
	$result = file_put_contents($filehtaccess, $htaccess);
	if (! empty($conf->global->MAIN_UMASK))
		@chmod($filehtaccess, octdec($conf->global->MAIN_UMASK));

	return $result;
}

/**
 * Save content of a page on disk
 *
 * @param	string		$file				Full path of filename to generate
 * @param	string		$content			Content of file
 * @return	boolean							True if OK
 */
function dolSaveManifestJson($file, $content)
{
	global $conf, $pathofwebsite;

	dol_syslog("Save manifest.js.php file into ".$file);

	dol_mkdir($pathofwebsite);
	$result = file_put_contents($file, $content);
	if (! empty($conf->global->MAIN_UMASK))
		@chmod($file, octdec($conf->global->MAIN_UMASK));

	return $result;
}

/**
 * Save content of a page on disk
 *
 * @param	string		$file				Full path of filename to generate
 * @param	string		$content			Content of file
 * @return	boolean							True if OK
 */
function dolSaveReadme($file, $content)
{
	global $conf, $pathofwebsite;

	dol_syslog("Save README.md file into ".$file);

	dol_mkdir($pathofwebsite);
	$result = file_put_contents($file, $content);
	if (! empty($conf->global->MAIN_UMASK))
		@chmod($file, octdec($conf->global->MAIN_UMASK));

		return $result;
}


/**
 * 	Show list of themes. Show all thumbs of themes/skins
 *
 *	@param	Website		$website		Object website to load the tempalte into
 * 	@return	void
 */
function showWebsiteTemplates(Website $website)
{
	global $conf,$langs,$db,$form;
	global $bc;

	$dirthemes=array('/doctemplates/websites');
	if (! empty($conf->modules_parts['websitetemplates']))		// Using this feature slow down application
	{
		foreach($conf->modules_parts['websitetemplates'] as $reldir)
		{
			$dirthemes=array_merge($dirthemes, (array) ($reldir.'doctemplates/websites'));
		}
	}
	$dirthemes=array_unique($dirthemes);
	// Now dir_themes=array('/themes') or dir_themes=array('/theme','/mymodule/theme')

	$colspan=2;

	$thumbsbyrow=6;
	print '<table class="noborder" width="100%">';

	// Title
	print '<tr class="liste_titre"><th class="titlefield"></th>';
	print '<th class="right">';
	$url='https://www.dolistore.com/43-web-site-templates';
	print '<a href="'.$url.'" target="_blank">';
	print $langs->trans('DownloadMoreSkins');
	print '</a>';
	print '</th></tr>';

	print '<tr>';
	print '<td>'.$langs->trans("ThemeDir").'</td>';
	print '<td>';
	foreach($dirthemes as $dirtheme)
	{
		echo '"'.$dirtheme.'" ';
	}
	print '</td>';
	print '</tr>';

	print '<tr><td colspan="'.$colspan.'">';

	print '<table class="nobordernopadding" width="100%"><tr><td><div class="center">';

	$i=0;
	foreach($dirthemes as $dir)
	{
		//print $dirroot.$dir;exit;
		$dirtheme=DOL_DATA_ROOT.$dir;	// This include loop on $conf->file->dol_document_root
		if (is_dir($dirtheme))
		{
			$handle=opendir($dirtheme);
			if (is_resource($handle))
			{
				while (($subdir = readdir($handle))!==false)
				{
					if (is_file($dirtheme."/".$subdir) && substr($subdir, 0, 1) <> '.'
						&& substr($subdir, 0, 3) <> 'CVS' && preg_match('/\.zip$/i', $subdir))
					{
						$subdirwithoutzip = preg_replace('/\.zip$/i', '', $subdir);

						// Disable not stable themes (dir ends with _exp or _dev)
						if ($conf->global->MAIN_FEATURES_LEVEL < 2 && preg_match('/_dev$/i', $subdir)) continue;
						if ($conf->global->MAIN_FEATURES_LEVEL < 1 && preg_match('/_exp$/i', $subdir)) continue;

						print '<div class="inline-block" style="margin-top: 10px; margin-bottom: 10px; margin-right: 20px; margin-left: 20px;">';

						$file = $dirtheme."/".$subdirwithoutzip.".jpg";
						$url=DOL_URL_ROOT.'/viewimage.php?modulepart=doctemplateswebsite&file='.$subdirwithoutzip.".jpg";

						if (! file_exists($file)) $url=DOL_URL_ROOT.'/public/theme/common/nophoto.png';

						$originalfile = basename($file);
						$entity = $conf->entity;
						$modulepart = 'doctemplateswebsite';
						$cache = '';
						$title = $file;

						$ret='';
						$urladvanced=getAdvancedPreviewUrl($modulepart, $originalfile, 1, '&entity='.$entity);
						if (! empty($urladvanced)) $ret.='<a class="'.$urladvanced['css'].'" target="'.$urladvanced['target'].'" mime="'.$urladvanced['mime'].'" href="'.$urladvanced['url'].'">';
						else $ret.='<a href="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$entity.'&file='.urlencode($originalfile).'&cache='.$cache.'">';
						print $ret;
						print '<img class="websiteskinthumb shadow" src="'.$url.'" border="0" width="80" height="60" alt="'.$title.'" title="'.$title.'" style="margin-bottom: 5px;">';
						print '</a>';

						print '<br>';
						print $subdir.' ('.dol_print_size(dol_filesize($dirtheme."/".$subdir), 1, 1).')';
						print '<br><a href="'.$_SERVER["PHP_SELF"].'?action=importsiteconfirm&website='.$website->ref.'&templateuserfile='.$subdir.'" class="button">'.$langs->trans("Load").'</a>';
						print '</div>';

						$i++;
					}
				}
			}
		}
	}

	print '</div></td></tr></table>';

	print '</td></tr>';
	print '</table>';
}
