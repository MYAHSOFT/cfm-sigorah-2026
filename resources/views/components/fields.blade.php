@switch($attribues->type)
    @case('text')
    @case('money')
        <input type="text" name="{{$name}}"
            value="{{ !empty($attribues->value) ? $attribues->value : old($name) }}"
            class="form-control"
            @if(!empty($attribues->readonly) && !empty($attribues->value)) readonly  @endif/>
        @break
    @case('textarea')
        <textarea name="{{$name}}" class="form-control" @if(!empty($attribues->readonly)) readonly="readonly" @endif>{{ !empty($attribues->value) ? $attribues->value : old($name) }}</textarea>
        @break
    @case('date')
        @php
            $date_default = !empty($attribues->default) ? $attribues->default : null;
            $value = !empty(old($name)) ? old($name) : $date_default;
        @endphp
        <input type="date" name="{{$name}}"
            value="{{ !empty($attribues->value) ? $attribues->value :$value }}"
            class="form-control"
            @if(!empty($attribues->readonly)) readonly="readonly" @endif/>
        @break

    @case('select')

        <select name="{{$name}}" class="form-control" @if(!empty($attribues->readonly)) readonly="readonly" @endif>
            @php $option_value = !empty($attribues->value) ? $attribues->value : null @endphp
            @foreach ($attribues->options as $optionkey=>$option)
                <option value="{{$optionkey}}" {{$option_value == $optionkey ? 'selected' : ''}}>{{ $option }}</option>
            @endforeach
        </select>

        @break

    {{-- @case('select2')

        <select name="{{$name}}" class="form-control select2" @if(!empty($attribues->readonly)) readonly="readonly" @endif>
            @foreach ($attribues->options as $key=>$option)
                @php $option_value = !empty($attribues->value) ? $attribues->value : null @endphp
                <option value="{{$key}}" @if($key == $option_value) selected @endif>{{ $option }}</option>
            @endforeach
        </select>

        @break
    @default --}}

@endswitch

