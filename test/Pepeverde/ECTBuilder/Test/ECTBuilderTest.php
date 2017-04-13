<?php

namespace Pepeverde\ECTBuilder\Test;

use Pepeverde\ECTBuilder\ECTBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

class ECTBuilderTest extends TestCase
{
    /**
     * @dataProvider goodPolicyProvider
     * @param array $policy
     * @param string $expected
     */
    public function testBuilder($policy, $expected)
    {
        $expectCT = new ECTBuilder($policy);
        $this->assertEquals(
            $expected,
            $expectCT->getCompiledHeader()
        );
    }

    /**
     * @dataProvider badPolicyProvider
     * @expectedException \InvalidArgumentException
     * @param array $badPolicy
     */
    public function testBadPolicy($badPolicy)
    {
        $expectCT = new ECTBuilder($badPolicy);
        $expectCT->getCompiledHeader();
    }

    /**
     * @dataProvider badPolicyNotArrayProvider
     * @param array $badPolicy
     */
    public function testBadNotArrayPolicy($badPolicy)
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->expectException(\TypeError::class);
        } else {
            $this->markTestIncomplete(
                'This test has not been implemented yet on PHP < 7.0.'
            );
        }

        $expectCT = new ECTBuilder($badPolicy);
        $expectCT->getCompiledHeader();
    }

    /**
     * @dataProvider badMaxAgeProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $badMaxAge
     */
    public function testBadMaxAge($badMaxAge)
    {
        $expectCT = new ECTBuilder(['maxAge' => $badMaxAge]);
        $expectCT->getCompiledHeader();
    }

    public function goodPolicyProvider()
    {
        return [
            'empty policy' => [
                [],
                'max-age=0'
            ],
            'maxAge only' => [
                [
                    'maxAge' => 0
                ],
                'max-age=0'
            ],
            'enforce false' => [
                [
                    'enforce' => false,
                    'maxAge' => 0
                ],
                'max-age=0'
            ],
            'enforce true' => [
                [
                    'enforce' => true,
                    'maxAge' => 0
                ],
                'enforce; max-age=0'
            ],
            'maxAge and reportUri set' => [
                [
                    'maxAge' => 0,
                    'reportUri' => '/report-url'
                ],
                'max-age=0; report-uri="/report-url"'
            ],
            'maxAge and reportUri set, enforce false' => [
                [
                    'enforce' => false,
                    'maxAge' => 0,
                    'reportUri' => '/report-url'
                ],
                'max-age=0; report-uri="/report-url"'
            ],
            'maxAge and reportUri set, enforce true' => [
                [
                    'enforce' => true,
                    'maxAge' => 0,
                    'reportUri' => '/report-url'
                ],
                'enforce; max-age=0; report-uri="/report-url"'
            ],
        ];
    }

    public function badPolicyProvider()
    {
        return [
            'enforce true only' => [['enforce' => true]],
            'enforce false only' => [['enforce' => false]],
            'reportUri only' => [['reportUri' => '/report-url']],
            'enforce true and reportUri' => [['enforce' => true, 'reportUri' => '/report-url']],
            'enforce false and reportUri' => [['enforce' => false, 'reportUri' => '/report-url']],
        ];
    }

    public function badPolicyNotArrayProvider()
    {
        return [
            'boolean' => [true],
            'number' => [123],
            'string' => [uniqid('ExpectCT', true)],
        ];
    }

    public function badMaxAgeProvider()
    {
        return [
            'boolean true' => [true],
            'boolean false' => [false],
            'string "0' => ['0'],
            'string "123' => ['123'],
            'valid integer in array' => [[123]],
            'string in array' => [['123']],
            'negative int -1' => [-1],
            'negative int -123' => [-123],
        ];
    }

    public function testInjectECTHeader()
    {
        $modifiedMessage = $this->getMockBuilder(MessageInterface::class)->getMock();
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();

        $expectCT = new ECTBuilder([
            'enforce' => true,
            'maxAge' => 0,
            'reportUri' => '/report-url'
        ]);
        $header = $expectCT->compile();
        $message
            ->expects($this->once())
            ->method('withAddedHeader')
            ->with('Expect-CT', $header)
            ->willReturn($modifiedMessage);
        $this->assertSame($modifiedMessage, $expectCT->injectECTHeader($message));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendHeader()
    {
        $expectCT = new ECTBuilder([
            'enforce' => true,
            'maxAge' => 30,
            'reportUri' => '/report-url'
        ]);
        $expectCT->sendECTHeader();
        $headerList = xdebug_get_headers();
        $this->assertEquals($headerList[0], 'Expect-CT: ' . $expectCT->getCompiledHeader());
    }
}
