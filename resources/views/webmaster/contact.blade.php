@extends('layouts.default-template')

@section('title', 'Contact Webmaster - ' . ($facility['name'] ?? 'Bio-Pacific'))

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Contact the Webmaster</h1>
                <p class="text-lg text-slate-600">{{ $facility['name'] ?? 'Bio-Pacific' }}</p>
                <p class="text-sm text-slate-500 mt-2">For website errors, change requests, or technical issues only.
                </p>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-8 md:p-12">
            @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800 border border-green-300">
                {{ session('success') }}
            </div>
            @endif
            @if($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800 border border-red-300">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="prose max-w-none mb-8">
                <p class="text-slate-700 mb-2">
                    Use this form strictly for <span class="font-semibold">website-related matters</span> such as
                    reporting errors, requesting modifications, or alerting us to technical concerns. All other general
                    inquiries should be submitted through our <a
                        href="/facility/{{ $facility['slug'] ?? 'facility' }}#contact"
                        class="underline text-blue-700 hover:text-blue-900">Contact Us</a> page.
                </p>
            </div>
            <form method="POST" action="{{ route('webmaster.contact.submit') }}" class="space-y-5"
                enctype="multipart/form-data" id="webmaster-contact-form" novalidate>
                @if(isset($facility) && isset($facility['id']))
                <input type="hidden" name="facility_id" value="{{ $facility['id'] }}">
                @endif
                @csrf
                <div>
                    <label for="name" class="block text-slate-700 text-sm font-medium mb-1">Your Name</label>
                    <input type="text" id="name" name="name" required
                        class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white/90">
                </div>
                <div>
                    <label for="email" class="block text-slate-700 text-sm font-medium mb-1">Your Email</label>
                    <input type="email" id="email" name="email" required
                        class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white/90">
                </div>
                <div>
                    <label for="subject" class="block text-slate-700 text-sm font-medium mb-1">Subject</label>
                    <input type="text" id="subject" name="subject" required
                        class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white/90">
                </div>
                <div>
                    <label for="message" class="block text-slate-700 text-sm font-medium mb-1">Message</label>

                    <textarea id="message" name="message" rows="5" required
                        class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white/90"
                        placeholder="Describe the error, issue, or change needed in detail..."></textarea>
                </div>
                <div>
                    <label for="screenshots" class="block text-slate-700 text-sm font-medium mb-1">Upload Screenshots
                        (optional)</label>
                    <p class="text-xs text-slate-500 mb-2">Attach one or more screenshots of the error or the part of
                        the site you are reporting. Screenshots help the webmaster quickly identify and resolve the
                        issue. Accepted formats: JPG, PNG, GIF. Max 5 files, 5MB each.</p>
                    <input type="file" id="screenshots" name="screenshots[]" accept="image/*" multiple
                        class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="urgent" value="0">
                    <input type="checkbox" id="urgent" name="urgent" value="1" class="accent-red-600">
                    <label for="urgent" class="text-xs text-red-700">Mark as urgent</label>
                </div>
                <div id="form-error-message"
                    class="mb-4 p-3 rounded bg-red-100 text-red-800 border border-red-300 hidden"></div>
                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white font-bold py-2.5 rounded-xl shadow-lg transition text-base">Send
                    to Webmaster</button>
            </form>
            <div class="mt-6 text-xs text-slate-500 text-center">This form is for website technical issues only. For
                privacy or general questions, use the <a href="/facility/{{ $facility['slug'] ?? 'facility' }}#contact"
                    class="underline">Contact Us</a> page.</div>
        </div>
        <script>
            document.getElementById('webmaster-contact-form').addEventListener('submit', function(e) {
            var name = document.getElementById('name').value.trim();
            var email = document.getElementById('email').value.trim();
            var subject = document.getElementById('subject').value.trim();
            var message = document.getElementById('message').value.trim();
            var errorDiv = document.getElementById('form-error-message');
            var errors = [];
            if (!name) errors.push('Name is required.');
            if (!email) {
                errors.push('Email is required.');
            } else {
                // Simple email format validation
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                    errors.push('Please enter a valid email address.');
                }
            }
            if (!subject) errors.push('Subject is required.');
            if (!message) errors.push('Message is required.');
            if (errors.length > 0) {
                e.preventDefault();
                errorDiv.innerHTML = '<ul class="list-disc pl-5">' + errors.map(function(err) { return '<li>' + err + '</li>'; }).join('') + '</ul>';
                errorDiv.classList.remove('hidden');
            } else {
                errorDiv.classList.add('hidden');
            }
        });
        </script>
    </div>
</div>
@endsection