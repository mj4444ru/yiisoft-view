<?php

declare(strict_types=1);

namespace Yiisoft\View\Tests;

use Yiisoft\Files\FileHelper;
use Yiisoft\View\Tests\Mocks\WebViewPlaceholderMock;
use Yiisoft\View\WebView;

final class WebViewTest extends \Yiisoft\View\Tests\TestCase
{
    private string $dataDir;
    private string $layoutPath;

    /**
     * @var string path for the test files.
     */
    private string $testViewPath = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataDir = dirname(__DIR__) . '/tests/public/view';
        $this->layoutPath = $this->dataDir . '/layout.php';
        $this->testViewPath = sys_get_temp_dir() . '/' . str_replace('\\', '_', get_class($this)) . uniqid('', false);

        FileHelper::createDirectory($this->testViewPath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        FileHelper::removeDirectory($this->testViewPath);
    }

    public function testRegisterJsVar(): void
    {
        $this->webView->registerJsVar('username', 'samdark');
        $html = $this->webView->render('//layout.php', ['content' => 'content']);
        $this-> assertStringContainsString("<script>var username = \"samdark\";</script>\n</head>", $html);

        $this->webView->registerJsVar('objectTest', [
            'number' => 42,
            'question' => 'Unknown',
        ]);
        $html = $this->webView->render('//layout.php', ['content' => 'content']);
        $this->assertStringContainsString("<script>var objectTest = {\"number\":42,\"question\":\"Unknown\"};</script>\n</head>", $html);
    }

    public function testRegisterJsFileWithAlias(): void
    {
        $this->webView->registerJsFile($this->aliases->get('@baseUrl/js/somefile.js'), ['position' => WebView::POSITION_HEAD]);
        $html = $this->webView->renderFile($this->layoutPath, ['content' => 'content']);
        $this->assertStringContainsString("<script src=\"/baseUrl/js/somefile.js\"></script>\n</head>", $html);

        $this->webView->registerJsFile($this->aliases->get('@baseUrl/js/somefile.js'), ['position' => WebView::POSITION_BEGIN]);
        $html = $this->webView->renderFile($this->layoutPath, ['content' => 'content']);
        $this->assertStringContainsString("<body>\n<script src=\"/baseUrl/js/somefile.js\"></script>\n", $html);

        $this->webView->registerJsFile($this->aliases->get('@baseUrl/js/somefile.js'), ['position' => WebView::POSITION_END]);
        $html = $this->webView->renderFile($this->layoutPath, ['content' => 'content']);
        $this->assertStringContainsString("<script src=\"/baseUrl/js/somefile.js\"></script>\n</body>", $html);
    }

    public function testRegisterCssFileWithAlias(): void
    {
        $this->webView->registerCssFile($this->aliases->get('@baseUrl/css/somefile.css'));
        $html = $this->webView->renderFile($this->layoutPath, ['content' => 'content']);
        $this->assertStringContainsString("<link href=\"/baseUrl/css/somefile.css\" rel=\"stylesheet\">\n</head>", $html);
    }

    public function testPlaceholders(): void
    {
        $webView = $this->getContainer()->get(WebView::class);
        $signature = $webView->getPlaceholderSignature();
        $html = $webView->renderFile($this->layoutPath, ['content' => 'content']);
        $this->assertStringNotContainsString($signature, $html);

        $webView = $this->getContainer()->get(WebViewPlaceholderMock::class);
        $signature = $webView->getPlaceholderSignature();
        $html = $webView->renderFile($this->layoutPath, ['content' => 'content']);
        $this->assertStringContainsString($signature, $html);
    }
}
