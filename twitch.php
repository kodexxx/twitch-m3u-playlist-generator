<?
header("Content-Type:text/plain;charset=utf-8");

echo "#EXTM3U\n";

$channel_names = array("ceh9", "cheatbanned", "starladder5", "sharishaxd", "dreamhackcs");
foreach($channel_names as $item)
{
	$status = getStreams($item);
	if(!$status)
		echo "#EXTINF:-1 mpeg4," . $item."(offline)\nhttp://offline\n";
	else
		echo "#EXTINF:-1 mpeg4," . $item. " [". $status[0]["res"] . " " . $status[0]["bw"]."Mbit/s] " . "\n" . $status[0]["stream"] . "\n";
}


function get_http_response_code($url) {
	$headers = get_headers($url);
	return substr($headers[0], 9, 3);
}

function getStreams($channel_name)
{
	$token_content = json_decode(file_get_contents("http://api.twitch.tv/api/channels/".$channel_name."/access_token"));

	$token = $token_content->token;
	$sig = $token_content->sig;
	$random = rand(0, 10000000);
	$url_streams = "http://usher.twitch.tv/api/channel/hls/$channel_name.m3u8?player=twitchweb&token=$token&sig=$sig&\$allow_audio_only=true&allow_source=true&type=any&p=$random";
	if(get_http_response_code($url_streams) != 200)
		return false;
	else
	{
		$streams = explode("\n", file_get_contents($url_streams));
		$streams_res = array();
		foreach($streams as $key => $value)
		{
			if(stripos($value, "#EXT-X-STREAM-INF") !== false)
			{
				$info = explode(",", $value);
				$pl = explode("\"", $info[3]);
				$bw = explode("=", $info[1]);
				$res = explode("x", $info[2]);
				array_push($streams_res, array("name"=> $pl[1], "res"=> $res[1] . "p", "bw" => round($bw[1] / 1048576, 1), "stream" => $streams[$key + 1]));
			}
		}
		return $streams_res;
	}
}
