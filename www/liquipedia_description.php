<?php

include 'includes/simple_html_dom.php';
include 'includes/generalFunctions.php';

function activateLinks($s) {
    return preg_replace('/https?:\/\/[\w\-\.!~#?&=+\*\'"(),\/]+/','<a href="$0">$0</a>',$s);
}

function fixRelativeLinks($string) {
    // fix relative links in anchors
    $regex = '~<a([^>]*)href=["\']/([^"\']*)["\']([^>]*)>~';
    $replace = '<a$1href="https://liquipedia.net/$2"$3>';
    $string = preg_replace($regex, $replace, $string);

    // do the same thing for images
    //$regex = '~<img([^>]*)src=["\']([^"\']*)["\']([^>]*)>~';
    //$replace = '<img$1src="https://liquipedia.net$2"$3>';
    //$string = preg_replace($regex, $replace, $string);
    //$string = str_ireplace('srcset=', 'deleted_attrib=', $string);

    // remove images, leaving the alt text only (because liquipedia doesn't like us embedding their images)
    $regex = '~<img([^>]*)alt=["\']([^"\']*)["\']([^>]*)>~';
    $replace = '<span>$2</span>';
    $string = preg_replace($regex, $replace, $string);


    return $string;
}

function getLiquipediaPage($botName, $context) {

    $botName = getBotAlias($botName);
    if (!isLiquipediaPageNameAllowed($botName)) return array("status"=>"not_found","result"=>null);

    $bestMatch = null;
    $bestMatchScore = 9999;
    $optionalCmcontinueParam = "";
    // note: the maximum cmlimit is 500
    $cmlimit = 500;
    do {
        $compressedData = file_get_contents("https://liquipedia.net/starcraft/api.php?action=query&list=categorymembers&cmtitle=Category:Bots&cmprop=title&cmlimit=$cmlimit$optionalCmcontinueParam&format=json", false, $context);
        if (stripos(implode($http_response_header), '200 OK') === False) return array("status"=>"inaccessible","result"=>null);
        if ($compressedData == null) return array("status"=>"inaccessible","result"=>null);
        $data = gzdecode($compressedData);
        if ($data == null) return array("status"=>"inaccessible","result"=>null);
        $json = json_decode($data, true);
        if ($json == null) return array("status"=>"inaccessible","result"=>null);
        if (!array_key_exists("query", $json)) return array("status"=>"inaccessible","result"=>null);
        if (!isset($json["query"]["categorymembers"])) return array("status"=>"inaccessible","result"=>null);

        foreach($json["query"]["categorymembers"] as $vals) {
            $title = strtolower(trim($vals["title"]));
            $mismatchedChars = min(levenshtein(strtolower(trim($botName)),$title), levenshtein($title,strtolower(trim($botName))));

            if ($mismatchedChars < $bestMatchScore) {
                $bestMatchScore = $mismatchedChars;
                $bestMatch = $vals["title"];
                if ($mismatchedChars == 0) break;
            }
        }

        if (!array_key_exists("continue", $json) || !isset($json["continue"]["cmcontinue"])) {
            break;
        }

        $optionalCmcontinueParam = "&cmcontinue=".$json["continue"]["cmcontinue"];
        // https://liquipedia.net/api-terms-of-use says to "Rate limit your requests to no more than 1 request per 2 seconds."
        sleep(2);
    } while ($optionalCmcontinueParam != "");

    // allow small text mismatch
    if ($bestMatchScore <= 2) {
        return array("status"=>"ok","result"=>$bestMatch);
    } else {
        return array("status"=>"not_found","result"=>null);
    }
}

// process the params
if (isset($_GET["bot_name"]) && (trim($_GET["bot_name"]) != '')) {
    $botName = $_GET["bot_name"];

    // first, check our cache
    $cache = new SimpleCache();
    $key = 'liquipedia_bot_'.str_replace(' ','_',$botName);

    $cacheAgeWeeksMin = 1;
    $cacheAgeWeeksMax = 12;
    $liquipedia_last_request_time_file = $GLOBALS['CACHE_FOLDER_WITHOUT_SLASH'].'/liquipedia_last_request_time';

    for ($cacheAgeWeeks = $cacheAgeWeeksMin; $cacheAgeWeeks <= $cacheAgeWeeksMax; $cacheAgeWeeks++) {

        $contentsStr = $cache->get($key, 60*60*24*7 * $cacheAgeWeeks);
        $lastRequestTime = (file_exists($liquipedia_last_request_time_file) ? intval(trim(file_get_contents($liquipedia_last_request_time_file))) : 0);
        // Throttle Liquipedia requests to avoid being blocked by Liquipedia. Note: a query request is allowed every 2 seconds,
        // and a parse request is allowed every 30 seconds, as per https://liquipedia.net/api-terms-of-use, but we don't need to use it that often.
        $nextRequestAllowed = (time() - $lastRequestTime > 60 * 10);
        if ($contentsStr != null) {
            $contentsStr = '<div style="color: rgb(40,40,40); font-size: 80%; padding-bottom: 5px;">The following section of content from <a href="https://liquipedia.net/starcraft/Category:Bots" target="_blank">Liquipedia</a> is displayed from cache, which might be up to '.$cacheAgeWeeks.' week'.(($cacheAgeWeeks > 1) ? 's' : '' ).' old, and is licensed using the <a href="https://creativecommons.org/licenses/by-sa/3.0/us/" target="_blank">CC-BY-SA 3.0 license</a>.</div>'.$contentsStr;
            break;
        }

        // if needed and allowed, get the fresh content from liquipedia
        if ($contentsStr == null && $nextRequestAllowed) {

            file_put_contents($liquipedia_last_request_time_file, time());

            $opts = [
                "http" => [
                    // Some requirements by Liquipedia as per https://liquipedia.net/api-terms-of-use
                    "user_agent" => 'sscait-web/1.0 (https://sscaitournament.com/; ' . $GLOBALS['ADMIN_EMAIL'] . ')',
                    "header" => "Accept-Encoding: gzip\r\n"
                ]
            ];
            $context = stream_context_create($opts);

            $pageRes = getLiquipediaPage($botName, $context);
            $liqPage = $pageRes["result"];
            if ($liqPage !== null) {
                // bot IS on Liquipedia

                // https://liquipedia.net/api-terms-of-use says to "Rate limit your requests to no more than 1 request per 2 seconds."
                sleep(2);

                $liqUrl = "https://liquipedia.net/starcraft/$liqPage";
                $compressedData = file_get_contents("https://liquipedia.net/starcraft/api.php?action=parse&page=$liqPage&format=json", false, $context);
                if (stripos(implode($http_response_header), '200 OK') === False) continue;
                if ($compressedData == null) continue;
                $data = gzdecode($compressedData);
                if ($data == null) continue;
                $json = json_decode($data, true);
                if ($json == null) continue;
                if (!array_key_exists("parse", $json)) continue;
                if (!array_key_exists("text", $json["parse"])) continue;
                if (!array_key_exists("*", $json["parse"]["text"])) continue;
                if (!isset($json["parse"]["text"]["*"])) continue;

                $html = str_get_html($json["parse"]["text"]["*"]);

                // extract everything of interest
                $interestingContent = array(

                    array("xpath"=>"p",                   "display"=>"p",     "plaintext"=>false ),
                    array("xpath"=>".mw-parser-output ul","display"=>"ul",    "plaintext"=>false ),
                    array("xpath"=>".mw-parser-output ol","display"=>"ol",    "plaintext"=>false ),
                    array("xpath"=>".mw-headline",        "display"=>"h4",    "plaintext"=>true ),
                    array("xpath"=>".wikitable",          "display"=>"table", "plaintext"=>false),
                    array("xpath"=>".mw-references-wrap", "display"=>"div",   "plaintext"=>false),
                    array("xpath"=>".fo-nttax-infobox",   "display"=>"div",   "plaintext"=>false)

                );
                $contents = array();
                foreach($interestingContent as $ic)  {

                    foreach($html->find($ic["xpath"]) as $e) {
                        // skip elements with certain text
                        $skipThis = false;
                        foreach (array("", "-", "Contents", "[e][h] SAIDA") as $skip) {
                            if (trim($e->plaintext) == $skip) {
                                $skipThis = true;
                                break;
                            }
                        }
                        if ($skipThis) continue;
                        if ( stripos($e->outertext(),"toclevel") != false) continue; // skip TOC

                        // extract this element's contents
                        $pos = $e->tag_start;
                        if ($ic["plaintext"]) {
                            $txt = '<'.$ic["display"].'>'.activateLinks($e->plaintext).'</'.$ic["display"].'>';
                        } else {
                            $txt = $e->outertext();
                        }
                        $contents[$pos] = array( "tag"=>$ic["display"], "txt"=>fixRelativeLinks($txt) );

                    }

                }

                // sort extracted contents
                ksort($contents);

                // print it out
                echo "<div style=\"padding-bottom: 20px; color: rgb(40,40,40);\">The following section of content from <a target=\"_blank\" href=\"".$liqUrl."\">Liquipedia</a> is licensed using the <a href=\"https://creativecommons.org/licenses/by-sa/3.0/us/\" target=\"_blank\">CC-BY-SA 3.0 license</a>.</div>";

                $filteredContents = array();
                $finalContents = array();
                foreach ($contents as $c) $filteredContents[] = $c;
                foreach ($filteredContents as $i => $c) {
                    // skip headers if there is no non-header content after it
                    if ( ( ($i+1) <= sizeof($filteredContents)) && ($filteredContents[$i]['tag'] == 'h4') && (($i+1) == sizeof($filteredContents) || $filteredContents[$i+1]['tag'] == 'h4') ) continue;
                    $finalContents[] = $c;
                }

                $contentsStr = "";
                foreach ($finalContents as $c) {
                    $contentsStr .= $c['txt'];
                }

                // cache the result
                if ($pageRes["status"] != "inaccessible") $cache->set($key, $contentsStr);
                break;

            } else {

                // bot IS NOT on Liquipedia
                $contentsStr = '<div style="color: rgb(40,40,40);">This bot doesn\'t have a page in the <a href="https://liquipedia.net/starcraft/Category:Bots" target="_blank">Bots section of Liquipedia</a> yet. You could help out by <a href="https://liquipedia.net/starcraft/Help:Create_an_Article" target="_blank">creating</a> it. Please use the <a href="https://liquipedia.net/starcraft/Template:Infobox_bot" target="_blank">Infobox_bot template</a> (see the <a href="./index.php?action=rules" target="_blank">Rules</a> page for further instructions).</div>';

                // cache the negative result as well
                if ($pageRes["status"] != "inaccessible") $cache->set($key, $contentsStr);

            }

        } else {
            // we've made a liquipedia request recently - tell user that we can't make another one at the moment
            $contentsStr = '<div style="color: rgb(40,40,40); font-size: 80%; padding-bottom: 5px;">Our request frequency policy prevents us from downloading the info from <a href="https://liquipedia.net/starcraft/Category:Bots" target="_blank">Liquipedia</a> now. Please try again in a few minutes.</div>';
        }

    }

    // finally, print out the result
    echo $contentsStr;

} else {
    die();
}


?>
