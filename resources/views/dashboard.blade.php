<x-app-layout>
    {{-- Enhanced Header with Gradient --}}
    <x-slot name="header">
        <div class="gradient-theme-header rounded-3 p-4 mb-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar-greeting">
                            <div class="avatar-circle">
                                <i class="bi bi-person-circle"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="h3 mb-1 fw-bold text-white">{{ __('Welcome back,') }} {{ Auth::user()->name }}!</h1>
                            <p class="mb-0 text-white-50 small">
                                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                                <span class="mx-2">•</span>
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25">
                                    <i class="bi bi-circle-fill pulse-dot me-1" style="font-size: 0.5rem;"></i>{{ __('Online') }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <x-action-button variant="light" icon="person-circle" href="{{ route('profile.show') }}" wire:navigate tooltip="{{ __('Manage your profile') }}">
                        <span class="d-none d-sm-inline">{{ __('Profile') }}</span>
                    </x-action-button>
                    <x-action-button variant="primary" icon="book" href="https://laravel.com/docs" tooltip="{{ __('Documentation') }}" target="_blank" rel="noopener">
                        <span class="d-none d-sm-inline">{{ __('Docs') }}</span>
                    </x-action-button>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Enhanced Stats Cards with Animations --}}
    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-icon">
                    <i class="bi bi-rocket-takeoff"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-header">
                        <span class="stat-card-label">{{ __('Tasks Completed') }}</span>
                        <span class="stat-card-badge badge-success">
                            <i class="bi bi-arrow-up"></i>66%
                        </span>
                    </div>
                    <div class="stat-card-value">
                        <span class="stat-number">8</span>
                        <span class="stat-total">/ 12</span>
                    </div>
                    <div class="progress stat-progress">
                        <div class="progress-bar progress-bar-animated" style="width: 66%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card stat-card-success">
                <div class="stat-card-icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-header">
                        <span class="stat-card-label">{{ __('Build Status') }}</span>
                        <span class="stat-card-badge badge-success">
                            <i class="bi bi-check-circle-fill"></i>OK
                        </span>
                    </div>
                    <div class="stat-card-value">
                        <span class="stat-status-ready">{{ __('Ready') }}</span>
                    </div>
                    <p class="stat-card-info">
                        <i class="bi bi-shield-check me-1"></i>{{ __('All systems operational') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-icon">
                    <i class="bi bi-plugin"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-header">
                        <span class="stat-card-label">{{ __('Active Integrations') }}</span>
                        <span class="stat-card-badge badge-warning">
                            <i class="bi bi-circle-fill pulse-dot"></i>{{ __('Live') }}
                        </span>
                    </div>
                    <div class="stat-card-value">
                        <span class="stat-number">3</span>
                    </div>
                    <div class="stat-card-tags">
                        <span class="tag">Bootstrap</span>
                        <span class="tag">Livewire</span>
                        <span class="tag">Alpine</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card stat-card-info">
                <div class="stat-card-icon">
                    <i class="bi bi-flag-fill"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-header">
                        <span class="stat-card-label">{{ __('Next Milestone') }}</span>
                        <span class="stat-card-badge badge-info">
                            <i class="bi bi-clock"></i>{{ __('Soon') }}
                        </span>
                    </div>
                    <div class="stat-card-value">
                        <span class="stat-milestone">{{ __('CRUD Base') }}</span>
                    </div>
                    <p class="stat-card-info">
                        <i class="bi bi-building me-1"></i>{{ __('Blocks 4 & 5') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="row g-3 g-lg-4 mb-4">
        {{-- Welcome Component --}}
        <div class="col-12 col-xl-8">
            <div class="welcome-card">
                <x-welcome />
            </div>
        </div>

        {{-- Enhanced Checklist with Progress --}}
        <div class="col-12 col-xl-4">
            <div class="checklist-card">
                <div class="checklist-header">
                    <div class="checklist-icon">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <div class="checklist-title-area">
                        <h2 class="checklist-title">{{ __('Getting Started') }}</h2>
                        <div class="checklist-progress-wrapper">
                            <div class="progress checklist-progress">
                                <div class="progress-bar bg-gradient-success progress-bar-striped progress-bar-animated" style="width: 66%"></div>
                            </div>
                            <span class="checklist-percentage">66%</span>
                        </div>
                    </div>
                </div>

                <div class="checklist-body">
                    <div class="checklist-item checklist-item-completed">
                        <div class="checklist-item-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="checklist-item-content">
                            <div class="checklist-item-title">{{ __('Vite & Bootstrap dependencies') }}</div>
                            <div class="checklist-item-description">{{ __('SCSS structure consolidated') }}</div>
                        </div>
                    </div>

                    <div class="checklist-item checklist-item-completed">
                        <div class="checklist-item-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="checklist-item-content">
                            <div class="checklist-item-title">{{ __('Bootstrap 5 base layout') }}</div>
                            <div class="checklist-item-description">{{ __('Responsive sidebar & dark mode') }}</div>
                        </div>
                    </div>

                    <div class="checklist-item checklist-item-active">
                        <div class="checklist-item-icon">
                            <div class="spinner-border spinner-border-sm"></div>
                        </div>
                        <div class="checklist-item-content">
                            <div class="checklist-item-title">{{ __('Plan initial CRUDs') }}</div>
                            <div class="checklist-item-description">{{ __('Priority modules for Livewire') }}</div>
                        </div>
                    </div>
                </div>

                <div class="checklist-footer">
                    <a href="#" class="btn btn-outline-primary w-100 btn-hover-lift">
                        <i class="bi bi-arrow-right-circle me-2"></i>{{ __('View all tasks') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Enhanced CRUD Preview Table --}}
    @php
        $showcaseRecords = [
            ['name' => 'Leads', 'entity' => 'Sales', 'status' => 'In Progress', 'variant' => 'primary', 'updated_at' => '5 min ago', 'icon' => 'people'],
            ['name' => 'Projects', 'entity' => 'PMO', 'status' => 'Review', 'variant' => 'warning', 'updated_at' => '32 min ago', 'icon' => 'kanban'],
            ['name' => 'Finance', 'entity' => 'Accounting', 'status' => 'Approved', 'variant' => 'success', 'updated_at' => '1h ago', 'icon' => 'cash-coin'],
        ];
    @endphp

    <div class="mb-4">
        <div class="modern-table-card">
            <div class="modern-table-header">
                <div>
                    <h3 class="modern-table-title">{{ __('CRUD Structure Preview') }}</h3>
                    <p class="modern-table-description">{{ __('Component kit ready for real data from priority modules.') }}</p>
                </div>
                <div class="modern-table-actions">
                    <x-action-button variant="outline-secondary" icon="funnel" tooltip="{{ __('Filter records') }}" size="sm" />
                    <x-action-button variant="primary" icon="plus" tooltip="{{ __('Create new record') }}" size="sm">
                        <span class="d-none d-sm-inline">{{ __('New') }}</span>
                    </x-action-button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Module') }}</th>
                            <th class="d-none d-md-table-cell">{{ __('Team') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="d-none d-sm-table-cell">{{ __('Updated') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($showcaseRecords as $record)
                            <tr class="modern-table-row">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="module-icon module-icon-{{ $record['variant'] }}">
                                            <i class="bi bi-{{ $record['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <div class="module-name">{{ $record['name'] }}</div>
                                            <div class="module-entity d-md-none">{{ $record['entity'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="team-badge">{{ $record['entity'] }}</span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $record['variant'] }}">
                                        <i class="bi bi-circle-fill"></i>{{ $record['status'] }}
                                    </span>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <span class="text-muted small">
                                        <i class="bi bi-clock me-1"></i>{{ $record['updated_at'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-action" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-action btn-action-primary" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="modern-table-footer">
                <span class="table-info">
                    <i class="bi bi-info-circle me-1"></i>{{ __('Bootstrap + Livewire table components') }}
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="{{ __('Refresh') }}">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Enhanced Components Showcase --}}
    <div class="row g-3 g-lg-4">
        <div class="col-12 col-lg-6">
            <div class="showcase-card">
                <div class="showcase-card-header">
                    <div class="showcase-icon showcase-icon-success">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div>
                        <h3 class="showcase-title">{{ __('Alerts & Notifications') }}</h3>
                        <p class="showcase-description">{{ __('Ready-to-use components') }}</p>
                    </div>
                </div>
                <div class="showcase-card-body">
                    <x-alert variant="success" icon="check-circle-fill" dismissible>
                        <strong>{{ __('Success!') }}</strong> {{ __('Reusable alert with icon and dismiss button.') }}
                    </x-alert>

                    <x-alert variant="info" icon="info-circle-fill">
                        <strong>{{ __('Tip:') }}</strong> {{ __('Alerts support multiple variants and icons.') }}
                    </x-alert>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="showcase-card">
                <div class="showcase-card-header">
                    <div class="showcase-icon showcase-icon-info">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="showcase-title">{{ __('Toast Notifications') }}</h3>
                        <p class="showcase-description">{{ __('Auto-initialized by JS') }}</p>
                    </div>
                    <button class="btn btn-primary btn-sm btn-hover-lift"
                            type="button"
                            x-data
                            x-on:click="
                                let toastEl = document.getElementById('starter-toast-example');
                                let toast = bootstrap.Toast.getOrCreateInstance(toastEl);
                                toast.show();
                            ">
                        <i class="bi bi-megaphone-fill me-1"></i>
                        <span class="d-none d-sm-inline">{{ __('Show Toast') }}</span>
                        <span class="d-sm-none">{{ __('Show') }}</span>
                    </button>
                </div>
                <div class="showcase-card-body">
                    <div class="toast-container position-relative">
                        <x-toast id="starter-toast-example" icon="megaphone" :autohide="false">
                            {{ __('This toast can be reused on any screen!') }}
                        </x-toast>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom Styles for Enhanced Dashboard --}}
    <style>
        /* Dashboard Header - uses global .gradient-theme-header class */

        /* Avatar Greeting */
        .avatar-greeting .avatar-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        /* Pulse Animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .pulse-dot {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Enhanced Stat Cards */
        .stat-card {
            background: var(--bs-body-bg);
            border-radius: 16px;
            padding: 1.5rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--bs-border-color);
        }

        [data-bs-theme="dark"] .stat-card {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--stat-color), transparent);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .stat-card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.4);
            background: rgba(255, 255, 255, 0.08);
        }

        .stat-card-primary { --stat-color: var(--bs-primary); }
        .stat-card-success { --stat-color: var(--bs-success); }
        .stat-card-warning { --stat-color: var(--bs-warning); }
        .stat-card-info { --stat-color: var(--bs-info); }

        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            background: var(--stat-color);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-card-content {
            flex: 1;
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .stat-card-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--bs-secondary);
        }

        .stat-card-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge-success {
            background: rgba(var(--bs-success-rgb), 0.1);
            color: var(--bs-success);
        }

        .badge-warning {
            background: rgba(var(--bs-warning-rgb), 0.1);
            color: var(--bs-warning);
        }

        .badge-info {
            background: rgba(var(--bs-info-rgb), 0.1);
            color: var(--bs-info);
        }

        .stat-card-value {
            margin-bottom: 0.75rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--bs-body-color);
        }

        .stat-total {
            font-size: 1rem;
            color: var(--bs-secondary);
            margin-left: 0.25rem;
        }

        .stat-status-ready {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--bs-success);
        }

        .stat-milestone {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--bs-info);
        }

        .stat-card-info {
            font-size: 0.875rem;
            color: var(--bs-secondary);
            margin: 0;
        }

        .stat-progress {
            height: 6px;
            border-radius: 3px;
            background: rgba(var(--bs-secondary-rgb), 0.1);
            overflow: hidden;
        }

        .stat-progress .progress-bar {
            background: var(--stat-color);
        }

        .stat-card-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .stat-card-tags .tag {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            background: rgba(var(--bs-secondary-rgb), 0.1);
            color: var(--bs-body-color);
        }

        /* Enhanced Checklist */
        .checklist-card {
            background: var(--bs-body-bg);
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            border: 1px solid var(--bs-border-color);
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .checklist-card {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .checklist-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05), rgba(var(--bs-primary-rgb), 0.1));
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .checklist-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--bs-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .checklist-title-area {
            flex: 1;
        }

        .checklist-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--bs-body-color);
        }

        .checklist-progress-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .checklist-progress {
            flex: 1;
            height: 8px;
            border-radius: 4px;
            background: rgba(var(--bs-secondary-rgb), 0.1);
        }

        .checklist-percentage {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--bs-success);
            min-width: 40px;
            text-align: right;
        }

        .bg-gradient-success {
            background: linear-gradient(90deg, var(--bs-success), #20c997);
        }

        .checklist-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .checklist-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .checklist-item-completed {
            background: rgba(var(--bs-success-rgb), 0.05);
            border: 1px solid rgba(var(--bs-success-rgb), 0.2);
        }

        .checklist-item-active {
            background: rgba(var(--bs-primary-rgb), 0.05);
            border: 1px solid rgba(var(--bs-primary-rgb), 0.3);
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.1);
        }

        .checklist-item-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .checklist-item-completed .checklist-item-icon {
            color: var(--bs-success);
        }

        .checklist-item-active .checklist-item-icon {
            color: var(--bs-primary);
        }

        .checklist-item-content {
            flex: 1;
            min-width: 0;
        }

        .checklist-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--bs-body-color);
        }

        .checklist-item-completed .checklist-item-title {
            color: var(--bs-success);
        }

        .checklist-item-active .checklist-item-title {
            color: var(--bs-primary);
        }

        .checklist-item-description {
            font-size: 0.875rem;
            color: var(--bs-secondary);
        }

        .checklist-footer {
            padding: 1rem 1.5rem 1.5rem;
        }

        .btn-hover-lift {
            transition: all 0.3s ease;
        }

        .btn-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Modern Table Card */
        .modern-table-card {
            background: var(--bs-body-bg);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--bs-border-color);
        }

        [data-bs-theme="dark"] .modern-table-card {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .modern-table-header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            border-bottom: 1px solid var(--bs-border-color);
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.03), rgba(var(--bs-primary-rgb), 0.06));
        }

        .modern-table-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--bs-body-color);
        }

        .modern-table-description {
            font-size: 0.875rem;
            color: var(--bs-secondary);
            margin: 0;
        }

        .modern-table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .modern-table {
            color: var(--bs-body-color);
        }

        .modern-table thead th {
            border-bottom: 2px solid var(--bs-border-color);
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--bs-secondary);
            background: rgba(var(--bs-secondary-rgb), 0.03);
        }

        .modern-table tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .modern-table-row {
            transition: all 0.2s ease;
        }

        .modern-table-row:hover {
            background: rgba(var(--bs-primary-rgb), 0.03);
        }

        [data-bs-theme="dark"] .modern-table-row:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .module-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .module-icon-primary {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }

        .module-icon-warning {
            background: rgba(var(--bs-warning-rgb), 0.1);
            color: var(--bs-warning);
        }

        .module-icon-success {
            background: rgba(var(--bs-success-rgb), 0.1);
            color: var(--bs-success);
        }

        .module-name {
            font-weight: 600;
            color: var(--bs-body-color);
            margin-bottom: 0.125rem;
        }

        .module-entity {
            font-size: 0.875rem;
            color: var(--bs-secondary);
        }

        .team-badge {
            font-size: 0.875rem;
            color: var(--bs-secondary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-badge i {
            font-size: 0.5rem;
        }

        .status-primary {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }

        .status-warning {
            background: rgba(var(--bs-warning-rgb), 0.1);
            color: var(--bs-warning);
        }

        .status-success {
            background: rgba(var(--bs-success-rgb), 0.1);
            color: var(--bs-success);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            padding: 0;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--bs-border-color);
            background: transparent;
            color: var(--bs-body-color);
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            background: rgba(var(--bs-secondary-rgb), 0.1);
            border-color: var(--bs-secondary);
            transform: scale(1.05);
        }

        .btn-action-primary:hover {
            background: rgba(var(--bs-primary-rgb), 0.1);
            border-color: var(--bs-primary);
            color: var(--bs-primary);
        }

        .modern-table-footer {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--bs-border-color);
            background: rgba(var(--bs-secondary-rgb), 0.02);
        }

        .table-info {
            font-size: 0.875rem;
            color: var(--bs-secondary);
            display: flex;
            align-items: center;
        }

        /* Showcase Cards */
        .showcase-card {
            background: var(--bs-body-bg);
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            border: 1px solid var(--bs-border-color);
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .showcase-card {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .showcase-card-header {
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            border-bottom: 1px solid var(--bs-border-color);
            background: linear-gradient(135deg, rgba(var(--bs-secondary-rgb), 0.02), rgba(var(--bs-secondary-rgb), 0.05));
        }

        .showcase-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .showcase-icon-success {
            background: rgba(var(--bs-success-rgb), 0.1);
            color: var(--bs-success);
        }

        .showcase-icon-info {
            background: rgba(var(--bs-info-rgb), 0.1);
            color: var(--bs-info);
        }

        .showcase-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--bs-body-color);
        }

        .showcase-description {
            font-size: 0.875rem;
            color: var(--bs-secondary);
            margin: 0;
        }

        .showcase-card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* Welcome Card Enhancement */
        .welcome-card {
            transition: all 0.3s ease;
        }

        /* Responsive Adjustments */
        @media (max-width: 991px) {
            .stat-card-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .stat-number {
                font-size: 1.75rem;
            }

            .modern-table-header {
                flex-direction: column;
            }
        }

        @media (max-width: 767px) {
            .dashboard-header-gradient {
                padding: 1.5rem !important;
            }

            .avatar-greeting .avatar-circle {
                width: 48px;
                height: 48px;
                font-size: 1.5rem;
            }

            .stat-card {
                padding: 1rem;
            }
        }
    </style>
</x-app-layout>
