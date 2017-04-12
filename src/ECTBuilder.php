<?php

namespace Pepeverde\ECTBuilder;

use Psr\Http\Message\MessageInterface;

class ECTBuilder
{
    private $policies;
    private $needsCompile = true;
    private $compiled = '';

    /**
     * @param array $policy
     */
    public function __construct(array $policy = [])
    {
        $this->policies = $policy;
    }

    /**
     * Compile the current policies into an Expect-CT header
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function compile()
    {
        $compiled = [];

        // Set max-age to 0 if there is no policy set in constructor
        if (empty($this->policies)) {
            $this->policies['maxAge'] = 0;
        }

        // max-age is mandatory
        if (!isset($this->policies['maxAge'])) {
            throw new \InvalidArgumentException('maxAge is mandatory. Set a positive integer as maxAge value.');
        }

        if (isset($this->policies['enforce']) && (bool)$this->policies['enforce'] === true) {
            $compiled[] = 'enforce';
        }

        $compiled[] = 'max-age=' . $this->parseMaxAge($this->policies['maxAge']);

        if (isset($this->policies['reportUri']) && !empty($this->policies['reportUri'])) {
            $compiled[] = 'report-uri="' . $this->policies['reportUri'] . '"';
        }

        $this->compiled = \implode('; ', $compiled);
        $this->needsCompile = false;

        return $this->compiled;
    }

    /**
     * @param int $maxAge
     * @return int
     * @throws \InvalidArgumentException
     */
    private function parseMaxAge($maxAge)
    {
        if (is_array($maxAge)) {
            throw new \InvalidArgumentException('maxAge in an array so it is not a valid value for maxAge. Use a positive integer');
        }

        if (!is_int($maxAge) || $maxAge < 0) {
            throw new \InvalidArgumentException($maxAge . ' is not a valid value for maxAge. Use a positive integer');
        }

        return $maxAge;
    }

    /**
     * Get the formatted Expect-CT header
     *
     * @return string
     * @throws \InvalidArgumentException::class
     */
    public function getCompiledHeader()
    {
        if ($this->needsCompile) {
            $this->compile();
        }

        return $this->compiled;
    }

    /**
     * PSR-7 header injection
     *
     * @param MessageInterface $message
     * @return MessageInterface
     * @throws \InvalidArgumentException
     */
    public function injectECTHeader(MessageInterface $message)
    {
        if ($this->needsCompile) {
            $this->compile();
        }
        $message = $message->withAddedHeader('Expect-CT', $this->compiled);

        return $message;
    }

    /**
     * Send the compiled Expect-CT as a header()
     *
     * @return boolean
     * @throws \Exception
     */
    public function sendECTHeader()
    {
        if (\headers_sent()) {
            throw new \Exception('Headers already sent!');
        }
        if ($this->needsCompile) {
            $this->compile();
        }

        \header('Expect-CT: ' . $this->compiled);

        return true;
    }
}
