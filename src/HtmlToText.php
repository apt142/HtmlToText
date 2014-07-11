<?php
/**
 * htmlToText Converter
 *
 * This is a derivative work of this repo: https://github.com/soundasleep/html2text
 *
 * Refactored to be object oriented and properly namespaced.
 *
 * PHP version 5.3
 *
 * @category html2Text
 * @package  Default
 * @author   Cris Bettis <apt142@apartment142.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://apartment142.com
 * @see      https://github.com/soundasleep/html2text
 */

namespace HtmlToText;

/**
 * htmlToText Converter
 *
 * Converts html input into a plain text output that is suitable to read.
 *
 * PHP version 5.3
 *
 * @category html2Text
 * @package  Default
 * @author   Cris Bettis <apt142@apartment142.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://apartment142.com
 * @see      https://github.com/soundasleep/html2text
 */


class HtmlToText {
    /** @var string $html */
    private $html = null;

    /** @var \DOMDocument $domDoc */
    private $document = null;

    // Ignore these tags
    private $ignoreTags = array('style', 'head', 'title', 'meta', 'script',
        'canvas', 'embed', 'video', 'audio');
    private $blockElements = array('article', 'header', 'aside',
        'hgroup', 'blockquote', 'hr', 'li', 'map', 'ol', 'caption', 'output',
        'p', 'pre', 'dd', 'progress', 'div', 'section', 'dl', 'table', 'dt',
        'tbody', 'embed', 'textarea', 'fieldset', 'tfoot', 'figcaption',
        'footer', 'tr', 'form', 'ul', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6');

    /**
     * Constructor
     *
     * @param string|null $html HTML string to translate (optional)
     *
     * @return \HtmlToText\HtmlToText
     */
    public function __construct($html = null) {
        $this->setHtml($html);
        $this->document = new \DOMDocument();
    }

    /**
     * Sets the HTML string to work on.
     *
     * @param string $html HTML String
     *
     * @return void
     */
    public function setHtml($html) {
        $this->html = $html;
    }

    /**
     * Tries to converts the given HTML into a plain text format
     *
     * @return string the HTML converted or empty string if not able to parse
     */
    function convert() {
        $output = '';
        $success = $this->document->loadHTML($this->html);
        if ($success) {
            $output = trim($this->render($this->document));

            // Trim each line
            $lines = array_map('trim', explode("\n", $output));
            $output = implode("\n", $lines);
        }
        return $output;
    }

    /**
     * Render a node
     *
     * @param \DOMNode $node     Dom Node
     * @param bool     $preceded Preceded by a sibling block element
     *
     * @return string
     */
    private function render(\DOMNode $node, $preceded = false) {
        $output = '';
        $tag = $node->nodeName;
        $lastChild = '';
        if ($tag == '#text') {
            // Convert two or more white space characters into a single
            $output = $this->cleanText($node->wholeText);
        } elseif ($node instanceof \DOMDocumentType) {
            $output = '';
        } else {
            $children = $node->childNodes;
            // If this has children
            if (($children !== null) && ($children->length > 0)) {
                $precededByBlock = false;
                foreach ($children as $child) {
                    // What child are we looking at?
                    $childName = $child->nodeName;
                    // If this is one we aren't ignoring, let's process
                    if (!in_array($childName, $this->ignoreTags)) {
                        // Render the child
                        $output .= $this->render($child, $precededByBlock);
                        $lastChild = $childName;
                        $precededByBlock = $this->isBlock($childName);
                    }
                }
            }
        }
        return $this->prefix($node, $preceded)
            . $output
            . $this->postFix(
                $node,
                ($this->isBlock($lastChild) && $this->isBlock($tag))
            );
    }

    /**
     * Determines prefix for tag
     *
     * @param \DOMNode $node      Dom Node
     * @param bool     $preceded This element is preceeded by another block.
     *
     * @return string
     */
    private function prefix($node, $preceded = false) {
        $name = strtolower($node->nodeName);
        $output = '';
        switch ($name) {
            case 'li':
                $output = ' * ';
                break;
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $output = "\n\n";
                break;
            case 'tr':
            case 'p':
                $output = "\n";
                break;
            case 'div':
                if (!$preceded) {
                    $output = "\n";
                }
                break;
            case 'a':
                // links are returned in [text](link) format
                $href = $node->getAttribute('href');
                if ($node->getAttribute('name') != null) {
                    $output = '[';
                } elseif ($href !== null) {
                    $text = $node->textContent;
                    if ($href !== $text
                        && $href !== 'mailto:' . $text
                        && $href !== 'http://' . $text
                        && $href !== 'https://' . $text
                    ) {
                        $output = '[';
                    }
                }
                break;
            default:
                $output = '';
                break;
        }
        return $output;
    }

    /**
     * Determines postfix for node
     *
     * @param \DOMNode $node     Dom Node
     * @param bool     $parented Parent is a block element
     *
     * @return string
     */
    private function postFix($node, $parented = false) {
        $name = strtolower($node->nodeName);
        $output = '';
        switch ($name) {
            case 'hr':
                $output = "------\n";
                break;

            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
            case 'tr':
            case 'br':
            case 'p':
            case 'div':
                if (!$parented) {
                    $output = "\n";
                }
                break;

            case 'td':
                $output = ' ';
                break;

            case 'a':
                // links are returned in [text](link) format
                $href = $node->getAttribute('href');
                if ($href == null) {
                    // it doesn't link anywhere
                    if ($node->getAttribute('name') != null) {
                        $output = ']';
                    }
                } else {
                    $text = $node->textContent;
                    if ($href == $text
                        || $href == 'mailto:' . $text
                        || $href == 'http://' . $text
                        || $href == 'https://' . $text
                    ) {
                        // link to the same address: just use link
                        $output = '';
                    } else {
                        // replace it
                        $output = '](' . $href . ')';
                    }
                }
                break;

            default:
                $output = '';
                break;
        }

        return $output;
    }

    /**
     * Cleans white space by removing carriage returns and consolidating
     * spacing
     *
     * @param string $text Text to clean
     *
     * @return string
     */
    private function cleanText($text) {
        $text = preg_replace('/\s/u', ' ', $text);
        return preg_replace('/\s{2,}/u', ' ', $text);
    }

    /**
     * Is this a block level tag?
     *
     * @param string $tag tag name
     *
     * @return bool
     */
    private function isBlock($tag) {
        return in_array($tag, $this->blockElements);
    }
}
