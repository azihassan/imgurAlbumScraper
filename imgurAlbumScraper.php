<?php

try
{
	list($id, $path) = parse_argv($argv);
	$links = get_links($id);
	
	if(($link_count = sizeof($links)) === 0)
	{
		echo 'No links were found.'.PHP_EOL;
		exit;
	}
	foreach($links as $k => $l)
	{
		$local_filename = $path.DIRECTORY_SEPARATOR.$id.'-'.($k + 1).'.jpg';
		printf('Downloading %s (%d/%d)...%s', basename($l), $k + 1, $link_count, "\t");
		
		if(($image = $image = file_get_contents($l)) === FALSE)
		{
			echo '[Failed]'.PHP_EOL;
			continue;
		}
		
		file_put_contents($local_filename, $image);
		echo '[Done]'.PHP_EOL;
	}
}
catch(Exception $e)
{
	echo $e->getMessage().PHP_EOL;
	exit;
}

/* Functions */
function parse_argv(array $argv)
{
	if(sizeof($argv) != 3)
	{
		throw new Exception('Usage : php '.$argv[0].' album_ID ./images');
	}

	if(!is_writable($argv[2]) && !mkdir($argv[2]))
	{
		throw new Exception('Could not write to '.$path.', check the permissions of the directory.');
	}
	
	return array($argv[1], $argv[2]);
}

function get_links($id)
{
	libxml_use_internal_errors(true);
	$links = array();
	$dom = new DOMDocument("1.0", "utf-8");
	$stream = stream_context_create(array
	(
		'http' => array('user_agent' => 'Nokia6600/1.0 (5.27.0) SymbianOS/7.0s Series60/2.0 Profile/MIDP-2.0 Configuration/CLDC-1')
	));
	$src = file_get_contents('http://imgur.com/a/'.$id.'/layout/blog', FALSE, $stream);
	
	if($src === FALSE)
	{
		throw new Exception('Failed to retrieve the source.');
	}
	
	$dom->strictErrorChecking = FALSE;
	$dom->recover = TRUE;
	$dom->loadHTML($src);
	libxml_clear_errors();
	
	$xpath = new DOMXPath($dom);
	$results = $xpath->query('//img[@class="unloaded"]/@data-src');
	
	foreach($results as $r)
	{
		$links[] = $r->nodeValue;
	}
	
	return $links;
}
