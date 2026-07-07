@extends('layouts.app')

@section('title', 'Add New Stock')

@section('content')
    @push('scripts')
        <script>
            $(document).ready(function() {
                $('.select2').select2({
                    width: 'resolve',
                    theme: 'classic',
                    dropdownCssClass: 'border-outline-variant bg-white rounded-lg shadow-lg'
                });
            });
        </script>
        <style>
            .select2-container--classic .select2-selection--single {
                border: 1px solid #d4c4a8 !important;
                border-radius: 0.5rem !important;
                padding: 0.5rem 1rem !important;
                height: auto !important;
                min-height: 42px !important;
                background-color: #f2ebd9 !important;
            }
            .select2-container--classic.select2-container--open .select2-selection--single {
                border-color: #c09d6b !important;
            }
            .select2-container--classic .select2-selection--single .select2-selection__rendered {
                color: #2b1d12 !important;
                line-height: 1.5 !important;
                padding-left: 0 !important;
            }
            .select2-container--classic .select2-selection--single .select2-selection__arrow {
                height: 40px !important;
                right: 8px !important;
            }
            .select2-container--classic .select2-selection--single .select2-selection__placeholder {
                color: #8b7355 !important;
            }
            .dark .select2-container--classic .select2-selection--single {
                background-color: var(--color-surface-container) !important;
                border-color: #fff !important;
            }
            .dark .select2-container--classic .select2-selection--single .select2-selection__rendered {
                color: #fff !important;
            }
            .dark .select2-container--classic .select2-selection--single .select2-selection__placeholder {
                color: #aaa !important;
            }
            .dark .select2-dropdown {
                border-color: #fff !important;
            }
            .dark .select2-container--classic .select2-results__option--highlighted[aria-selected] {
                background-color: #000 !important;
                color: #fff !important;
            }
            .dark .select2-container--classic .select2-results__option[aria-selected=true] {
                background-color: #222 !important;
                color: #fff !important;
            }
            .select2-dropdown {
                border: 1px solid #d4c4a8 !important;
                border-radius: 0.5rem !important;
                overflow: hidden !important;
            }
            .select2-container--classic .select2-results__option--highlighted[aria-selected] {
                background-color: #f2ebd9 !important;
                color: #2b1d12 !important;
            }
            .select2-container--classic .select2-results__option[aria-selected=true] {
                background-color: #e8dfcc !important;
                color: #1a1009 !important;
            }
        </style>
    @endpush
@endsection
