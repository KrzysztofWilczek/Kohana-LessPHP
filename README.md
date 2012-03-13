KOHANA 3.X LESS PHP CLASS
Auhor: Krzysztof Wilczek

This module compile LESS files to CSS stylesheets using lessphp lib (http://leafo.net/lessphp/). Module usage depends on config file and list or simple path to less file specyfied in compile method.

Config usage:
path				:	Path to css files catalog 
bind_to_one_file	:	Should all less file be binded to one CSS output file (default = true)
css_file_name		: 	If all less files should be binded to one CSS we need to put a file name of new CSS file (default = "style.css")	

Simple usage (in view we write):

$less_files = array
(
	APPPATH.'../media/css/some_style.less',
	APPPATH.'../media/css/other_style.less'
);            
echo Less::compile($less_files);

And that's all.