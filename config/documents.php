<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User-facing document terminology
    |--------------------------------------------------------------------------
    |
    | Employee files are stored as Upload records. Use "document" language in
    | the UI; keep Upload/UploadType as internal model names.
    |
    */

    'labels' => [
        'singular' => 'document',
        'plural' => 'documents',
        'type' => 'Document type',
        'types' => 'Document types',
        'management' => 'Documents Management',
        'facility_page_title' => 'Facility Documents',
        'center' => 'Document Center',
        'on_file' => 'Documents on file',
        'required_not_on_file' => 'Required not on file',
        'expiring_in_60_days' => 'Expiring in 60 days',
        'expiration_date' => 'Expiration Date',
        'need_tracking' => 'Need Tracking',
        'my_documents' => 'My documents',
        'upload_modal_title' => 'Upload document',
        'uploaded' => 'Uploaded',
        'uploaded_by' => 'Uploaded by',
        'select_type' => 'Select document type',
        'verification_status' => 'Review status',
        'submission_reason' => 'Reason for upload',
        'upload_review_notice' => 'Uploads are reviewed by your facility DSD, DON, or administrator before they count toward compliance.',
        'certifications_subset_note' => 'Licenses and certifications are a subset of your position documents.',
    ],

    'verification' => [
        'not_submitted' => 'Not submitted',
        'pending' => 'Pending for Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],

    'messages' => [
        'created' => 'Document uploaded successfully.',
        'created_pending_review' => 'Document uploaded and submitted for review by your facility DSD, DON, or administrator.',
        'updated' => 'Document updated successfully.',
        'deleted' => 'Document deleted successfully.',
        'not_found' => 'Document not found.',
        'type_created' => 'Document type created successfully.',
        'type_updated' => 'Document type updated successfully.',
        'type_deleted' => 'Document type deleted successfully.',
    ],

];
