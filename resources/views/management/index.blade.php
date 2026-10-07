@extends('layouts.app')

@section('title', $definition['title'])

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">{{ $definition['title'] }}</h1>
                <p class="text-secondary mb-0">Create, update, and manage {{ strtolower($definition['title']) }} records.</p>
            </div>
            <span class="badge rounded-pill text-bg-primary px-3 py-2">{{ $records->total() }} records</span>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->has('record'))
            <div class="alert alert-danger" role="alert">{{ $errors->first('record') }}</div>
        @endif

        <section class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 px-4 pt-4">
                <h2 class="h5 fw-bold mb-0">{{ $editing ? 'Edit record' : 'Add '.rtrim($definition['title'], 's') }}</h2>
            </div>
            <div class="card-body px-4 pb-4">
                <form method="POST" action="{{ $editing ? route($module.'.update', $editing->id) : route($module.'.store') }}">
                    @csrf
                    @if ($editing)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        @foreach ($definition['fields'] as $field)
                            @php
                                $value = old($field['name'], $formValues[$field['name']] ?? '');
                                $fieldId = $module.'_'.$field['name'];
                            @endphp
                            <div class="col-12 col-md-6">
                                <label for="{{ $fieldId }}" class="form-label fw-semibold">
                                    {{ $field['label'] }}@if ($field['required'] ?? false) <span class="text-danger">*</span>@endif
                                </label>

                                @if ($field['type'] === 'select')
                                    <select id="{{ $fieldId }}" name="{{ $field['name'] }}" class="form-select @error($field['name']) is-invalid @enderror" @required($field['required'] ?? false)>
                                        @if (!($field['required'] ?? false))
                                            <option value="">— None —</option>
                                        @else
                                            <option value="">Choose {{ strtolower($field['label']) }}</option>
                                        @endif
                                        @foreach ($field['choices'] as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($field['type'] === 'textarea')
                                    <textarea id="{{ $fieldId }}" name="{{ $field['name'] }}" rows="3" class="form-control @error($field['name']) is-invalid @enderror" @required($field['required'] ?? false)>{{ $value }}</textarea>
                                @else
                                    <input id="{{ $fieldId }}" type="{{ $field['type'] }}" name="{{ $field['name'] }}" value="{{ $value }}" class="form-control @error($field['name']) is-invalid @enderror" @required($field['required'] ?? false) @if(isset($field['step'])) step="{{ $field['step'] }}" @endif>
                                @endif

                                @error($field['name'])
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2 me-1" aria-hidden="true"></i>{{ $editing ? 'Save changes' : 'Add record' }}
                        </button>
                        @if ($editing)
                            <a href="{{ route($module.'.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        @endif
                    </div>
                </form>
            </div>
        </section>

        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                @foreach ($definition['columns'] as $label)
                                    <th class="text-nowrap px-3 py-3">{{ $label }}</th>
                                @endforeach
                                <th class="text-end px-3 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $record)
                                <tr>
                                    @foreach ($definition['columns'] as $path => $label)
                                        @php($cell = data_get($record, $path))
                                        <td class="px-3">
                                            @if ($cell instanceof \DateTimeInterface)
                                                {{ $cell->format('M j, Y') }}
                                            @elseif (str_contains($path, 'amount') || $path === 'balance')
                                                {{ is_numeric($cell) ? number_format((float) $cell, 2) : '—' }}
                                            @elseif ($path === 'status')
                                                <span class="badge text-bg-{{ $cell === 'present' || $cell === 'active' ? 'success' : ($cell === 'absent' || $cell === 'inactive' ? 'danger' : 'secondary') }}">{{ ucfirst((string) $cell) }}</span>
                                            @else
                                                {{ filled($cell) ? $cell : '—' }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-end text-nowrap px-3">
                                        <a href="{{ route($module.'.index', ['edit' => $record->id]) }}" class="btn btn-sm btn-outline-primary" aria-label="Edit record">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </a>
                                        <form method="POST" action="{{ route($module.'.destroy', $record->id) }}" class="d-inline" onsubmit="return confirm('Delete this record? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete record">
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($definition['columns']) + 1 }}" class="text-center text-secondary py-5">
                                        No records yet. Use the form above to add the first one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($records->hasPages())
                <div class="card-footer bg-white border-0 px-4">{{ $records->links() }}</div>
            @endif
        </section>
    </div>
@endsection
