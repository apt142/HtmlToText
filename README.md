#HtmlToText
[![Build Status](https://travis-ci.org/apt142/html2text.svg?branch=master)](https://travis-ci.org/apt142/html2text)

html2text is a very simple script that uses PHP's DOM methods to load from HTML,
 and then iterates over the resulting DOM to output plain text.

 Example:

```html
<html>
<title>Ignored Title</title>
<body>
  <h1>Hello, World!</h1>

  <p>This is some e-mail content.
  Even though it has whitespace and newlines, the e-mail converter
  will handle it correctly.

  <p>Even mismatched tags.</p>

  <div>A div</div>
  <div>Another div</div>
  <div>A div<div>within a div</div></div>

  <a href="http://foo.com">A link</a>

</body>
</html>
```

Will be converted into:

```text
Hello, World!

This is some e-mail content. Even though it has whitespace and newlines, the e-mail converter will handle it correctly.

Even mismatched tags.
A div
Another div
A div
within a div
[A link](http://foo.com)
```

This is a fork of: (https://github.com/soundasleep/html2text)

## Installing

You can use [Composer](http://getcomposer.org/) to add the [package](https://packagist.org/packages/soundasleep/jsonrpcclient) to your project:

```json
{
  "require": {
    "apt142/html2text": "~0.1.0"
  }
}
```

And then use it quite simply:

```php
$converter = new \HtmlToText\HtmlToText($html);
$text = $converter->convert();

// Or

$converter = new \HtmlToText\HtmlToText();
$convertor->set($html);
$text = $converter->convert();
```
