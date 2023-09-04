<?php

declare(strict_types=1);

namespace PsPark\Request;

use PHPUnit\Framework\TestCase;
use PsPark\Enum\ApiUrl;
use PsPark\Enum\ApiVersion;

class HttpRequestTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $defaultHeaders = [
        'Accept: application/json',
    ];

    private array $defaultOptions = [
        CURLOPT_USERAGENT      => 'PsPark-PHP-SDK',
        CURLOPT_HEADER         => false,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_RETURNTRANSFER => true,
    ];

    private HttpRequest $request;

    protected function setUp(): void
    {
        $this->request = new HttpRequest();
    }

    public function testDefaultParams(): void
    {
        $this->assertSame($this->defaultHeaders, $this->request->getHeaders());
        $this->assertSame($this->defaultOptions, $this->request->getOptions());
    }

    public function testWithHeaders(): void
    {
        $testHeaders = [
            'header-1: value-1',
            'header-2: value-2',
            'header-3: value-3',
        ];

        $this->request->withHeaders($testHeaders);

        $this->assertSame($testHeaders, $this->request->getHeaders());
    }

    public function testWithHeader(): void
    {
        $this->request
            ->withHeader('header-1', 'value-1')
            ->withHeader('header-2', 'value-2')
            ->withHeader('header-3', 'value-3');

        $this->assertSame(
            array_merge(
                $this->defaultHeaders,
                [
                    'header-1: value-1',
                    'header-2: value-2',
                    'header-3: value-3',
                ]
            ),
            $this->request->getHeaders()
        );
    }

    public function testGetHeaders(): void
    {
        $this->assertSame($this->defaultHeaders, $this->request->getHeaders());
    }

    public function testHasHeader(): void
    {
        $this->assertTrue($this->request->hasHeader('Accept: application/json'));
        $this->assertFalse($this->request->hasHeader('Client'));
    }

    public function testGetOptions(): void
    {
        $this->assertIsArray($this->request->getOptions());
        $this->assertEquals($this->defaultOptions, $this->request->getOptions());
    }

    public function testWithUrl(): void
    {
        $testUrl = ApiUrl::WALLET_WITHDRAWAL_CREATE;

        $this->request->withUrl($testUrl);
        $this->assertSame($testUrl, $this->request->getUrl());
    }

    public function testGetFullUrl(): void
    {
        $testUrl = ApiUrl::WALLET_WITHDRAWAL_CREATE;
        $baseUrl = 'https://api.pspark.io';
        $this->request->withUrl($testUrl);

        $this->assertSame(
            sprintf('%s/%s/%s', $baseUrl, ApiVersion::getDefault()->value, $testUrl->value),
            $this->request->getFullUrl()
        );
    }

    public function testGetMethod(): void
    {
        $this->request->withMethod(Request::METHOD_GET);

        $this->assertSame(Request::METHOD_GET, $this->request->getMethod());
    }

    public function testGetBody(): void
    {
        $testData = [
            'foo' => 'bar',
            'boo' => 'baz',
        ];

        $this->request->withBody($testData);

        $this->assertSame($testData, $this->request->getBody());
        $this->assertSame(json_encode($testData, JSON_THROW_ON_ERROR), $this->request->getBodyAsJson());
    }

    public function testAddUrlParamsMethod(): void
    {
        $testInvoiceUrl = ApiUrl::WALLET_INVOICE_CREATE;
        $testInvoiceWalletId = 'DA53924D-6888-4A37-B211-B2797C4BD496';
        $baseUrl = 'https://api.pspark.io';

        $invoiceRequest = clone $this->request;

        $invoiceRequest
            ->withUrl($testInvoiceUrl)
            ->addUrlParams(ApiUrl::WALLET_ID_PARAM_NAME->value, $testInvoiceWalletId);

        $this->assertSame(
            sprintf(
                '%s/%s/%s',
                $baseUrl,
                ApiVersion::getDefault()->value,
                strtr($testInvoiceUrl->value, [':' . ApiUrl::WALLET_ID_PARAM_NAME->value => $testInvoiceWalletId]),
            ),
            $invoiceRequest->getFullUrl()
        );

        $balanceRequest = clone $this->request;
        $testBalanceUrl = ApiUrl::BALANCES;
        $balanceRequest
            ->withUrl($testBalanceUrl)
            ->addUrlParams(ApiUrl::WALLET_ID_PARAM_NAME->value, $testInvoiceWalletId);

        $this->assertSame(
            sprintf('%s/%s/%s', $baseUrl, ApiVersion::getDefault()->value, $testBalanceUrl->value),
            $balanceRequest->getFullUrl()
        );
    }
}
