<?php 

/**
 * GeShiHighlighter
 * @author francis
 *
 * Using GeSHi highlight engine to highlight, instead CTextHighlighter
 * because it lacks languages to process.
 *
 */

/*
 * Usages:
 *
 * when using like a COutputFilter, just like CTextHighlighter
 * when using standalone, just follow codes below
 *
 * $highlighter = new GeSHiHighlighter();
 * $highlighter->language = 'php';
 *
 * echo $highlighter->highlight('<?php echo "Hello World"; ?>');
 *
 *
 * Tips:
 *
 * you can set code container htmlOptions follow codes below
 * $highlighter->containerOptions = array(
 *     'class' => 'cb_highlight',
 * ));
 * BE ADVISED: only id, style, class is support
 *
 */

class GeSHiHighlighter extends COutputProcessor
{
    /* language to highlight */
    public $language = 'text';
    /* show linenumber for eachline or not */
    public $showLineNumbers = false;
    /* htmlOptions for code container */
    public $containerOptions = array();

    /**
     * Override COutputProcessor::processOutput method to invoke in filter
     * @param string content to output
     * @return string content after highlighted
     */
    public function processOutput($output)
    {
        $output = $this->highlight($output);

        return parent::processOutput($output);
    }

    /**
     * Highlight the content
     * @param string code to highlighted
     * @return string highlighted by defined language in html tags
     */
    public function highlight($content)
    {
        include_once dirname(__FILE__) . '/geshi.php';

        if(!class_exists('GeSHi')) {
            throw new CHttpException(500, 'GeSHi core missing');
        }

        $geshi = new GeSHi($content, strtolower($this->language));

        if($this->showLineNumbers) $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
        foreach($this->containerOptions as $attribute => $options) {
            if(method_exists($geshi, 'set_overall_'.$attribute)) {
                call_user_func(array($geshi, 'set_overall_'.$attribute), $options);
            }
        }

        return $geshi->parse_code();
    }
}
