@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-3xl font-bold text-gray-900">Select a Facility for Galleries</h1>
            <p class="text-gray-600">Choose a facility below to manage its photo galleries.</p>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-facility-select :facilities="$facilities" :type="$type" />
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                {{ ucfirst($type) }} Management
            </h3>
            <p class="text-gray-500 mb-6">
                Select a facility above to manage its {{ $type }}. This page will allow you to:
            </p>
            <ul class="text-sm text-gray-600 space-y-2 max-w-md mx-auto text-left">
                @if($type === 'gallery')
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Upload and organize facility photos
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Create themed photo albums
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Add captions and descriptions
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Set featured images for galleries
                </li>
                @elseif($type === 'news')
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Publish facility news and updates
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Schedule announcements
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Add images and attachments
                </li>
                @elseif($type === 'testimonial')
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Collect and display testimonials
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Approve or reject submissions
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Add author details
                </li>
                @elseif($type === 'faq')
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Manage facility FAQs
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Add answers and categories
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Set featured questions
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection