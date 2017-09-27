<?php

class AimpAPI{
	const URL = 'http://localhost:3333/RPC_JSON';
	static $queryID = 0;

	function query($method, $params = []){
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/json\r\n",
				'method'  => 'POST',
				'content' => json_encode([
					"version"=> "1.1",
					"method" => $method,
					"id" => self::$queryID++,
					"params" => $params
				])
			)
		);
		$context  = stream_context_create($options);

		$container = json_decode(file_get_contents(self::URL, false, $context));
		return $container->result;
	}

}

$aimpAPI = new AimpAPI();
// list playlists
$playlists = [];
foreach ($aimpAPI->query('GetPlaylists', [
	"fields" => ['id', 'title'],
]) as $playlist)
	$playlists[$playlist->title] = $playlist->id;

// find playlist from GET
if (array_key_exists($_GET['playlist'], $playlists)){
	$playListID = $playlists[$_GET['playlist']];
	// get first song
	$resp = $aimpAPI->query('GetPlaylistEntries', [
		"playlist_id" => $playListID,
		"fields" => ['id', 'title'],
	])->entries;

	// select random track
	list($trackID, $trackTitle) = $resp[mt_rand(0, count($resp) - 1)];
	$aimpAPI->query('Play', ['track_id' => $trackID]);
	echo $trackTitle;
}
