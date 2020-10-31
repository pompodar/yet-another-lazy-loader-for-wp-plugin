<?php
class LozadProcessing {
	
	public function delTypeAttributeForJavascriptResource(&$content)
    {
        $content = str_replace(['type="text/javascript"', 'type=\'text/javascript\''], '', $content);
    }

    private function getEmptyImage()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABLAAAAKkAQMAAAAqeFQ7AAAAA3NCSVQICAjb4U/gAAAABlBMVEX///////9VfPVsAAAAAnRSTlMA/1uRIrUAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzbovLKMAAAAFnRFWHRDcmVhdGlvbiBUaW1lADAxLzI5LzE536V52wAAAHpJREFUeJztwQENAAAAwqD3T20ON6AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIAHA47LAAHp9LjEAAAAAElFTkSuQmCC';
    }

    private function getLazyloadScriptBaseClass()
    {
        return 'lazyload';
    }

    private function addLazyloadClassToTheImgElement($replaceHTML)
    {
        $lazyloadClass = $this->getLazyloadScriptBaseClass();
        // add the lazy class to the img element
        if ( preg_match( '/class=["\']/i', $replaceHTML ) ) {
            $replaceHTML = preg_replace( '/class=(["\'])(.*?)["\']/is', 'class=$1'.$lazyloadClass.' $2$1', $replaceHTML );
        } else {
            $replaceHTML = preg_replace( '/<img/is', '<img class="'.$lazyloadClass.'"', $replaceHTML );
        }

        return $replaceHTML;
    }

    private function getSkipImagesRegexLazyload($additionalClass = null)
    {
        $lazyloadClass = $this->getLazyloadScriptBaseClass();;
        $skippedClasses = array('no-' . $lazyloadClass, $lazyloadClass);
        if ($additionalClass) {
            $skippedClasses[] = $additionalClass;
        }

        $skipImagesPregQuoted = array_map( function($what) {
            return str_replace( array( '\*', '\.' ), '', preg_quote( $what, '/' ) );
        }, $skippedClasses );

        return sprintf( '/class=".*(%s).*"/s', implode( '|', $skipImagesPregQuoted ) );
    }

    private function getContentImages($content)
    {
        $matches = [];

        preg_match_all( '/<img[^>]*(?:(?:src=\"[^\"\']*\")|(?:src=\'[^\'\"]*\'))[^>]*>/im', $content, $matches);

        return $matches;
    }

    private function getLazyLoadVideoIFrameFromContent($content)
    {
        $matches = [];
//        preg_match_all( '/<iframe[^>]*(?:(?:src=\"[^\"\']+\")|(?:src=\'[^\'\"]+\'))[^>]*>*<\/iframe*>/im', $content, $matches);
        preg_match_all( '/<iframe[^>]*?>(.*?)<\/iframe>/sim', $content, $matches);

        return $matches;
    }

    private function getTagsWithBgUrl($content)
    {
        $tagsWithBgUrl = [];
        preg_match_all('/<[^>]*?style=[^>]*?background-image\s*?:\s*?url\([^>]+\)[^>]*?>/', $content, $tagsWithBgUrl);

        return $tagsWithBgUrl;
    }

  	public function processImages(&$content) {
        $search = [];
        $replace = [];
        $contentImages = $this->getContentImages($content);
        $skipImagesRegex = $this->getSkipImagesRegexLazyload();

        foreach ($contentImages[0] as $imgHTML ) {

            if(!preg_match( $skipImagesRegex, $imgHTML )) {

                $doc = new DOMDocument();
                $doc->loadHTML($imgHTML);
                $xpath = new DOMXPath($doc);
                $imgSrc = $xpath->evaluate("string(//img/@src)");

                unset($doc);
                unset($xpath);

                $replaceHTML = preg_replace( '/<img(.*?)src=/is', '<img decoding="async" $1src="'.$imgSrc.'" data-srcset="'.$imgSrc.'" data-lazy-type="image" data-original=', $imgHTML);

                $replaceHTML = $this->addLazyloadClassToTheImgElement($replaceHTML);

                if (strripos($replaceHTML, ' srcset=') === false) {
                    $replaceHTML = preg_replace( '/<img/is', '<img srcset="' .$this->getEmptyImage() . '"', $replaceHTML);
                }

                if (strripos($replaceHTML, 'alt=') === false) {
                    $replaceHTML = preg_replace( '/<img/is', '<img alt="img"', $replaceHTML);
                }

                array_push( $search, $imgHTML );
                array_push( $replace, $replaceHTML);

            }
        }

        $search = array_unique( $search );
        $replace = array_unique( $replace );

        $content = str_replace( $search, $replace, $content );
    }	
	
    public function processBackground(&$content, $imageSet = false)
    {
        $tagsWithBgUrl = $this->getTagsWithBgUrl($content);

        $search = [];
        $replace = [];

        $skipTagClassesRegex = $this->getSkipImagesRegexLazyload();

        foreach ($tagsWithBgUrl[0] as $htmlTag) {
            if (preg_match($skipTagClassesRegex, $htmlTag) !== 0) {
                continue;
            }

            $bgImgMatches = [];
            preg_match('/background-image:\s*url\s*\(\s*[\'"]?([^\'"]*)[\'"]?\)/im', $htmlTag, $bgImgMatches);
            $bgImg = isset($bgImgMatches[1]) ? $bgImgMatches[1] : null;
            if (empty($bgImg)) {
                continue;
            }

            $bgImgSlashes = preg_replace(['/\//', '/\./'], ['\/', '\.'], $bgImg);

            // del bg image from style tag
            $replaceHtmlTag = preg_replace('/(.*?style=.*?)background-image:\s*url\([\'"]' . $bgImgSlashes . '[\'"]\);*\s*(.*?)/is', '$1$2', $htmlTag);
            // add lazyload class
            $replaceHtmlTag = preg_replace('/(.*?)class=([\'"])(.*?)/is', '$1class=$2' . $this->getLazyloadScriptBaseClass() . ' $3', $replaceHtmlTag);

            $doc = new DOMDocument();
            $doc->loadHTML($replaceHtmlTag);
            $xpath = new DOMXPath($doc);
            $nodes = $xpath->evaluate('//*[@style]');
            foreach ($nodes as $node) {
                if (strlen($node->getAttribute('style')) < 5) { // if empty style tag now delete style tag
                    $replaceHtmlTag = preg_replace('/(.*?)style=[\'"].*[\'"](.*?)/is', '$1$2', $replaceHtmlTag);
                }
            }
            unset($doc);
            unset($xpath);

            // https://apoorv.pro/lozad.js/
            if ($imageSet) {
                // for use with responsive background images (image-set)
                // <div class="lozad" data-background-image-set="url('photo.jpg') 1x, url('photo@2x.jpg') 2x"></div>
                $bg2xImg = preg_replace('/\./', '@2x.', $bgImg);
                $replaceHtmlTag = preg_replace('/<(.*)>/is', '<$1 data-background-image-set="url(\'' . $bgImg . '\') 1x, url(\'' . $bg2xImg . '\') 2x">', $replaceHtmlTag);
            } else {
                //for use with background images
                // <div class="lozad" data-background-image="image.png"></div>
                $replaceHtmlTag = preg_replace('/<(.*)>/is', '<$1 data-background-image="' . $bgImg . '">', $replaceHtmlTag);
            }

            array_push($search, $htmlTag);
            array_push($replace, $replaceHtmlTag);
        }

        $search = array_unique($search);
        $replace = array_unique($replace);

        $content = str_replace($search, $replace, $content);
    }

    public function processIframe(&$content)
    {
        $search = [];
        $replace = [];
        $matches = $this->getLazyLoadVideoIFrameFromContent($content);

        $skipFramesRegex = $this->getSkipImagesRegexLazyload('if-video');
        $baseLazyloadClass = $this->getLazyloadScriptBaseClass();

        foreach ($matches[0] as $iframeHTML) {
            if(!preg_match( $skipFramesRegex, $iframeHTML)) {

                $replaceIframeHtml = '<div class="' . $baseLazyloadClass . '" data-original_content="' . base64_encode($iframeHTML) . '"></div>';

                array_push($search, $iframeHTML);
                array_push($replace, $replaceIframeHtml);
            }
        }

        $search = array_unique($search);
        $replace = array_unique($replace);
        $content = str_replace($search, $replace, $content);

    }
	
    public function processVideo(&$content)
    {
        $search = [];
        $replace = [];
        $videoWithPosterTags = [];
        preg_match_all( '/<video[^>]*?>(.*?)<\/video>/sim', $content, $videoWithPosterTags);

        $skipTagClassesRegex = $this->getSkipImagesRegexLazyload();

        foreach ($videoWithPosterTags[0] as $videoWithPosterTag) {
            if (preg_match($skipTagClassesRegex, $videoWithPosterTag) !== 0) {
                continue;
            }
            $replaceVideoWithPosterTag = preg_replace('/<(.*)poster=/is', '<$1 data-poster=', $videoWithPosterTag);

            $baseLazyloadClass = $this->getLazyloadScriptBaseClass();
            if (preg_match( '/class=["\']/i', $replaceVideoWithPosterTag ) ) {
                $replaceVideoWithPosterTag = preg_replace( '/class=(["\'])(.*?)["\']/is', "class=$1{$baseLazyloadClass} $2$1", $replaceVideoWithPosterTag);
            } else {
                $replaceVideoWithPosterTag = preg_replace( '/<video/is', '<video class="'.$baseLazyloadClass.'"', $replaceVideoWithPosterTag);
            }

            array_push($search, $videoWithPosterTag);
            array_push($replace, $replaceVideoWithPosterTag);

            $sourceTagMatches = [];
            preg_match_all('/<source[^>]*?\/>/sim', $videoWithPosterTag, $sourceTagMatches);
			$src = $this->getEmptyImage();
            foreach($sourceTagMatches[0] as $sourceTagMatch) {
                $sourceTagReplace = preg_replace('/<(.*)src=/is', '<$1 src="'.$src.'" data-src=', $sourceTagMatch);
                array_push($search, $sourceTagMatch);
                array_push($replace, $sourceTagReplace);
            }
        }

        $search = array_unique($search);
        $replace = array_unique($replace);

        $content = str_replace($search, $replace, $content);
    }

}

