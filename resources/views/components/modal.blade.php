{{-- ═══════════════════════════════════════════
     REUSABLE MODAL COMPONENT
     Usage: @component('components.modal', ['id' => 'my-modal', 'title' => 'Judul', 'size' => 'md'])
               form content here
            @endcomponent
     Sizes: sm (448px), md (560px), lg (700px), xl (860px)
═══════════════════════════════════════════ --}}

@props(['id' => 'app-modal', 'title' => 'Modal', 'size' => 'md', 'backUrl' => ''])

@php
$maxW = match($size) {
'sm' => '448px',
'lg' => '700px',
'xl' => '860px',
default => '560px', // md
};
@endphp

{{-- Backdrop --}}
<div id="{{ $id }}-backdrop"
    style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;padding:24px;animation:fadeInBackdrop 0.2s ease;"
    onclick="if(event.target===this) closeModal('{{ $id }}', '{{ $backUrl }}')">

    <div id="{{ $id }}-panel"
        style="width:100%;max-width:{{ $maxW }};max-height:90vh;overflow-y:auto;background:rgba(18,16,42,0.97);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid rgba(255,255,255,0.1);border-radius:20px;box-shadow:0 32px 80px rgba(0,0,0,0.7),0 0 0 1px rgba(99,102,241,0.12);animation:slideUpModal 0.3s cubic-bezier(0.16,1,0.3,1);"
        onclick="event.stopPropagation()">

        {{-- Modal Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px 18px;border-bottom:1px solid rgba(255,255,255,0.07);">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,rgba(99,102,241,0.2),rgba(139,92,246,0.2));border:1px solid rgba(99,102,241,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:16px;height:16px;color:#a5b4fc;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span style="font-size:0.9rem;font-weight:600;color:#e2e8f0;">{{ $title }}</span>
            </div>
            <button onclick="closeModal('{{ $id }}', '{{ $backUrl }}')"
                style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:none;cursor:pointer;background:rgba(255,255,255,0.06);color:rgba(148,163,184,0.7);transition:all 0.15s;"
                onmouseover="this.style.background='rgba(239,68,68,0.15)';this.style.color='#fca5a5'"
                onmouseout="this.style.background='rgba(255,255,255,0.06)';this.style.color='rgba(148,163,184,0.7)'">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <div style="padding:24px;">
            {{ $slot }}
        </div>
    </div>
</div>

<style>
    @keyframes fadeInBackdrop {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUpModal {
        from {
            opacity: 0;
            transform: translateY(16px) scale(0.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 500;
        color: rgba(203, 213, 225, 0.85);
        margin-bottom: 6px;
    }

    .modal-req {
        color: #f87171;
        margin-left: 2px;
    }

    .modal-input {
        width: 100%;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 0.875rem;
        color: #e2e8f0;
        font-family: inherit;
        outline: none;
        transition: all 0.15s;
        box-sizing: border-box;
    }

    .modal-input::placeholder {
        color: rgba(148, 163, 184, 0.4);
    }

    .modal-input:focus {
        border-color: rgba(99, 102, 241, 0.6);
        background: rgba(255, 255, 255, 0.07);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .modal-select {
        width: 100%;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 0.875rem;
        color: #e2e8f0;
        font-family: inherit;
        outline: none;
        transition: all 0.15s;
        box-sizing: border-box;
        appearance: auto;
    }

    .modal-select:focus {
        border-color: rgba(99, 102, 241, 0.6);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .modal-select option {
        background: #1a1535;
        color: #e2e8f0;
    }

    .modal-textarea {
        width: 100%;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 0.875rem;
        color: #e2e8f0;
        font-family: inherit;
        outline: none;
        transition: all 0.15s;
        resize: vertical;
        box-sizing: border-box;
    }

    .modal-textarea::placeholder {
        color: rgba(148, 163, 184, 0.4);
    }

    .modal-textarea:focus {
        border-color: rgba(99, 102, 241, 0.6);
        background: rgba(255, 255, 255, 0.07);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
        margin-top: 20px;
    }

    .modal-btn-submit {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        padding: 9px 22px;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.15s;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
    }

    .modal-btn-submit:hover {
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        transform: translateY(-1px);
    }

    .modal-btn-cancel {
        background: rgba(255, 255, 255, 0.06);
        color: rgba(203, 213, 225, 0.8);
        padding: 9px 18px;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1px solid rgba(255, 255, 255, 0.09);
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .modal-btn-cancel:hover {
        background: rgba(255, 255, 255, 0.10);
    }

    .modal-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .modal-field {
        display: flex;
        flex-direction: column;
    }

    .modal-hint {
        font-size: 0.72rem;
        color: rgba(148, 163, 184, 0.5);
        margin-top: 4px;
    }

    .modal-divider {
        border: none;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
        margin: 16px 0;
    }

    /* ── Toggle Switch (modal-check-row) ── */
    .modal-check-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Sembunyikan checkbox asli */
    .modal-check-row input[type=checkbox] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }

    /* Label jadi toggle track */
    .modal-check-row label {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 0.875rem;
        color: rgba(203, 213, 225, 0.8);
        cursor: pointer;
        margin: 0;
        user-select: none;
    }

    /* Track (background pill) */
    .modal-check-row label::before {
        content: '';
        display: inline-block;
        width: 40px;
        height: 22px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.12);
        transition: background 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        flex-shrink: 0;
    }

    /* Knob (white dot) */
    .modal-check-row label::after {
        content: '';
        position: absolute;
        left: 3px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: rgba(148, 163, 184, 0.6);
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), background 0.25s ease;
    }

    /* ✅ Checked = ON (Hijau) */
    .modal-check-row input[type=checkbox]:checked+label::before {
        background: rgba(16, 185, 129, 0.85);
        border-color: rgba(16, 185, 129, 0.5);
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
    }

    .modal-check-row input[type=checkbox]:checked+label::after {
        transform: translateX(18px) translateY(-50%);
        background: white;
    }

    /* Focus ring */
    .modal-check-row input[type=checkbox]:focus-visible+label::before {
        outline: 2px solid rgba(99, 102, 241, 0.6);
        outline-offset: 2px;
    }

    /* Hover effect */
    .modal-check-row label:hover::before {
        border-color: rgba(255, 255, 255, 0.22);
    }

    .modal-check-row input[type=checkbox]:checked+label:hover::before {
        background: rgba(16, 185, 129, 1);
        border-color: rgba(16, 185, 129, 0.7);
    }
</style>

<script>
    function closeModal(id, backUrl) {
        const backdrop = document.getElementById(id + '-backdrop');
        if (backdrop) {
            backdrop.style.animation = 'fadeInBackdrop 0.15s ease reverse forwards';
            setTimeout(() => {
                if (backUrl) {
                    window.location.href = backUrl;
                } else {
                    window.history.back();
                }
            }, 150);
        }
    }
    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const backdrops = document.querySelectorAll('[id$="-backdrop"]');
            if (backdrops.length > 0) {
                const last = backdrops[backdrops.length - 1];
                const id = last.id.replace('-backdrop', '');
                const backUrl = last.getAttribute('data-back-url') || '';
                closeModal(id, backUrl);
            }
        }
    });
</script>