@extends('layouts.superadmin')

@section('title', 'General System Settings')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="feather-settings"></i> General System Settings
                </h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('superadmin.config.general.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @foreach($settingsByGroup as $group => $settings)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0 text-uppercase">{{ ucfirst($group) }} Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($settings as $setting)
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                {{ $setting->display_name }}
                                                @if($setting->description)
                                                    <small class="text-muted d-block">{{ $setting->description }}</small>
                                                @endif
                                            </label>
                                            
                                            @switch($setting->setting_type)
                                                @case('text')
                                                @case('email')
                                                @case('number')
                                                    <input type="{{ $setting->setting_type }}" 
                                                           name="settings[{{ $setting->setting_key }}]" 
                                                           value="{{ old('settings.'.$setting->setting_key, $setting->setting_value) }}"
                                                           class="form-control">
                                                    @break
                                                    
                                                @case('textarea')
                                                    <textarea name="settings[{{ $setting->setting_key }}]" 
                                                              class="form-control" 
                                                              rows="3">{{ old('settings.'.$setting->setting_key, $setting->setting_value) }}</textarea>
                                                    @break
                                                    
                                                @case('select')
                                                    <select name="settings[{{ $setting->setting_key }}]" class="form-control">
                                                        @php
                                                            $options = json_decode($setting->options, true) ?? [];
                                                        @endphp
                                                        @foreach($options as $value => $label)
                                                            <option value="{{ $value }}" 
                                                                {{ old('settings.'.$setting->setting_key, $setting->setting_value) == $value ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @break
                                                    
                                                @case('boolean')
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" 
                                                               name="settings[{{ $setting->setting_key }}]" 
                                                               value="1" 
                                                               class="form-check-input" 
                                                               id="{{ $setting->setting_key }}"
                                                               {{ old('settings.'.$setting->setting_key, $setting->setting_value) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="{{ $setting->setting_key }}">
                                                            {{ $setting->setting_value ? 'Enabled' : 'Disabled' }}
                                                        </label>
                                                    </div>
                                                    @break
                                                    
                                                @case('file')
                                                    <div>
                                                        @if($setting->setting_value)
                                                            <div class="mb-2">
                                                                <img src="{{ asset($setting->setting_value) }}" 
                                                                     alt="{{ $setting->display_name }}" 
                                                                     style="max-height: 50px;">
                                                            </div>
                                                        @endif
                                                        <input type="file" 
                                                               name="files[{{ $setting->setting_key }}]" 
                                                               class="form-control">
                                                        @if($setting->setting_value)
                                                            <input type="hidden" 
                                                                   name="settings[{{ $setting->setting_key }}]" 
                                                                   value="{{ $setting->setting_value }}">
                                                        @endif
                                                    </div>
                                                    @break
                                            @endswitch
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="card">
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save"></i> Save All Settings
                            </button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                            
                            @if(empty($settingsByGroup))
                                <a href="{{ route('superadmin.config.general.seed') }}" 
                                   class="btn btn-outline-info float-end"
                                   onclick="return confirm('This will seed default settings. Continue?')">
                                    <i class="feather-download"></i> Seed Default Settings
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Handle boolean switches
    document.querySelectorAll('.form-check-input').forEach(switch => {
        switch.addEventListener('change', function() {
            const label = this.parentElement.querySelector('.form-check-label');
            label.textContent = this.checked ? 'Enabled' : 'Disabled';
        });
    });

    // Handle file preview
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const parent = this.parentElement;
            const preview = parent.querySelector('img');
            
            if (this.files && this.files[0]) {
                if (!preview) {
                    const img = document.createElement('img');
                    img.style.maxHeight = '50px';
                    img.classList.add('mb-2');
                    parent.prepend(img);
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = parent.querySelector('img');
                    if (img) {
                        img.src = e.target.result;
                    }
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
@endsection