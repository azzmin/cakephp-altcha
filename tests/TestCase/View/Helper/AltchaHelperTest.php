<?php
declare(strict_types=1);

namespace Altcha\Test\TestCase\View\Helper;

use Altcha\View\Helper\AltchaHelper;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;

class AltchaHelperTest extends TestCase
{
    private AltchaHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();

        Configure::write('App.encoding', 'UTF-8');

        $request = new ServerRequest();
        $response = new Response();
        $view = new View($request, $response);
        $this->helper = new AltchaHelper($view);
    }

    public function testWidgetOutputsScriptTag(): void
    {
        $result = $this->helper->widget();

        $this->assertStringContainsString('<script', $result);
        $this->assertStringContainsString('type="module"', $result);
        $this->assertStringContainsString('altcha.js', $result);
    }

    public function testWidgetOutputsAltchaElement(): void
    {
        $result = $this->helper->widget();

        $this->assertStringContainsString('<altcha-widget', $result);
        $this->assertStringContainsString('</altcha-widget>', $result);
    }

    public function testWidgetIncludesChallengeJson(): void
    {
        $result = $this->helper->widget();

        $this->assertStringContainsString('challengejson="', $result);
        $this->assertStringContainsString('SHA-256', $result);
    }

    public function testWidgetIncludesSignature(): void
    {
        $result = $this->helper->widget();

        $this->assertStringContainsString('signature', $result);
    }

    public function testWidgetOnlyLoadsScriptOnce(): void
    {
        $result1 = $this->helper->widget();
        $result2 = $this->helper->widget();

        $this->assertStringContainsString('<script', $result1);
        $this->assertStringNotContainsString('<script', $result2);
    }

    public function testWidgetPassesBooleanOptions(): void
    {
        $result = $this->helper->widget(['hidelogo' => true]);

        $this->assertStringContainsString('hidelogo', $result);
        $this->assertStringNotContainsString('hidelogo="', $result);
    }

    public function testWidgetPassesStringOptions(): void
    {
        $result = $this->helper->widget(['name' => 'custom_field']);

        $this->assertStringContainsString('name="custom_field"', $result);
    }

    public function testWidgetExcludesFalseOptions(): void
    {
        $result = $this->helper->widget(['hidelogo' => false]);

        $this->assertStringNotContainsString('hidelogo', $result);
    }

    public function testScriptUsesCdnByDefault(): void
    {
        $result = $this->helper->script();

        $this->assertStringContainsString('cdn.jsdelivr.net', $result);
    }

    public function testScriptUsesConfiguredUrl(): void
    {
        $result = $this->helper->script(['jsUrl' => '/local/altcha.js']);

        $this->assertStringContainsString('/local/altcha.js', $result);
    }
}
