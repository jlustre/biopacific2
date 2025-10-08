<div class="border-b border-gray-200">
    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
        <button type="button" onclick="showTab('basic')" id="basic-tab"
            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ ($activeTab ?? 'basic') === 'basic' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            aria-current="{{ ($activeTab ?? 'basic') === 'basic' ? 'page' : false }}">
            Basic Info
        </button>
        <button type="button" onclick="showTab('contact')" id="contact-tab"
            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ ($activeTab ?? '') === 'contact' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            aria-current="{{ ($activeTab ?? '') === 'contact' ? 'page' : false }}">
            Contact
        </button>
        <button type="button" onclick="showTab('content')" id="content-tab"
            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ ($activeTab ?? '') === 'content' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            aria-current="{{ ($activeTab ?? '') === 'content' ? 'page' : false }}">
            Content
        </button>
        <button type="button" onclick="showTab('colors')" id="colors-tab"
            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ ($activeTab ?? '') === 'colors' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            aria-current="{{ ($activeTab ?? '') === 'colors' ? 'page' : false }}">
            Colors
        </button>
        <button type="button" onclick="showTab('services')" id="services-tab"
            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ ($activeTab ?? '') === 'services' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            aria-current="{{ ($activeTab ?? '') === 'services' ? 'page' : false }}">
            Services
        </button>
        <button type="button" onclick="showTab('social')" id="social-tab"
            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ ($activeTab ?? '') === 'social' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            aria-current="{{ ($activeTab ?? '') === 'social' ? 'page' : false }}">
            Social Media
        </button>
        <button type="button" onclick="showTab('news')" id="news-tab"
            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ ($activeTab ?? '') === 'news' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            aria-current="{{ ($activeTab ?? '') === 'news' ? 'page' : false }}">
            News
        </button>
        <button type="button" onclick="showTab('sections')" id="sections-tab"
            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ ($activeTab ?? '') === 'sections' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            aria-current="{{ ($activeTab ?? '') === 'sections' ? 'page' : false }}">
            Sections
        </button>
    </nav>
</div>