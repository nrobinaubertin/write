<?php

require_once __DIR__ . '/vendor/autoload.php';
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\DocParser;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use League\CommonMark\HtmlElement;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

class DelayedLoadingImageRenderer implements InlineRendererInterface
{
	private $host;

	public function __construct($host)
	{
		$this->host = $host;
	}

	public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
	{
		$attrs = array();

		if ($inline->getUrl() != "") {
			$attrs['data-src'] = $inline->getUrl();
		}

        return new HtmlElement('img', $attrs, $htmlRenderer->renderInlines($inline->children()));
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

		$attrs['href'] = $htmlRenderer->escape($inline->getUrl(), true);

		if (isset($inline->attributes['title'])) {
			$attrs['title'] = $htmlRenderer->escape($inline->data['title'], true);
		}

		if ($this->isExternalUrl($inline->getUrl())) {
			$attrs['target'] = '_blank';
		}

		return new HtmlElement('a', $attrs, $htmlRenderer->renderInlines($inline->children()));
	}

	private function isExternalUrl($url)
    {
		return parse_url($url, PHP_URL_HOST) != NULL && parse_url($url, PHP_URL_HOST) !== $this->host;
	}
}

function parseMarkDown($markdown) {
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
	//$environment->addInlineRenderer('League\CommonMark\Inline\Element\Image', new DelayedLoadingImageRenderer($host));

	$parser = new DocParser($environment);
	$htmlRenderer = new HtmlRenderer($environment);
	$document = $parser->parse($markdown);
	return $htmlRenderer->renderBlock($document);
}
