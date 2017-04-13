# Expect-CT Builder

[![Build Status](https://travis-ci.org/pepeverde/Expect-CT-Builder.svg?branch=master)](https://travis-ci.org/pepeverde/Expect-CT-Builder)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pepeverde/Expect-CT-Builder/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pepeverde/Expect-CT-Builder/?branch=master)

The `Expect-CT` HTTP header tells browsers to expect Certificate Transparency. For more information, see [this blog post by Scott Helme](https://scotthelme.co.uk/a-new-security-header-expect-ct/) and the [in-progress spec](https://datatracker.ietf.org/doc/draft-stark-expect-ct).

Expect-CT Builder was inspired by [ParagonIE\CSPBuilder](https://github.com/paragonie/csp-builder)

## Usage

```php
<?php

use \Pepeverde\ECTBuilder\ECTBuilder;

$expectCT = new ECTBuilder([
    'enforce' => true,
    'maxAge' => 30,
    'reportUri' => 'https://example.org/report'
]);
$expectCT->sendECTHeader();
```

## Inject an Expect-CT into a PSR-7 message

Instead of invoking `sendECTHeader()`, you can instead inject the headers into
your PSR-7 message object by calling it like so:

```php
/**
 * $yourMessageHere is an instance of an object that implements 
 * \Psr\Http\Message\MessageInterface
 *
 * Typically, this will be a Response object that implements 
 * \Psr\Http\Message\ResponseInterface
 *
 * @ref https://github.com/guzzle/psr7/blob/master/src/Response.php
 */
$expectCT->injectECTHeader($yourMessageHere);
```
