@extends('user_type.auth', ['parentFolder' => 'appSettings', 'childFolder' => 'none'])

@section('content')
    @if (isset($message))
        <script>
            alert('{{ $message }}');
        </script>
    @endif

    <div class="py-4 container-fluid">
        <div class="card">
            <div class="px-3 pb-0 card-header">
                <h6 class="mb-0">
                    <Information></Information>
                </h6>
            </div>
            <div class="p-3 pt-4 card-body">
                <form action="{{ route('app-settings') }}" method="POST" role="form text-left">
                    @csrf
                    <div class="row">

                        @foreach ($generalSettings['devices_name']['value'] as $key => $value)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user-name" class="form-control-label">Name for {{ $key }}</label>
                                    <div class="">
                                        <input class="form-control" value="{{ $value }}" type="text"
                                            placeholder="Name" id="devices_name_{{ $key }}"
                                            name="devices_name[{{ $key }}]" onfocus="focused(this)"
                                            onfocusout="defocused(this)">
                                    </div>
                                </div>
                            </div>
                        @endforeach


                    </div>
                    <div class="row">
                        @foreach ($translation['value'] as $key => $value)
                            @php
                                $name = 'translation[' . $key . ']';
                            @endphp
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="{{ $name }}" class="form-control-label">{{ $key }}</label>
                                    <div class="">
                                        <input class="form-control" type="tel" id="{{ $name }}"
                                            name="{{ $name }}" value="{{ $value }}" onfocus="focused(this)"
                                            onfocusout="defocused(this)">
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                    <div hidden class="form-group">
                        <label for="about">About Me</label>
                        <div class="">
                            <textarea class="form-control" id="about" rows="3" placeholder="Say something about yourself" name="about_me"></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="mt-4 mb-4 btn bg-gradient-dark btn-md">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
