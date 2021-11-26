<div>
    @if(is_array($options))
        @if($options['action'] ?? null)
            @can($options['action']['permission'][0] ?? null, $options['action']['permission'][1] ?? null)

                @php
                    $params = [...$parents, $item->id];
                    if(is_callable($options['action']['params'] ?? null)){
                        $params = $options['action']['params']($item);
                    }
                @endphp
                <a href="{{$options['action']['route']($params)}}"
                   class="font-medium whitespace-nowrap {{$options['class_link'] ?? ''}}">
                    @if($options['component'] ?? null)
                        <x-datalistcrm::dynamics :component="$options['component']['name']"
                                    :datas="$datas ?? []"/>
                    @else

                        {!! $value ?? '' !!}
                    @endif
                </a>
            @endcan

            @cannot($options['action']['permission'][0] ?? null, $options['action']['permission'][1] ?? null)
                @if($options['component'] ?? null)

                    <x-datalistcrm::dynamics :component="$options['component']['name']"
                                             :datas="$datas ?? []"/>
                @else
                    {!! $value ?? '' !!}
                @endif
            @endcannot
        @else
            @if($options['component'] ?? null)
                <x-datalistcrm::dynamics :component="$options['component']['name']"
                            :datas="$datas ?? []"/>
            @else
                {!! $value ?? '' !!}
            @endif
        @endif
    @else
        {!! $value ?? '' !!}
    @endif
</div>
