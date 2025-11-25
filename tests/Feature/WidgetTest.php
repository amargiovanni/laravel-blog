<?php

declare(strict_types=1);

use App\Models\WidgetInstance;
use App\Services\WidgetRegistry;
use App\Widgets\CustomHtmlWidget;
use App\Widgets\SearchWidget;

describe('WidgetInstance Model', function (): void {
    test('widget instance can be created', function (): void {
        $widget = WidgetInstance::factory()->search()->create();

        expect($widget->area)->toBe('primary_sidebar');
        expect($widget->widget_type)->toBe('search');
    });

    test('widget instance can store settings', function (): void {
        $widget = WidgetInstance::factory()->create([
            'settings' => ['count' => 5, 'show_date' => true],
        ]);

        expect($widget->settings)->toBe(['count' => 5, 'show_date' => true]);
    });

    test('widget instance getSetting returns correct value', function (): void {
        $widget = WidgetInstance::factory()->create([
            'settings' => ['count' => 10],
        ]);

        expect($widget->getSetting('count'))->toBe(10);
        expect($widget->getSetting('nonexistent', 'default'))->toBe('default');
    });

    test('widget instance setSetting updates settings', function (): void {
        $widget = WidgetInstance::factory()->create(['settings' => []]);

        $widget->setSetting('count', 15);

        expect($widget->settings['count'])->toBe(15);
    });

    test('widget instance getDisplayTitle returns title or default', function (): void {
        $withTitle = WidgetInstance::factory()->search()->create(['title' => 'Custom Title']);
        $withoutTitle = WidgetInstance::factory()->search()->create(['title' => null]);

        expect($withTitle->getDisplayTitle())->toBe('Custom Title');
        expect($withoutTitle->getDisplayTitle())->toBe('Search');
    });

    test('widget instance scopeForArea filters correctly', function (): void {
        WidgetInstance::factory()->forArea('primary_sidebar')->count(3)->create();
        WidgetInstance::factory()->forArea('footer_1')->count(2)->create();

        expect(WidgetInstance::forArea('primary_sidebar')->count())->toBe(3);
        expect(WidgetInstance::forArea('footer_1')->count())->toBe(2);
    });

    test('widget instances are ordered by sort_order', function (): void {
        $third = WidgetInstance::factory()->forArea('primary_sidebar')->create(['sort_order' => 2]);
        $first = WidgetInstance::factory()->forArea('primary_sidebar')->create(['sort_order' => 0]);
        $second = WidgetInstance::factory()->forArea('primary_sidebar')->create(['sort_order' => 1]);

        $widgets = WidgetInstance::forArea('primary_sidebar')->get();

        expect($widgets[0]->id)->toBe($first->id);
        expect($widgets[1]->id)->toBe($second->id);
        expect($widgets[2]->id)->toBe($third->id);
    });
});

describe('WidgetRegistry', function (): void {
    test('registry returns available widgets', function (): void {
        $registry = app(WidgetRegistry::class);
        $widgets = $registry->getAvailableWidgets();

        expect($widgets)->toHaveKey('search');
        expect($widgets)->toHaveKey('recent_posts');
        expect($widgets)->toHaveKey('categories');
        expect($widgets)->toHaveKey('tags');
        expect($widgets)->toHaveKey('archives');
        expect($widgets)->toHaveKey('custom_html');
    });

    test('registry returns widget areas', function (): void {
        $registry = app(WidgetRegistry::class);
        $areas = $registry->getWidgetAreas();

        expect($areas)->toHaveKey('primary_sidebar');
        expect($areas)->toHaveKey('footer_1');
        expect($areas)->toHaveKey('footer_2');
        expect($areas)->toHaveKey('footer_3');
    });

    test('registry creates widget instance', function (): void {
        $registry = app(WidgetRegistry::class);
        $instance = WidgetInstance::factory()->search()->create();

        $widget = $registry->createWidget($instance);

        expect($widget)->toBeInstanceOf(SearchWidget::class);
    });

    test('registry returns null for unknown widget type', function (): void {
        $registry = app(WidgetRegistry::class);
        $instance = WidgetInstance::factory()->create(['widget_type' => 'nonexistent']);

        $widget = $registry->createWidget($instance);

        expect($widget)->toBeNull();
    });

    test('registry returns settings fields for widget type', function (): void {
        $registry = app(WidgetRegistry::class);
        $fields = $registry->getSettingsFields('recent_posts');

        expect($fields)->toBeArray();
        expect($fields)->not->toBeEmpty();
    });
});

describe('Widget Factories', function (): void {
    test('factory creates search widget', function (): void {
        $widget = WidgetInstance::factory()->search()->create();

        expect($widget->widget_type)->toBe('search');
    });

    test('factory creates recent posts widget with settings', function (): void {
        $widget = WidgetInstance::factory()->recentPosts()->create();

        expect($widget->widget_type)->toBe('recent_posts');
        expect($widget->settings['count'])->toBe(5);
        expect($widget->settings['show_date'])->toBeTrue();
    });

    test('factory creates categories widget', function (): void {
        $widget = WidgetInstance::factory()->categories()->create();

        expect($widget->widget_type)->toBe('categories');
        expect($widget->settings['show_count'])->toBeTrue();
    });

    test('factory creates tags widget', function (): void {
        $widget = WidgetInstance::factory()->tags()->create();

        expect($widget->widget_type)->toBe('tags');
        expect($widget->settings['max_tags'])->toBe(30);
    });

    test('factory creates archives widget', function (): void {
        $widget = WidgetInstance::factory()->archives()->create();

        expect($widget->widget_type)->toBe('archives');
        expect($widget->settings['type'])->toBe('monthly');
    });

    test('factory creates custom html widget', function (): void {
        $widget = WidgetInstance::factory()->customHtml('<p>Test</p>')->create();

        expect($widget->widget_type)->toBe('custom_html');
        expect($widget->settings['content'])->toBe('<p>Test</p>');
    });
});

describe('CustomHtmlWidget Sanitization', function (): void {
    test('custom html widget sanitizes script tags', function (): void {
        $instance = WidgetInstance::factory()->customHtml('<script>alert("xss")</script><p>Safe</p>')->create();
        $widget = new CustomHtmlWidget($instance);

        $view = $widget->render();
        $html = $view->render();

        expect($html)->not->toContain('<script>');
        expect($html)->toContain('Safe');
    });

    test('custom html widget sanitizes event handlers', function (): void {
        $instance = WidgetInstance::factory()->customHtml('<div onclick="alert(1)">Test</div>')->create();
        $widget = new CustomHtmlWidget($instance);

        $view = $widget->render();
        $html = $view->render();

        expect($html)->not->toContain('onclick');
    });

    test('custom html widget sanitizes javascript protocol', function (): void {
        $instance = WidgetInstance::factory()->customHtml('<a href="javascript:alert(1)">Link</a>')->create();
        $widget = new CustomHtmlWidget($instance);

        $view = $widget->render();
        $html = $view->render();

        expect($html)->not->toContain('javascript:');
    });

    test('custom html widget allows safe html', function (): void {
        $instance = WidgetInstance::factory()->customHtml('<p class="text-lg">Hello <strong>World</strong></p>')->create();
        $widget = new CustomHtmlWidget($instance);

        $view = $widget->render();
        $html = $view->render();

        expect($html)->toContain('<p class="text-lg">');
        expect($html)->toContain('<strong>World</strong>');
    });
});

describe('Widget Config', function (): void {
    test('widget areas are configured', function (): void {
        $areas = config('widgets.areas');

        expect($areas)->toBeArray();
        expect($areas)->toHaveKey('primary_sidebar');
    });

    test('widget types are configured', function (): void {
        $types = config('widgets.types');

        expect($types)->toBeArray();
        expect($types)->toHaveKey('search');
        expect($types['search'])->toHaveKey('class');
    });

    test('widget cache settings are configured', function (): void {
        $cache = config('widgets.cache');

        expect($cache)->toHaveKey('enabled');
        expect($cache)->toHaveKey('ttl');
    });
});
