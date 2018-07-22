<?php

require_once __DIR__ . '/../vendor/autoload.php';
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\DocParser;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Environment;
use League\CommonMark\HtmlElement;
use League\CommonMark\HtmlRenderer;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use League\CommonMark\Util\Xml;

class PictureRenderer implements InlineRendererInterface
{
    private $pathToGD;
    private $host;
    private $dir;
    private $root_path;

    public function __construct($root_path, $host, $dir)
    {
        $this->pathToGD = $root_path."_gd";
        $this->root_path = $root_path;
        $this->host = $host;
        $this->dir = $dir;
    }

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        $attrs = array();

        $innerHTML = "";
        if ($inline->getUrl()) {
            $src = $inline->getUrl();
            $original = $src;
            if (!$this->isExternalUrl($src)) {
                $src = $this->dir.$src;
                $original = $this->root_path."/".$src;
            }
            for ($i = 0; $i < 20; $i++) {
                $screenWidth = 100 + 100 * $i;
                $size = min(800, $screenWidth);
                $innerHTML .= '<source srcset="'.$this->pathToGD.'?url='.urlencode($src).'&w='.$size.'" media="(max-width: '.$screenWidth.'px)">';
            }
            $innerHTML .= '<img src="'.$original.'">';
        }

        return new HtmlElement('picture', $attrs, $innerHTML);
    }

    private function isExternalUrl($url)
    {
        return parse_url($url, PHP_URL_HOST) != null && parse_url($url, PHP_URL_HOST) !== $this->host;
    }
}


class ExternalLinkRenderer implements InlineRendererInterface
{
    private $host;

    public function __construct($host)
    {
        $this->host = $host;
    }

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (!($inline instanceof Link)) {
            throw new \InvalidArgumentException('Incompatible inline type: ' . get_class($inline));
        }

        $attrs = array();

        $attrs['href'] = Xml::escape($inline->getUrl(), true);

        if (isset($inline->attributes['title'])) {
            $attrs['title'] = Xml::escape($inline->data['title'], true);
        }

        if ($this->isExternalUrl($inline->getUrl())) {
            $attrs['target'] = '_blank';
        }

        return new HtmlElement('a', $attrs, $htmlRenderer->renderInlines($inline->children()));
    }

    private function isExternalUrl($url)
    {
        return parse_url($url, PHP_URL_HOST) != null && parse_url($url, PHP_URL_HOST) !== $this->host;
    }
}

function parseMarkDown($markdown, $root_path, $dir)
{
    $config = [
        'renderer' => [
            'block_separator' => "\n",
            'inner_separator' => "\n",
            'soft_break'      => "\n",
        ],
        'enable_em' => true,
        'enable_strong' => true,
        'use_asterisk' => true,
        'use_underscore' => true,
    ];

    $environment = Environment::createCommonMarkEnvironment();
    $environment->setConfig($config);
    $environment->addInlineRenderer('League\CommonMark\Inline\Element\Link', new ExternalLinkRenderer($_SERVER["HTTP_HOST"]));
    $environment->addInlineRenderer('League\CommonMark\Inline\Element\Image', new PictureRenderer($root_path, $_SERVER["HTTP_HOST"], $dir));

    $parser = new DocParser($environment);
    $htmlRenderer = new HtmlRenderer($environment);
    $document = $parser->parse($markdown);
    return $htmlRenderer->renderBlock($document);
}
