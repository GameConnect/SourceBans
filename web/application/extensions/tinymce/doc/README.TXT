Copyright (c) 2009 MetaYii

---------------------------------------------------------------------------------------------

Note: if you use this extension and you use the embed media plugin and the HTMLPurifier feature of Yii, you need to extend the HTMLPurifier class to include something like 
this:

class HTMLPurifier_Filter_YouTube extends HTMLPurifier_Filter
{

    public $name = 'YouTube';

    public function preFilter($html, $config, $context)
    {
        $pre_regex = '#<script [^>]+>write([A-Za-z]+)\(\{.*"src":"([A-Za-z0-9\-_\/\&\=\:\.\;\?\%]+)",.*?"width":"([0-9]+)","height":"([0-9]+).+?</script>#s';
        $pre_replace = '<span class="multimedia-embed">\1*\2*\3*\4</span>';
        $x = preg_replace($pre_regex, $pre_replace, $html);
        return $x;
    }

    public function postFilter($html, $config, $context)
    {
        $post_regex = '#<span class="multimedia-embed">([A-Za-z]+)\*([A-Za-z0-9\-_\/\&\=\:\.\;\?\%]+)\*([0-9]+)\*([0-9]+)</span>#';
        $post_replace = '<script type="text/javascript">write\1({"src":"\2","width":"\3","height":"\4"});</script> ';
        $x = preg_replace($post_regex, $post_replace, $html);
        return $x;
    }
}


, otherwise HTMLPurifier will mess your multimedia content (<object> and <script> tags are a security risk). 

Warning: if you embed anything from the outside (flash, wmv, whatever), or you allow javascript code in your input, you're likely opening a can of worms. Please read the 
HTMLPurifier documentation.
