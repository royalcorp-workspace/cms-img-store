<ul class="ml-6 space-y-2 border-l-2 border-outline-variant/30 pl-4 mt-2">
    @php
        $dis = $disabled ? 'disabled' : '';
    @endphp

    @foreach ($childs as $child)
        <li class="relative">
            <div class="flex items-center gap-2.5 py-1">
                @if(!empty($child->route_name) && str_contains($child->route_name, '|'))
                    @php
                        $pipeRoutes = explode('|', $child->route_name);
                        $isChecked = false;
                        foreach($pipeRoutes as $r) {
                            if (in_array(trim($r), $rolePermissions)) {
                                $isChecked = true;
                                break;
                            }
                        }
                    @endphp
                    <input {{ $dis }} 
                           type="checkbox" 
                           class="role-checkbox w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/20 cursor-pointer" 
                           {{ $isChecked ? 'checked' : '' }} 
                           name="permission[{{ $child->id }}]" 
                           id="menu-{{ $child->id }}" 
                           value="{{ $child->route_name }}">
                @else
                    @php
                        $routeVal = $child->route_name ?: $child->permission ?: ('menu.' . $child->id);
                        $isChecked = in_array($routeVal, $rolePermissions);
                    @endphp
                    <input {{ $dis }} 
                           type="checkbox" 
                           class="role-checkbox w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/20 cursor-pointer" 
                           {{ $isChecked ? 'checked' : '' }} 
                           name="permission[{{ $child->id }}]" 
                           id="menu-{{ $child->id }}" 
                           value="{{ $routeVal }}">
                @endif

                <label for="menu-{{ $child->id }}" class="cursor-pointer flex items-center gap-1.5 select-none text-body-md text-on-surface hover:text-primary transition-colors">
                    @if($child->icon)
                        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">{{ $child->icon }}</span>
                    @endif
                    <span class="font-medium">{{ $child->title }}</span>
                    @if($child->route_name)
                        <span class="text-[11px] px-1.5 py-0.5 rounded bg-surface-container text-on-surface-variant font-mono">{{ $child->route_name }}</span>
                    @endif
                </label>
            </div>

            @if(count($child->childs))
                @include('pages.roles.childs', [
                    'childs' => $child->childs, 
                    'disabled' => $disabled, 
                    'rolePermissions' => $rolePermissions
                ])
            @endif
        </li>
    @endforeach
</ul>
