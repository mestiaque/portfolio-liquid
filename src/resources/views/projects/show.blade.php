@extends('me::master')
@section('title', 'Project Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Project Details: {{ $project->title }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Projects
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">ID</th>
                                    <td>{{ $project->id }}</td>
                                </tr>
                                <tr>
                                    <th>Title</th>
                                    <td>{{ $project->title }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{!! nl2br(e($project->description)) !!}</td>
                                </tr>
                                <tr>
                                    <th>Project URL</th>
                                    <td>
                                        @if($project->url)
                                            <a href="{{ $project->url }}" target="_blank">{{ $project->url }}</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>GitHub URL</th>
                                    <td>
                                        @if($project->github_url)
                                            <a href="{{ $project->github_url }}" target="_blank">{{ $project->github_url }}</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Technologies</th>
                                    <td>
                                        @if($project->technologies)
                                            @foreach($project->technologies as $tech)
                                                <span class="badge bg-info-subtle text-info">{{ $tech }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Order</th>
                                    <td>{{ $project->order }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($project->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $project->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $project->updated_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-4">
                            @if($project->image)
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Project Image</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <img src="{{ asset('storage/' . $project->image) }}" alt="{{ $project->title }}" class="img-fluid img-thumbnail">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
