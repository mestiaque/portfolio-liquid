@extends('me::master')
@section('title', 'Edit Project: ' . $project->title)
@push('buttons')
<a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-encodex-list">
    <i class="fas fa-arrow-left"></i> Back to Projects
</a>
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.projects.update', $project) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('portfolio::projects._form')

                    <div class="form-group text-end">
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-encodex-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-encodex-save">
                            <i class="fas fa-save"></i> Update Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
