<!-- Note: Convert .docx files to .md: https://documentalchemy.com/demo/docx2md -->

<div id="blog-section">
<?php

    if (
        (isset($_GET['date'])) && 
        (date('Y-m-d', strtotime($_GET['date'])) == $_GET['date'])  && 
        (file_exists('./blogposts/'.$_GET['date'].'.md')) 
    ) {
        
        // single post
        include './includes/Parsedown.php';
        $Parsedown = new Parsedown();
        
        
        $postFile = $_GET['date'].'.md';
        $postDate = date('Y-m-d', strtotime(basename($postFile, ".md")));
        $contents = file_get_contents('./blogposts/'.$postFile);
        
        $contentsHTML = $Parsedown->text($contents);
        // minor HTML modifications
        $contentsHTML = str_ireplace('<h1>','<h2>',$contentsHTML);
        $contentsHTML = str_ireplace('</h1>','</h2>',$contentsHTML);
        
        $permalink = $GLOBALS["DOMAIN_WITHOUT_SLASH"].'/index.php?action=blog&date='.$postDate;
        ?>

        <div id="blog-single-post">
          <div class="container">            
                
                <?php
                echo '<div class="blog-content">'.$contentsHTML.'</div>';
                echo '<div class="blog-date">Published on: '.$postDate.'</div>';
                echo '<div class="blog-permalink">Permalink: <a href="'.$permalink.'">'.$permalink.'</a></div>';
                ?>
            
          </div>
        </div>

<?php

    } else {
        
        // list of posts
        
        echo '<h2>SSCAIT Blog</h2>';
        
        // blog settings
        $EXCERPT_LENGTH = 450;
        
        function getExcerpt($text, $cutOffLength) {
            if (strlen($text) <= $cutOffLength) return $text;
            $charAtPosition = "";
            $textLength = strlen($text);
            do {
                $cutOffLength++;
                $charAtPosition = substr($text, $cutOffLength, 1);
            } while ($cutOffLength < $textLength && $charAtPosition != " ");
            return substr($text, 0, $cutOffLength) . '...';
        }
        
        // get all the blogpost files
        $postFiles=array();
        if($dir=opendir('./blogposts/')){
            while($file=readdir($dir)){
                if($file!='.' && $file!='..' && $file!=basename(__FILE__) && (basename($file, ".md") == date('Y-m-d', strtotime(basename($file, ".md"))))){
                    $postFiles[]=$file;
                }
            }
            closedir($dir);
        }
        natsort($postFiles); // sort them
        $postFiles = array_reverse($postFiles, true);
        
        // print all post excerpts
        include './includes/Parsedown.php';
        $Parsedown = new Parsedown();
        foreach ($postFiles as $postFile) {
            
            $postDate = date('Y-m-d', strtotime(basename($postFile, ".md")));
            $contents = file_get_contents('./blogposts/'.$postFile);
            $contentsHTML = $Parsedown->text($contents);
            $permalink = $GLOBALS["DOMAIN_WITHOUT_SLASH"].'/index.php?action=blog&date='.$postDate;
            // minor HTML modifications
            $contentsHTML = str_ireplace('<h1>','<h3>',$contentsHTML);
            $contentsHTML = str_ireplace('</h1>','</h3>',$contentsHTML);
            $contentsHTML = str_ireplace('<h2>','<h3>',$contentsHTML);
            $contentsHTML = str_ireplace('</h2>','</h3>',$contentsHTML);
            $contentsHTML = str_ireplace('<h3>','<h3><a href="'.$permalink.'">',$contentsHTML);
            $contentsHTML = str_ireplace('</h3>','</a></h3>',$contentsHTML);
            $contentsHTML = preg_replace("/<img[^>]+\>/i", " ", $contentsHTML);
            
            echo '<div class="blog-excerpt">'.getExcerpt($contentsHTML,$EXCERPT_LENGTH).'</div>';
            echo '<div class="blog-date">Published on: '.$postDate.'</div>';
            echo '<div class="blog-permalink">Permalink: <a href="'.$permalink.'">'.$permalink.'</a></div>';
        }
        
    }
?>
</div>
