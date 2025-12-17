<div class="page-content">
    <div class="container-fluid">
        <div class="settings-header card border-0 mb-4">
            <div class="card-body py-4">
                <h3 class="mb-1">General Settings</h3>
                <p class="text-muted mb-0">Manage global defaults for the FixitZed platform.</p>
            </div>
        </div>

        <div class="row g-4">
            @foreach($sections as $sectionTitle => $subsections)
                @php $isAdvanced = str_contains($sectionTitle, 'Advanced'); @endphp
                @if($isAdvanced)
                    <div class="col-12">
                        <div class="accordion" id="advancedSettingsAccordion">
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed px-0" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSettingsBody" aria-expanded="false" aria-controls="advancedSettingsBody">
                                        {{ $sectionTitle }}
                                    </button>
                                </h2>
                                <div id="advancedSettingsBody" class="accordion-collapse collapse" data-bs-parent="#advancedSettingsAccordion">
                                    <div class="accordion-body px-0 pt-0">
                                        <div class="card">
                                            <div class="card-header settings-card-header">
                                                <h5 class="mb-0">{{ $sectionTitle }}</h5>
                                            </div>
                                            <div class="card-body">
                                                @foreach($subsections as $subsection => $fields)
                                                    <div class="settings-group">
                                                        @foreach($fields as $field)
                                                            @include('livewire.settings.partials.setting-row', ['field' => $field])
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header settings-card-header">
                                <h5 class="mb-0">{{ $sectionTitle }}</h5>
                            </div>
                            <div class="card-body">
                                @foreach($subsections as $subsection => $fields)
                                    @if($subsection !== 'default')
                                        <h6 class="text-muted text-uppercase mb-3">{{ $subsection }}</h6>
                                    @endif
                                    <div class="settings-group">
                                        @foreach($fields as $field)
                                            @include('livewire.settings.partials.setting-row', ['field' => $field])
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <style>
        .settings-header {
            background: linear-gradient(135deg, #f46b45 0%, #ffa477 100%);
            color: #fff;
        }
        .settings-header .text-muted {
            color: rgba(255, 255, 255, 0.85) !important;
        }
        .settings-header h3 {
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .settings-header .card-body {
            border-radius: 14px;
        }
        .card {
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }
        .settings-card-header {
            background: linear-gradient(135deg, rgba(244, 107, 69, 0.18) 0%, rgba(255, 164, 119, 0.18) 100%);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 14px 14px 0 0;
        }
        .settings-row {
            display: grid;
            grid-template-columns: minmax(220px, 1.2fr) minmax(220px, 1fr) minmax(150px, 180px);
            gap: 1.5rem;
            align-items: center;
            padding: 0.9rem 0;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        }
        .settings-row:last-child {
            border-bottom: none;
        }
        .settings-meta h6 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .settings-meta small {
            display: block;
        }
        .settings-row .form-control {
            height: 44px;
            border-radius: 10px;
            border-color: rgba(15, 23, 42, 0.12);
        }
        .settings-row .form-control:focus {
            border-color: rgba(244, 107, 69, 0.6);
            box-shadow: 0 0 0 0.15rem rgba(244, 107, 69, 0.12);
        }
        .settings-actions {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            align-items: flex-start;
        }
        .settings-actions .btn {
            border-radius: 10px;
            padding: 0.45rem 1rem;
            font-weight: 600;
            box-shadow: 0 6px 12px rgba(244, 107, 69, 0.2);
        }
        .settings-actions .saved-indicator {
            color: #198754;
            font-size: 0.85rem;
        }
        .settings-group {
            padding: 0 0.25rem;
        }
        @media (max-width: 992px) {
            .settings-row {
                grid-template-columns: 1fr;
            }
            .settings-actions {
                flex-direction: row;
                align-items: center;
            }
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', ({type, message}) => {
                const toast = bootstrap.Toast.getOrCreateInstance(document.getElementById('app-toast'));
                const toastBody = document.querySelector('#app-toast .toast-body');
                const toastElement = document.getElementById('app-toast');
                toastElement.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
                toastElement.classList.add(`bg-${type}`);
                toastBody.innerText = message;
                toast.show();
            });
        });
    </script>
</div>
