<?php

require __DIR__ . '/vendor/autoload.php';
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\DocParser;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use League\CommonMark\HtmlElement;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

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
		return parse_url($url, PHP_URL_HOST) !== $this->host;
	}
}

class TwitterHandleParser extends AbstractInlineParser {
    public function getCharacters() {
        return ['@'];
    }
    public function parse(InlineParserContext $inlineContext) {
        $cursor = $inlineContext->getCursor();
        // The @ symbol must not have any other characters immediately prior
        $previousChar = $cursor->peek(-1);
        if ($previousChar !== null && $previousChar !== ' ' && $previousChar !== "\n") {
            // peek() doesn't modify the cursor, so no need to restore state first
            return false;
        }
        // Save the cursor state in case we need to rewind and bail
        $previousState = $cursor->saveState();
        // Advance past the @ symbol to keep parsing simpler
        $cursor->advance();
        // Parse the handle
        $handle = $cursor->match('/^[A-Za-z0-9_]{1,15}(?!\w)/');
        if (empty($handle)) {
            // Regex failed to match; this isn't a valid Twitter handle
            $cursor->restoreState($previousState);
            return false;
        }
        $profileUrl = 'https://twitter.com/' . $handle;
        $inlineContext->getContainer()->appendChild(new Link($profileUrl, '@' . $handle));
        return true;
    }
}

function genHTML($path, $host) {

	$html = "";

	$html .= '<!DOCTYPE html><html><head>';

	$html .= '<meta charset="utf8">';

	$html .= '<style>';
	$html .= file_get_contents("style.css");
	$html .= '</style>';

	$html .= '<script>';
	$html .= file_get_contents("script.js");
	$html .= '</script>';

	$html .= '</head><body><article>';

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
	$environment->addInlineParser(new TwitterHandleParser());
	$environment->addInlineRenderer('League\CommonMark\Inline\Element\Link', new ExternalLinkRenderer($host));

	$parser = new DocParser($environment);
	$htmlRenderer = new HtmlRenderer($environment);
	$document = $parser->parse(file_get_contents($path));
	$html .= $htmlRenderer->renderBlock($document);

	$html .= '</article></body></html>';
	return $html;
}

?>
