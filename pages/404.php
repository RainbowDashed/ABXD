<?php
//  AcmlmBoard XD - 404
//  Access: all

// Some servers use one response, some use another. For safety, use both.
header('HTTP/1.1 404 Not Found');
header('Status: 404 Not Found');

$title = __('404');

Kill(__('Error 404 - The page you are looking for was not found.').'<br />
	<a href=".">'.__('Return to the board index').'</a>');

?>
