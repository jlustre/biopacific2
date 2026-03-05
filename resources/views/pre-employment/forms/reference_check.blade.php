<div class="container mx-auto py-8 max-w-2xl">
    <h1 class="text-2xl font-bold mb-4 text-center">Confidential Reference Check</h1>
    <p class="mb-6">
        The person named below has applied for employment. He/she has authorized the collection of any information
        concerning past employment with your organization. The Company deals in long-term health care and it is of the
        utmost importance to us that we hire the right person for the job. Therefore we would appreciate your reply to
        the questions below. Thank you.
    </p>
    <form method="POST" action="#">
        @csrf
        <div class="mb-6 flex flex-col md:flex-row md:gap-4">
            <div class="flex-1">
                <label class="block font-semibold mb-1">Applicant's Name</label>
                <input type="text" name="applicant_name" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2" required>
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">Position Applied for</label>
                <input type="text" name="position_applied_for" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2" required>
            </div>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Employed By</label>
            <input type="text" name="employed_by" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
        </div>
        <div class="mb-6 flex flex-col md:flex-row md:gap-4">
            <div class="flex-1">
                <label class="block font-semibold mb-1">Name, Title, & Relationship of person contacted</label>
                <input type="text" name="contact_name_title_relationship" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="mb-6 flex flex-col md:flex-row md:gap-4">
            <div class="flex-1">
                <label class="block font-semibold mb-1">Employment From</label>
                <input type="text" name="employment_from" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">to</label>
                <input type="text" name="employment_to" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">Salary</label>
                <input type="text" name="salary" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">per</label>
                <input type="text" name="salary_per" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Position and description of duties</label>
            <input type="text" name="position_description" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Describe applicant's performance</label>
            <textarea name="performance" rows="3" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"></textarea>
        </div>
        <div class="mb-6 flex flex-col md:flex-row md:gap-4">
            <div class="flex-1">
                <label class="block font-semibold mb-1">Signature</label>
                <input type="text" name="signature" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">Title</label>
                <input type="text" name="title" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-end">
            <div class="flex-1 mr-4">
                <label class="block font-semibold mb-1">Supervisor</label>
                <input type="text" name="supervisor" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2" required>
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">Date</label>
                <input type="date" name="date" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2" required>
            </div>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Applicant's Signature</label>
            <input type="text" name="applicant_signature" class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2" required>
        </div>
        <div class="mb-6 italic text-gray-700">
            I hereby release from all liability the Company or people named below, and authorize him or her to release
            all information regarding my employment with them.
        </div>
        <button type="submit" class="btn btn-primary">Submit Reference Check</button>
    </form>
</div>