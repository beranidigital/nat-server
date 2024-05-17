@extends('user_type.auth', ['parentFolder' => 'appSettings', 'childFolder' => 'none'])

@section('content')
    @if(isset($message))
        <script>
            alert('{{$message}}');
        </script>

    @endif

    <div class="card-body pt-4 m-4 p-3" style="height: 100vh">
        @php
            // epic hack below
        @endphp
        <iframe src="{{route('settings.parameter.livewire')}}" width="100%" height="100%"></iframe>
    </div>

@endsection
