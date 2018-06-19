<?php

namespace Recca0120\LaravelTracy\Tests\Panels;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelTracy\Panels\ViewPanel;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class ViewPanelTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRender()
    {
        $panel = new ViewPanel(
            $template = m::mock('Recca0120\LaravelTracy\Template')
        );
        $laravel = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $laravel->shouldReceive('version')->once()->andReturn(5.4);
        $laravel->shouldReceive('offsetGet')->once()->with('events')->andReturn(
            $events = m::mock('Illuminate\Contracts\Event\Dispatcher')
        );
        $collection = m::mock('Illuminate\Support\Collection');
        $events->shouldReceive('listen')->once()->with('composing:*', m::on(function ($closure) use ($collection) {
            $event = m::mock('stdClass');
            $event->shouldReceive('getName')->once()->andReturn($name = 'foo');

            $collection->shouldReceive('count')->once()->andReturn(100);
            $collection->shouldReceive('take')->once()->andReturn(50)->andReturnSelf();

            $event->shouldReceive('getData')->once()->andReturn($data = [
                range(1, 100),
                $collection,
            ]);
            $event->shouldReceive('getPath')->once()->andReturn($path = '');
            $closure('foo', [$event]);

            return true;
        }));
        $panel->setLaravel($laravel);

        $template->shouldReceive('setAttributes')->once()->with([
            'rows' => [[
                'name' => 'foo',
                'data' => [
                    range(1, 50),
                    $collection,
                ],
                'path' => '',
            ]],
        ]);
        $template->shouldReceive('render')->twice()->with(m::type('string'))->andReturn($content = 'foo');

        $this->assertSame($content, $panel->getTab());
        $this->assertSame($content, $panel->getPanel());
    }

    public function testRenderAndLaravel50()
    {
        $panel = new ViewPanel(
            $template = m::mock('Recca0120\LaravelTracy\Template')
        );
        $laravel = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $laravel->shouldReceive('version')->once()->andReturn(5.3);
        $laravel->shouldReceive('offsetGet')->once()->with('events')->andReturn(
            $events = m::mock('Illuminate\Contracts\Event\Dispatcher')
        );
        $collection = m::mock('Illuminate\Support\Collection');
        $events->shouldReceive('listen')->once()->with('composing:*', m::on(function ($closure) use ($collection) {
            $event = m::mock('stdClass');
            $event->shouldReceive('getName')->once()->andReturn($name = 'foo');

            $collection->shouldReceive('count')->once()->andReturn(100);
            $collection->shouldReceive('take')->once()->andReturn(50)->andReturnSelf();

            $event->shouldReceive('getData')->once()->andReturn($data = [
                '__env' => [],
                'app' => [],
                range(1, 100),
                $collection,
            ]);
            $event->shouldReceive('getPath')->once()->andReturn($path = '');
            $closure($event);

            return true;
        }));
        $panel->setLaravel($laravel);

        $template->shouldReceive('setAttributes')->once()->with([
            'rows' => [[
                'name' => 'foo',
                'data' => [
                    range(1, 50),
                    $collection,
                ],
                'path' => '',
            ]],
        ]);
        $template->shouldReceive('render')->twice()->with(m::type('string'))->andReturn($content = 'foo');

        $this->assertSame($content, $panel->getTab());
        $this->assertSame($content, $panel->getPanel());
    }
}
