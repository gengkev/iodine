<?php
/**
* Just contains the definition for the class {@link NewsODP}.
* @author The Intranet 2 Development Team <intranet2@tjhsst.edu>
* @copyright 2005 The Intranet 2 Development Team
* @package NewsODP
* @filesource
*/

/**
* The class that handles unauthenticated distribution of ODP formatted
* presentations for the scrolling announcements.
* @package NewsODP
*/
class NewsODP {
	public static function get_cache($cachefile) {
		if(!file_exists($cachefile) || !($contents = file_get_contents($cachefile))) {
			return FALSE;
		}
		
		return $contents;
	}

	private static function store_cache($content,$cachefile) {
		$fh = fopen($cachefile,'w');
		fwrite($fh,$content);
		fclose($fh);
	}

	private static function create_contents_docbook() {
		global $I2_ROOT;
		$p = "";
		$p.="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$p.="<!DOCTYPE slides PUBLIC \"-//TJ Iodine//DTD Slides XML V3.3.0//EN\"\n";
		$p.="\"http://docbook.sourceforge.net/release/slides/3.3.0/schema/dtd/slides.dtd\">\n";
		$p.="<slides>\n";
		$p.="  <slidesinfo>\n";
		$p.="    <title>TJ News Feed</title>\n";
		$p.="    <abstract>\n";
		//$p.="	     <link>".$I2_ROOT."</link>\n";
		//$p.="	     <description>TJHSST Intranet News</description>\n";
		//$p.="	     <language>en-us</language>\n";
		//$p.="	     <pubDate>".date("r")."</pubDate>\n"; //We should make a variable to store this later.
		$p.="      <para>Generated by the TJHSST Intranet</para>\n";
		//$p.="      <managingEditor>iodine@tjhsst.edu</managingEditor>\n";
		//$p.="      <webMaster>iodine@tjhsst.edu</webMaster>\n";
		$p.="    </abstract>\n";
		$p.="  </slidesinfo>\n";
		$news = Feeds::getItems();
		foreach($news as $item) {
			$p.="  <foil>\n";
			$p.="    <title>".strip_tags($item->title)."</title>\n";
			//$p.="    <link>".$I2_ROOT."news/show/$item->nid</link>\n";
			$p.="    <para>".str_replace("&nbsp;"," ",strip_tags($item->text))."</para>\n";
			//$p.="    <pubDate>".date("r",strtotime($item->posted))."</pubDate>\n";
			//$p.="    <guid>".$I2_ROOT."news/show/$item->nid</guid>\n";
			$p.="  </foil>\n";
		}
		$p.="</slides>\n";
		return $p;
	}
	public static function update($cachefile=FALSE) {
		$cachefile = i2config_get('cache_dir','/var/cache/iodine/','core') . 'newsodp.cache';
		$contents = NewsODP::create_contents_docbook();// If the contents of the file havn't been made yet.
		NewsODP::store_cache($contents,"$cachefile.temp");
		if(file_exists($cachefile)) {
			unlink($cachefile); // Otherwise docbook2odf won't write to it.
		}
		exec("docbook2odf $cachefile.temp --output-file $cachefile");
		unlink("$cachefile.temp"); // We don't need this file afterwards. Comment this line out if you want more info for debugging this module.
		$contents = NewsODP::get_cache($cachefile);
		return $contents;
	}
}

?>
