@props(['id', 'title', 'icon' => null, 'iconColor' => 'var(--accent)', 'formId' => null])

<div id="{{ $id }}" class="modal-overlay" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 9999; flex-direction: column; justify-content: center; align-items: center;">
    <div class="modal-content" style="text-align: center; width: 90%; max-width: 450px;">
        @if($icon)
            <i class="fa-solid {{ $icon }}" style="font-size: 40px; color: {{ $iconColor }}; margin-bottom: 16px;"></i>
        @endif
        
        <h3 style="margin-bottom: 24px; font-weight: 800; font-size: 20px;">{{ $title }}</h3>
        
        @if($formId)
        <form id="{{ $formId }}" method="POST" action="">
            @csrf
        @endif
            
        <div style="text-align: left;">
            {{ $slot }}
        </div>
        
        @if($formId)
        </form>
        @endif
    </div>
</div>
