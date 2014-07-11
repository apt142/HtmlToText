<?php
/**
 * HtmlToTextTest
 *
 * PHP version 5.3
 *
 * @category html2Text
 * @package default
 * @author   Cris Bettis <apt142@apartment142.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://apartment142.com
 */

namespace tests;

use HtmlToText;

/**
 * Class HtmlToTextTest
 *
 * What does this class do?
 *
 * @category html2Text
 * @package default
 * @author   Cris Bettis <apt142@apartment142.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://apartment142.com
 */
class HtmlToTextTest extends \PHPUnit_Framework_TestCase {

    /**
     * Standardize newlines into one convention. (Unix)
     *
     * @param string text text with any number of \r, \r\n and \n combinations
     *
     * @return string the fixed text
     */
    private function cleanLineEndings($text) {
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);
        return trim($text);
    }

    public function dataTestRenders() {
        return array(
             array("basic"),
             array("anchors"),
             array("test3"),
             array("test4"),
             array("breaking"),
             array("more-anchors"),
             array("list")
        );
    }

    /**
     * Test the set command
     *
     * @dataProvider dataTestRenders
     */
    public function testRender($stubName) {
        $path = realpath('./tests/stubs/') . '/';
        $html = file_get_contents($path . $stubName . '.html');
        $text = file_get_contents($path . $stubName . '.txt');
        $text = $this->cleanLineEndings($text);

        $converter = new \HtmlToText\HtmlToText($html);
        $actual = $converter->convert();

        $this->assertSame($text, $actual);
    }

}
