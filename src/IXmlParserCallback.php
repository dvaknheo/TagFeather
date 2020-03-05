<?php
namespace TagFeather;

interface IXmlParserCallback
{

	public function asp_handle($str);
	public function cdata_handle($str);
	public function comment_handle($str);
	public function error_handle($error_info);
	public function notation_handle($str);
	public function pi_handle($str);
	public function tagbegin_handle($attrs);
	public function tagend_handle($tagname);
	public function text_handle($str);
}
