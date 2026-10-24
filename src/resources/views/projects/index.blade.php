@extends('me::master')
@section('title', 'Projects')

@push('buttons')
  <a href="{{ route(me_prefix() . '.projects.create') }}" class="btn btn-sm btn-encodex-create">@lang('Add Project')</a>
@endpush


@section('content')
<div class="row">
    <div class="col-12">
        <div class="card ">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-encodex">
                        <thead>
                            <tr class="text-center">
                                <th width="50">#</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Technologies</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                <tr>
                                    <td>{{ $project->id }}</td>
                                    <td>
                                        @if($project->image)
                                            <img src="{{ asset('storage/' . $project->image) }}" alt="{{ $project->title }}" width="60" height="40" class="img-thumbnail">
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                    <td>{{ $project->title }}</td>
                                    <td>
                                        @if($project->technologies)
                                            @foreach($project->technologies as $tech)
                                                <span class="badge bg-info-subtle text-info">{{ $tech }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $project->order }}</td>
                                    <td class="text-center">
                                        @if($project->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route(me_prefix() . '.projects.show', $project) }}" class="btn btn-encodex-show btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route(me_prefix() . '.projects.edit', $project) }}" class="btn btn-encodex-edit btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route(me_prefix() . '.projects.destroy', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-encodex-delete btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No projects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
