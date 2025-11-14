'url' => url()->current(),
'logo' => is_array($facility) ? asset($facility['logo_url'] ?? 'images/default-logo.png') : (is_object($facility) ?
asset($facility->logo_url ?? 'images/default-logo.png') : asset('images/default-logo.png')),
'image' => is_array($facility) ? asset($facility['facility_image'] ?? 'images/default-og.jpg') : (is_object($facility) ?
asset($facility->facility_image ?? 'images/default-og.jpg') : asset('images/default-og.jpg')),
'description' => $metaDescription,
'address' => [
'@type' => 'PostalAddress',
'streetAddress' => is_array($facility) ? ($facility['address'] ?? '') : (is_object($facility) ? ($facility->address ??
'') : ''),
'addressLocality' => is_array($facility) ? ($facility['city'] ?? '') : (is_object($facility) ? ($facility->city ?? '') :
''),
'addressRegion' => is_array($facility) ? ($facility['state'] ?? '') : (is_object($facility) ? ($facility->state ?? '') :
''),
'postalCode' => is_array($facility) ? ($facility['zip'] ?? '') : (is_object($facility) ? ($facility->zip ?? '') : '')
],
'telephone' => is_array($facility) ? ($facility['phone'] ?? '') : (is_object($facility) ? ($facility->phone ?? '') : '')