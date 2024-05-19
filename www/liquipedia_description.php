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

function getLiquipediaUrl($botName) {

    $botName = getBotAlias($botName);
    if (!isLiquipediaPageNameAllowed($botName)) return array("status"=>"not_found","result"=>null);

    // first, try the direct link
    $directUrl = 'https://liquipedia.net/starcraft/'.str_replace(' ','_',$botName);
    $headers = get_headers($directUrl);
    if ( stripos( implode($headers), '200 OK') !== False ) return array("status"=>"ok","result"=>$directUrl);
    if ( stripos( implode($headers), '429 Too Many Requests') !== False ) return array("status"=>"inaccessible","result"=>null);

    // then, browse the Bots category
    $html = file_get_html('https://liquipedia.net/starcraft/Category:Bots');
    if ($html == null) return array("status"=>"inaccessible","result"=>null);
    $bestMatch = null;
    $bestMatchScore = 9999;
    foreach($html->find('.mw-content-ltr a') as $e) {
        $n = strtolower(trim($e->plaintext));
        $mismatchedChars = min( levenshtein(strtolower(trim($botName)),$n) , levenshtein($n,strtolower(trim($botName))) );

        if ($mismatchedChars < $bestMatchScore) {
            $bestMatchScore = $mismatchedChars;
            $bestMatch = 'https://liquipedia.net'.$e->href;
            if ($mismatchedChars == 0) break;
        }
    }

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
        $nextRequestAllowed = (time() - $lastRequestTime > 60 * 20); // allow only one request every few minutes to avoid being blocked by Liquipedia
        if ($contentsStr != null) {
            $contentsStr = '<div style="color: rgb(40,40,40); font-size: 80%; padding-bottom: 5px;">The following content from Liquipedia is displayed from cache, which might be up to '.$cacheAgeWeeks.' week'.(($cacheAgeWeeks > 1) ? 's' : '' ).' old.</div>'.$contentsStr;
            break;
        }

        // if needed and allowed, get the fresh content from liquipedia
        if ($contentsStr == null && $nextRequestAllowed) {

            file_put_contents($liquipedia_last_request_time_file, time());
            $urlRes = getLiquipediaUrl($botName);
            $liqUrl = $urlRes["result"];
            if ($liqUrl !== null) {

                // bot IS on Liquipedia
                $html = file_get_html($liqUrl);

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
                echo "<div style=\"padding-bottom: 20px; color: rgb(40,40,40);\">Information from <a target=\"_blank\" href=\"".$liqUrl."\">Liquipedia</a>:</div>";

                $filteredContents = array();
                $finalContents = array();
                foreach ($contents as $c) $filteredContents[] = $c;
                foreach ($filteredContents as $i => $c) {
                    // skip headers if there is no non-header content after it
                    if ( ( ($i+1) <= sizeof($filteredContents)) && ($filteredContents[$i]['tag'] == 'h4') && ($filteredContents[$i+1]['tag'] == 'h4') ) continue;
                    $finalContents[] = $c;
                }

                $contentsStr = "";
                foreach ($finalContents as $c) {
                    $contentsStr .= $c['txt'];
                }

                // cache the result
                if ($urlRes["status"] != "inaccessible") $cache->set($key, $contentsStr);


            } else {

                // bot IS NOT on Liquipedia
                $contentsStr = '<div style="color: rgb(40,40,40);">This bot doesn\'t have a page in the <a href="https://liquipedia.net/starcraft/Category:Bots" target="_blank">Bots section of Liquipedia</a> yet. You could help out by <a href="https://liquipedia.net/starcraft/Help:Create_an_Article" target="_blank">creating</a> it. Feel free to use the <a href="https://liquipedia.net/starcraft/Template:Infobox_bot" target="_blank">Infobox_bot template</a>.</div>';

                // cache the negative result as well
                if ($urlRes["status"] != "inaccessible") $cache->set($key, $contentsStr);

            }

        } else {
            // we've made a liquipedia request recently - tell user that we can't make another one at the moment
            $contentsStr = '<div style="color: rgb(40,40,40); font-size: 80%; padding-bottom: 5px;">Our request frequency policy prevents us from downloading the info from Liquipedia now. Please try again in a few minutes.</div>';
        }

    }

    // finally, print out the result
    echo $contentsStr;

} else {
    die();
}


?>
