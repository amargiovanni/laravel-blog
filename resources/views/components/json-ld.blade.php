@props(['type' => 'website', 'post' => null])

@php
    use App\Services\JsonLdService;
    use App\Models\Setting;

    // Skip if JSON-LD is disabled
    if (!Setting::get('geo.jsonld_enabled', true)) {
        return;
    }

    $jsonLdService = app(JsonLdService::class);

    $data = match($type) {
        'post' => $post ? $jsonLdService->forPost($post) : [],
        'organization' => $jsonLdService->forOrganization(),
        'website' => $jsonLdService->forWebsite(),
        default => [],
    };
@endphp

@if(!empty($data))
{!! $jsonLdService->toScript($data) !!}
@endif
