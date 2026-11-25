{{-- Component: cell-user (Avatar 40px + Tên 16px font-semibold + vai trò/email 13px) --}}
@props([
    'name',
    'email' => null,
    'role' => null,
    'avatar' => null,
])
<td {{ $attributes->merge(['class' => 'py-5 px-5 whitespace-nowrap']) }}>
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-surface-container text-on-surface border border-surface-container-high/60 flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
            @if($avatar)
                <img src="{{ $avatar }}" alt="{{ $name }}" class="w-full h-full rounded-xl object-cover">
            @else
                <span>{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
            @endif
        </div>
        <div class="min-w-0">
            <span class="font-semibold text-base text-on-surface block leading-snug truncate" title="{{ $name }}">
                {{ $name }}
            </span>
            @if($email || $role)
                <span class="text-[13px] text-on-surface-variant block mt-0.5 truncate" title="{{ $email ?? $role }}">
                    {{ $email ?? $role }}
                </span>
            @endif
        </div>
    </div>
</td>
