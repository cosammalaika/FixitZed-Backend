@php
    $user = $fixer->user;
    $workPhotos = array_values(array_filter((array) ($user->work_photos ?? [])));
    $supporting = array_values(array_filter((array) ($user->documents ?? [])));

    $resolveUrl = function (?string $path) {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return route('files.show', ['path' => $path]);
    };

    $isImage = function (?string $path) {
        if (! $path) {
            return false;
        }

        $cleanPath = strtolower((string) strtok($path, '?'));
        $extension = pathinfo($cleanPath, PATHINFO_EXTENSION);

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg', 'avif'], true);
    };

    $status = strtolower((string) ($fixer->status ?? 'pending'));
    $statusMeta = match ($status) {
        'approved' => ['label' => 'Approved', 'class' => 'fixer-status-badge is-approved'],
        'rejected' => ['label' => 'Rejected', 'class' => 'fixer-status-badge is-rejected'],
        default => ['label' => 'Pending', 'class' => 'fixer-status-badge is-pending'],
    };

    $profileUrl = $resolveUrl($user->profile_photo_path);
    $frontUrl = $resolveUrl($user->nrc_front_path);
    $backUrl = $resolveUrl($user->nrc_back_path);
    $workPhotoCount = count($workPhotos);
@endphp

<div class="fixer-application-shell" id="fixer-application-{{ $fixer->id }}">
    <style>
        .fixer-application-shell .fixer-application-card {
            border: 1px solid #e8ebf0;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .fixer-application-shell .fixer-section {
            padding: 24px;
        }

        .fixer-application-shell .fixer-section + .fixer-section {
            border-top: 1px solid #edf1f6;
        }

        .fixer-application-shell .fixer-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
        }

        .fixer-application-shell .fixer-title {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .fixer-application-shell .fixer-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }

        .fixer-application-shell .fixer-status-badge.is-pending {
            background: #fff7e9;
            color: #a55e00;
            border-color: #ffd59c;
        }

        .fixer-application-shell .fixer-status-badge.is-approved {
            background: #e8f9ef;
            color: #0f8f44;
            border-color: #93dfb3;
        }

        .fixer-application-shell .fixer-status-badge.is-rejected {
            background: #ffebee;
            color: #b4232d;
            border-color: #f3a6ad;
        }

        .fixer-application-shell .fixer-header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .fixer-application-shell .fixer-btn {
            min-width: 128px;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 14px;
        }

        .fixer-application-shell .fixer-btn-approve {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }

        .fixer-application-shell .fixer-btn-approve:hover {
            background: #15803d;
            border-color: #15803d;
            color: #fff;
        }

        .fixer-application-shell .fixer-btn-reject,
        .fixer-application-shell .fixer-btn-revoke {
            border: 1px solid #dc3545;
            color: #dc3545;
            background: #fff;
        }

        .fixer-application-shell .fixer-btn-reject:hover,
        .fixer-application-shell .fixer-btn-revoke:hover {
            background: #fff2f4;
            color: #b4232d;
            border-color: #b4232d;
        }

        .fixer-application-shell .fixer-section-title {
            margin: 0 0 16px;
            font-size: 1rem;
            font-weight: 700;
            color: #1f2a3d;
        }

        .fixer-application-shell .fixer-profile-wrap {
            border: 1px solid #e8ebf0;
            border-radius: 14px;
            background: #f8fafc;
            padding: 14px;
            height: 100%;
        }

        .fixer-application-shell .fixer-profile-label {
            margin-bottom: 10px;
            font-size: 0.83rem;
            font-weight: 700;
            color: #516074;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .fixer-application-shell .fixer-profile-preview {
            height: 260px;
        }

        .fixer-application-shell .fixer-info-item {
            border: 1px solid #e8ebf0;
            border-radius: 12px;
            background: #fff;
            padding: 14px;
            height: 100%;
        }

        .fixer-application-shell .fixer-info-label {
            margin: 0 0 6px;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            color: #64748b;
        }

        .fixer-application-shell .fixer-info-value {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }

        .fixer-application-shell .fixer-doc-card {
            border: 1px solid #e7edf4;
            border-radius: 14px;
            padding: 12px;
            background: #fff;
            height: 100%;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        }

        .fixer-application-shell .fixer-doc-title {
            margin: 0 0 10px;
            font-size: 0.9rem;
            font-weight: 700;
            color: #334155;
        }

        .fixer-application-shell .fixer-media-button {
            position: relative;
            width: 100%;
            height: 170px;
            border: 1px solid #e5eaf1;
            border-radius: 12px;
            overflow: hidden;
            background: #f8fafc;
            cursor: zoom-in;
            display: block;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .fixer-application-shell .fixer-media-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
        }

        .fixer-application-shell .fixer-media-button img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .fixer-application-shell .fixer-media-overlay {
            position: absolute;
            inset: auto 0 0;
            padding: 10px 12px;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0) 0%, rgba(15, 23, 42, 0.88) 100%);
            color: #fff;
            font-size: 0.82rem;
            font-weight: 600;
            text-align: center;
        }

        .fixer-application-shell .fixer-file-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #2b5dab;
            text-decoration: none;
        }

        .fixer-application-shell .fixer-file-link:hover {
            text-decoration: underline;
            color: #1d4a8d;
        }

        .fixer-application-shell .fixer-count-badge {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            border-radius: 999px;
            padding: 6px 10px;
            color: #334155;
            background: #f1f5f9;
            border: 1px solid #dbe5ef;
        }

        .fixer-application-shell .fixer-work-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .fixer-application-shell .fixer-work-photo {
            height: 120px;
        }

        .fixer-application-shell .fixer-empty-state {
            border: 1px dashed #d1dae6;
            border-radius: 12px;
            background: #f8fafc;
            color: #64748b;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .fixer-application-shell .fixer-empty-state svg {
            width: 18px;
            height: 18px;
            color: #94a3b8;
            flex-shrink: 0;
        }

        .fixer-application-shell .fixer-bio {
            margin: 0;
            border: 1px solid #e8ebf0;
            border-radius: 12px;
            background: #f8fafc;
            padding: 14px;
            color: #1e293b;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .fixer-image-preview-overlay {
            position: fixed;
            inset: 0;
            z-index: 2055;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.15s ease;
        }

        .fixer-image-preview-overlay.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .fixer-image-preview-overlay .fixer-image-preview-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.82);
        }

        .fixer-image-preview-overlay .fixer-image-preview-dialog {
            position: relative;
            margin: 2.5vh auto;
            width: min(960px, calc(100% - 2rem));
            max-height: 95vh;
            background: #fff;
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 25px 50px rgba(2, 6, 23, 0.32);
            overflow: hidden;
        }

        .fixer-image-preview-overlay .fixer-image-preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .fixer-image-preview-overlay .fixer-image-preview-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
        }

        .fixer-image-preview-overlay .fixer-image-preview-close {
            border: 1px solid #dbe5ef;
            background: #fff;
            border-radius: 8px;
            color: #334155;
            font-weight: 700;
            width: 36px;
            height: 36px;
            line-height: 1;
            cursor: pointer;
        }

        .fixer-image-preview-overlay .fixer-image-preview-close:hover {
            background: #f1f5f9;
        }

        .fixer-image-preview-overlay .fixer-image-preview-frame {
            background: #f8fafc;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            max-height: calc(95vh - 86px);
            min-height: 220px;
            overflow: auto;
            border: 1px solid #e8ebf0;
        }

        .fixer-image-preview-overlay .fixer-image-preview-frame img {
            max-width: 100%;
            max-height: calc(95vh - 106px);
            object-fit: contain;
        }

        body.fixer-preview-open {
            overflow: hidden;
        }

        @media (max-width: 991.98px) {
            .fixer-application-shell .fixer-work-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .fixer-application-shell .fixer-section {
                padding: 18px;
            }

            .fixer-application-shell .fixer-profile-preview {
                height: 220px;
            }

            .fixer-application-shell .fixer-work-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>

    <div class="fixer-application-card">
        <section class="fixer-section fixer-header">
            <div>
                <h4 class="fixer-title">Fixer Application</h4>
                <span class="{{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
            </div>

            <div class="fixer-header-actions">
                @if ($status === 'pending')
                    <button
                        type="button"
                        class="btn fixer-btn fixer-btn-approve"
                        wire:click="approve"
                        wire:confirm="Are you sure you want to approve this fixer?"
                        wire:loading.attr="disabled"
                        wire:target="approve,reject">
                        Approve
                    </button>
                    <button
                        type="button"
                        class="btn fixer-btn fixer-btn-reject"
                        wire:click="reject"
                        wire:confirm="Are you sure you want to reject this fixer?"
                        wire:loading.attr="disabled"
                        wire:target="approve,reject">
                        Reject
                    </button>
                @elseif ($status === 'approved')
                    <button
                        type="button"
                        class="btn fixer-btn fixer-btn-revoke"
                        wire:click="reject"
                        wire:confirm="Are you sure you want to revoke this fixer approval?"
                        wire:loading.attr="disabled"
                        wire:target="approve,reject">
                        Suspend / Revoke
                    </button>
                @elseif ($status === 'rejected')
                    <button
                        type="button"
                        class="btn fixer-btn fixer-btn-approve"
                        wire:click="approve"
                        wire:confirm="Are you sure you want to reconsider and approve this fixer?"
                        wire:loading.attr="disabled"
                        wire:target="approve,reject">
                        Reconsider / Approve
                    </button>
                @endif
            </div>
        </section>

        <section class="fixer-section">
            <h6 class="fixer-section-title">Personal Information</h6>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="fixer-profile-wrap">
                        <div class="fixer-profile-label">Profile Photo</div>
                        @if ($profileUrl)
                            <button
                                type="button"
                                class="fixer-media-button fixer-profile-preview"
                                data-fixer-preview-target="fixer-preview-{{ $fixer->id }}"
                                data-fixer-preview-src="{{ $profileUrl }}"
                                data-fixer-preview-title="Profile Photo">
                                <img src="{{ $profileUrl }}" alt="Profile photo">
                                <span class="fixer-media-overlay">View full image</span>
                            </button>
                        @else
                            <div class="fixer-empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 6a2 2 0 0 1 2-2h8l6 6v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/>
                                    <path d="M14 4v6h6"/>
                                </svg>
                                <span>Profile photo not provided.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="fixer-info-item">
                                <p class="fixer-info-label">Name</p>
                                <p class="fixer-info-value">{{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fixer-info-item">
                                <p class="fixer-info-label">Email</p>
                                <p class="fixer-info-value">{{ $user->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fixer-info-item">
                                <p class="fixer-info-label">Phone</p>
                                <p class="fixer-info-value">{{ $user->contact_number ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fixer-info-item">
                                <p class="fixer-info-label">Applied On</p>
                                <p class="fixer-info-value">{{ optional($fixer->created_at)->format('M d, Y') ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fixer-info-item">
                                <p class="fixer-info-label">Accepted Terms</p>
                                <p class="fixer-info-value">{{ $fixer->accepted_terms_at ? $fixer->accepted_terms_at->format('M d, Y H:i') : 'Pending' }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fixer-info-item">
                                <p class="fixer-info-label">Rating</p>
                                <p class="fixer-info-value">{{ $fixer->rating_avg !== null ? number_format((float) $fixer->rating_avg, 1) . '/5' : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="fixer-section">
            <h6 class="fixer-section-title">Verification Documents</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="fixer-doc-card">
                        <p class="fixer-doc-title">NRC Front</p>
                        @if ($frontUrl && $isImage($user->nrc_front_path))
                            <button
                                type="button"
                                class="fixer-media-button"
                                data-fixer-preview-target="fixer-preview-{{ $fixer->id }}"
                                data-fixer-preview-src="{{ $frontUrl }}"
                                data-fixer-preview-title="NRC Front">
                                <img src="{{ $frontUrl }}" alt="NRC Front">
                                <span class="fixer-media-overlay">View full image</span>
                            </button>
                        @elseif ($frontUrl)
                            <a href="{{ $frontUrl }}" target="_blank" class="fixer-file-link" rel="noopener noreferrer">
                                Open uploaded document
                            </a>
                        @else
                            <div class="fixer-empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 6a2 2 0 0 1 2-2h8l6 6v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/>
                                    <path d="M14 4v6h6"/>
                                </svg>
                                <span>NRC front not uploaded.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fixer-doc-card">
                        <p class="fixer-doc-title">NRC Back</p>
                        @if ($backUrl && $isImage($user->nrc_back_path))
                            <button
                                type="button"
                                class="fixer-media-button"
                                data-fixer-preview-target="fixer-preview-{{ $fixer->id }}"
                                data-fixer-preview-src="{{ $backUrl }}"
                                data-fixer-preview-title="NRC Back">
                                <img src="{{ $backUrl }}" alt="NRC Back">
                                <span class="fixer-media-overlay">View full image</span>
                            </button>
                        @elseif ($backUrl)
                            <a href="{{ $backUrl }}" target="_blank" class="fixer-file-link" rel="noopener noreferrer">
                                Open uploaded document
                            </a>
                        @else
                            <div class="fixer-empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 6a2 2 0 0 1 2-2h8l6 6v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/>
                                    <path d="M14 4v6h6"/>
                                </svg>
                                <span>NRC back not uploaded.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="fixer-section">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h6 class="fixer-section-title mb-0">Work Portfolio</h6>
                <span class="fixer-count-badge">Work Photos ({{ $workPhotoCount }})</span>
            </div>

            @if ($workPhotoCount > 0)
                <div class="fixer-work-grid">
                    @foreach ($workPhotos as $index => $path)
                        @php $url = $resolveUrl($path); @endphp
                        @if ($url)
                            <button
                                type="button"
                                class="fixer-media-button fixer-work-photo"
                                data-fixer-preview-target="fixer-preview-{{ $fixer->id }}"
                                data-fixer-preview-src="{{ $url }}"
                                data-fixer-preview-title="Work Photo {{ $index + 1 }}">
                                <img src="{{ $url }}" alt="Work photo {{ $index + 1 }}">
                                <span class="fixer-media-overlay">View full image</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="fixer-empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 6a2 2 0 0 1 2-2h8l6 6v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/>
                        <path d="M14 4v6h6"/>
                    </svg>
                    <span>No work photos uploaded.</span>
                </div>
            @endif
        </section>

        <section class="fixer-section">
            <h6 class="fixer-section-title">Supporting Documents</h6>
            @if (! empty($supporting))
                <div class="row g-3">
                    @foreach ($supporting as $index => $path)
                        @php
                            $url = $resolveUrl($path);
                            $name = basename((string) $path);
                        @endphp
                        @if ($url)
                            <div class="col-md-6">
                                <div class="fixer-doc-card">
                                    <p class="fixer-doc-title mb-2">{{ $name ?: 'Document ' . ($index + 1) }}</p>
                                    @if ($isImage($path))
                                        <button
                                            type="button"
                                            class="fixer-media-button"
                                            data-fixer-preview-target="fixer-preview-{{ $fixer->id }}"
                                            data-fixer-preview-src="{{ $url }}"
                                            data-fixer-preview-title="{{ $name ?: 'Supporting Document ' . ($index + 1) }}">
                                            <img src="{{ $url }}" alt="{{ $name ?: 'Supporting Document ' . ($index + 1) }}">
                                            <span class="fixer-media-overlay">View full image</span>
                                        </button>
                                    @else
                                        <a href="{{ $url }}" target="_blank" class="fixer-file-link" rel="noopener noreferrer">
                                            Open document
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="fixer-empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 6a2 2 0 0 1 2-2h8l6 6v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/>
                        <path d="M14 4v6h6"/>
                    </svg>
                    <span>No supporting documents uploaded.</span>
                </div>
            @endif
        </section>

        <section class="fixer-section">
            <h6 class="fixer-section-title">Bio</h6>
            <p class="fixer-bio">{{ $fixer->bio ?? 'No bio available.' }}</p>
        </section>

        <section class="fixer-section">
            <h6 class="fixer-section-title">Skilled Services</h6>
            <ul class="mb-0 ps-3">
                @forelse ($fixer->services as $service)
                    <li class="mb-1">{{ $service->name }}</li>
                @empty
                    <li class="text-muted">No services listed.</li>
                @endforelse
            </ul>
        </section>
    </div>

    <div class="fixer-image-preview-overlay" id="fixer-preview-{{ $fixer->id }}" aria-hidden="true">
        <div class="fixer-image-preview-backdrop" data-fixer-preview-close></div>
        <div class="fixer-image-preview-dialog">
            <div class="fixer-image-preview-header">
                <p class="fixer-image-preview-title" data-fixer-preview-title>Image Preview</p>
                <button type="button" class="fixer-image-preview-close" data-fixer-preview-close aria-label="Close">
                    x
                </button>
            </div>
            <div class="fixer-image-preview-frame">
                <img src="" alt="Document preview" data-fixer-preview-image>
            </div>
        </div>
    </div>
</div>

@once
    <script>
        (() => {
            if (window.__fixerImagePreviewBound) {
                return;
            }

            window.__fixerImagePreviewBound = true;

            const closeOverlay = (overlay) => {
                if (!overlay) {
                    return;
                }

                overlay.classList.remove('is-open');
                overlay.setAttribute('aria-hidden', 'true');

                const image = overlay.querySelector('[data-fixer-preview-image]');
                if (image) {
                    image.setAttribute('src', '');
                    image.setAttribute('alt', 'Document preview');
                }

                if (!document.querySelector('.fixer-image-preview-overlay.is-open')) {
                    document.body.classList.remove('fixer-preview-open');
                }
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-fixer-preview-src]');
                if (trigger) {
                    event.preventDefault();

                    const targetId = trigger.getAttribute('data-fixer-preview-target');
                    const src = trigger.getAttribute('data-fixer-preview-src');
                    const titleText = trigger.getAttribute('data-fixer-preview-title') || 'Image Preview';
                    if (!targetId || !src) {
                        return;
                    }

                    const overlay = document.getElementById(targetId);
                    if (!overlay) {
                        return;
                    }

                    const image = overlay.querySelector('[data-fixer-preview-image]');
                    const title = overlay.querySelector('[data-fixer-preview-title]');
                    if (image) {
                        image.setAttribute('src', src);
                        image.setAttribute('alt', titleText);
                    }

                    if (title) {
                        title.textContent = titleText;
                    }

                    overlay.classList.add('is-open');
                    overlay.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('fixer-preview-open');
                    return;
                }

                const closeTrigger = event.target.closest('[data-fixer-preview-close]');
                if (closeTrigger) {
                    closeOverlay(closeTrigger.closest('.fixer-image-preview-overlay'));
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') {
                    return;
                }

                document.querySelectorAll('.fixer-image-preview-overlay.is-open').forEach(closeOverlay);
            });
        })();
    </script>
@endonce
