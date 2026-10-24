@extends('me::master')
@section('title', 'Create New Project')
@push('buttons')
<a href="{{ route(me_prefix() . '.projects.index') }}" class="btn btn-sm btn-encodex-list">
    <i class="fas fa-arrow-left"></i> Back to Projects
</a>
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route(me_prefix() . '.projects.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('portfolio::projects._form')

                    <div class="form-group text-end">
                        <a href="{{ route(me_prefix() . '.projects.index') }}" class="btn btn-encodex-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-encodex-save">
                            <i class="fas fa-save"></i> Create Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
