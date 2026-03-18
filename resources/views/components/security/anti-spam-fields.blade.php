@props(['form'])

@php($antiSpam = app(\App\Services\Security\AntiSpam\AntiSpamService::class))

<div class="hidden" aria-hidden="true">
    <label for="{{ $form }}-{{ $antiSpam->honeypotField() }}">Leave this field blank</label>
    <input
        id="{{ $form }}-{{ $antiSpam->honeypotField() }}"
        name="{{ $antiSpam->honeypotField() }}"
        type="text"
        value=""
        autocomplete="off"
        tabindex="-1"
    >
</div>

@if ($antiSpam->shouldRenderWidget($form))
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce

    <div class="space-y-2">
        <div class="cf-turnstile" data-sitekey="{{ $antiSpam->turnstileSiteKey() }}"></div>
    </div>
@endif

<x-input-error :messages="$errors->get('anti_spam')" class="mt-2" />
