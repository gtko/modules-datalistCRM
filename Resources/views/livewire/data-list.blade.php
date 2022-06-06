<div>
    <h2 class="intro-y text-lg font-medium mt-10">{{$title}}</h2>
    <x-basecore::layout.panel-full>
        <div class="flex flex-wrap justify-between sm:flex-nowrap items-center mt-2">
            @if(!empty($create))
                @can($create['permission'][0] ?? '', $create['permission'][1] ?? '')
                    @php
                        $params = $parents;
                        if(is_callable($options['action']['params'] ?? null)){
                            $params = $options['action']['params']($parents);
                        }
                    @endphp
                    <a href="{{$create['route']($params)}}"
                       class="btn btn-primary shadow-md mr-2 {{$create['class_link'] ?? ''}}">
                        @if($create['icon'] ?? false)
                            @icon($create['icon'],null,"w-4 h-4 mr-1")
                        @endif
                        {{$create['label'] ?? ''}}
                    </a>
                @endcan
            @endif

            @if($searchable)
                <div class="search hidden sm:block">
                    <input type="text" wire:model.debounce.500ms="search"
                           class="w-full search__input form-control border-transparent placeholder-theme-13"
                           placeholder="Recherche ...">
                    <span class="flex items-center">
                    <span wire:loading.remove>
                        @icon('search',null,'search__icon dark:text-gray-300')
                    </span>
                    <span wire:loading>
                         @icon('spinner',null,'search__icon animate-spin')
                    </span>
                </span>
                </div>
            @endif

        </div>

        <!-- BEGIN: Data List -->
        <div class="overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                <tr>
                    @foreach($fields as $field => $options)
                        <th class="whitespace-nowrap">
                            <div
                                @if($options['sortable'] ?? $sortable )  class="flex cursor-pointer items-center justify-start"
                                wire:click="sort('{{$field}}')" @endif>
                                @if(is_array($options))
                                    @if($options['label'])
                                        {{ $options['label'] }}
                                    @endif
                                @else
                                    {{ \Illuminate\Support\Str::ucfirst($field) }}
                                @endif
                                @if(($sort[$field] ?? '') === 'asc')
                                    <span class="ml-2">@icon('asc', 16, 'mr-2')</span>
                                @elseif(($sort[$field] ?? '') === 'desc')
                                    <span class="ml-2">@icon('desc', 16, 'mr-2')</span>
                                @elseif($options['sortable'] ?? $sortable )
                                    <span class="ml-2">--</span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>

                </thead>
                <tbody>


                @forelse($datas as $item)
                    <tr @if($datalist->link($item)) link="{{$datalist->link($item)}}" @endif
                    @if($datalist->link($item)) link-blank="{{$datalist->link($item)}}" @endif
                    >
                        @foreach($fields as $field => $options)
                            <td class="text-left {{$options['class'] ?? ''}}">
                                <x-datalistcrm::data-list-value :item="$item" :field="$field" :options="$options"
                                                                :parents="$parents"/>
                            </td>
                        @endforeach

                        <td class="table-report__action w-56">
                            <div class="flex justify-center items-center">
                                @foreach($actions as $action)
                                    @if($action['confirm'] ?? false)
                                        <x-basecore::ActionConfirm>
                                            @endif



                                            @php
                                                $params = [...$parents, $item->id];
                                                if(is_callable($action['params'] ?? null)){
                                                    $params = $action['params']($item);
                                                }
                                            @endphp

                                            @if($action['permission'] ?? false)
                                                @can($action['permission'][0], $action['permission'][1])
                                                    @if($action['method'] ?? false)
                                                        <form method="POST" action="{{$action['route']($params)}}">
                                                            @csrf
                                                            <input type="hidden" name="_method"
                                                                   value="{{$action['method']}}">
                                                            <button
                                                                class="border-0 bg-transparent flex items-center mr-3 p-0 m-0">
                                                                @if($action['icon'] ?? false)
                                                                    @icon($action['icon'], null, 'w-4 h-4 mr-1')
                                                                @endif
                                                                {{$action['label'] ?? ''}}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a class="flex items-center mr-3 {{$action['class_link'] ?? ''}}"
                                                           href="{{$action['route']($params)}}">
                                                            @if($action['icon'] ?? false)
                                                                @icon($action['icon'], null, 'w-4 h-4 mr-1')
                                                            @endif
                                                            {{$action['label'] ?? ''}}
                                                        </a>
                                                    @endif
                                                @endcan
                                            @else
                                                @if($action['method'] ?? true)
                                                    <form method="POST" action="{{$action['route']($params)}}">
                                                        @csrf
                                                        <input type="hidden" name="_method"
                                                               value="{{$action['method']}}">
                                                        <button
                                                            class="border-0 bg-transparent flex items-center mr-3 p-0 m-0">
                                                            @if($action['icon'] ?? false)
                                                                @icon($action['icon'], null, 'w-4 h-4 mr-1')
                                                            @endif
                                                            {{$action['label'] ?? ''}}
                                                        </button>
                                                    </form>
                                                @else
                                                    <a class="flex items-center mr-3 {{$action['class_link'] ?? ''}}"
                                                       href="{{$action['route']($params)}}">
                                                        @if($action['icon'] ?? false)
                                                            @icon($action['icon'], null, 'w-4 h-4 mr-1')
                                                        @endif
                                                        {{$action['label'] ?? ''}}
                                                    </a>
                                                @endif
                                            @endif
                                            @if($action['confirm'] ?? false)
                                        </x-basecore::ActionConfirm>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="intro-x">
                        <td colspan="{{count($fields) + 1}}">
                            <button type="button"
                                    class="relative block w-full  p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path>
                                </svg>
                                <span class="mt-2 block text-sm font-medium text-gray-900">
                                        @if(!empty($this->search))
                                        Aucun résultat pour la recherche "{{$this->search}}"
                                    @else
                                        Aucun "{{$title}}"
                                    @endif
                                </span>
                            </button>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <!-- END: Data List -->

        <!-- BEGIN: Pagination -->
        <div class="flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
            <div class="pagination">
                {{ $datas->links() }}
            </div>
        </div>
        <!-- END: Pagination -->
    </x-basecore::layout.panel-full>
</div>
