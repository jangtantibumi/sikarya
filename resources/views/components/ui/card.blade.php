@props(['title', 'value', 'icon' => null, 'trend' => null, 'trendDirection' => 'up', 'interactive' => false, 'class' => ''])

<div {{ $attributes->merge(['class' => 'card ' . ($interactive ? 'interactive ' : '') . $class]) }}>
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <h3>{{ $title }}</h3>
        @if($icon)
            <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(var(--accent-rgb), 0.1); display: flex; align-items: center; justify-content: center; color: var(--accent);">
                <i class="fa-solid {{ $icon }} fa-lg"></i>
            </div>
        @endif
    </div>
    <div class="value">{{ $value }}</div>
    @if($trend)
        <div class="trend" style="color: {{ $trendDirection === 'up' ? 'var(--success)' : 'var(--danger)' }}">
            <i class="fa-solid {{ $trendDirection === 'up' ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
            {{ $trend }}
        </div>
    @endif
</div>
