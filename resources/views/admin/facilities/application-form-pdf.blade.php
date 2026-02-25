<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employment Application</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #111827;
            line-height: 1.2;
            padding: 20px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .company-name {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            margin-top: 6px;
            margin-bottom: 8px;
        }

        .subtext {
            font-size: 8px;
            line-height: 1.2;
            margin-bottom: 12px;
            text-align: center;
        }

        .section-header {
            background: #d3d3d3;
            border: 1px solid #000;
            padding: 3px 4px;
            font-weight: bold;
            font-size: 10px;
            margin-top: 8px;
            margin-bottom: 0;
        }

        .form-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            border-top: none;
            margin-bottom: 4px;
        }

        .form-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            vertical-align: top;
            height: 16px;
        }

        .form-table td.label {
            font-weight: bold;
            font-size: 8px;
            background: white;
            padding: 1px 3px;
        }

        .form-table td.value {
            font-size: 9px;
            overflow: hidden;
            word-wrap: break-word;
        }

        .field-label {
            font-weight: bold;
            font-size: 8px;
            display: block;
            margin-bottom: 1px;
        }

        .checkbox {
            display: inline-block;
            width: 8px;
            height: 8px;
            border: 1px solid #000;
            line-height: 8px;
            text-align: center;
            font-size: 7px;
            margin-right: 2px;
        }

        .inline-item {
            display: inline-block;
            margin-right: 10px;
        }

        .row-spacer {
            height: 6px;
        }

        .work-exp-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            border-top: none;
            margin-bottom: 4px;
            font-size: 8px;
        }

        .work-exp-table td {
            border: 1px solid #000;
            padding: 2px;
            vertical-align: top;
            height: auto;
        }

        .work-exp-table .header {
            background: #e8e8e8;
            font-weight: bold;
            text-align: center;
            padding: 2px;
        }

        .equal-opportunity {
            font-size: 9px;
            line-height: 1.4;
            margin-bottom: 12px;
            text-align: justify;
        }
    </style>
</head>

<body>
    <div class="title">EMPLOYMENT APPLICATION</div>
    <div class="company-name">{{ $facility->name ?? 'BIO-PACIFIC OPERATIONAL, INC.' }}</div>

    <!-- Equal Opportunity Statement -->
    <div class="equal-opportunity">
        <strong>{{ $facility->name ?? 'Our Company' }}</strong> is an equal opportunity and affirmative action employer
        committed to diversifying its workforce. It is the Company's policy to provide equal employment opportunities to
        all employees and applicants without regard to race, color, creed, sex, gender, gender identity or expression,
        national origin, ancestry, age, mental or physical disability, genetic information, marital status, familial
        status, sexual orientation, military or veteran status or any other legally protected status under applicable
        law or similar factors that are not job-related. No question on the application is intended to secure
        information about these subjects. We encourage all qualified individuals to apply for employment. We also
        provide reasonable accommodation to qualified individuals with disabilities in accordance with the Americans
        with Disabilities Act and applicable state and local law. If you require assistance or a reasonable
        accommodation to complete the application or any aspect of the application process, please contact the Human
        Resources Department or the hiring manager.
    </div>

    <!-- PERSONAL INFORMATION SECTION -->
    <div class="section-header">PERSONAL INFORMATION</div>

    <table class="form-table">
        <tr>
            <td style="width: 30%;">
                <span class="field-label">Date</span>
                <div class="value">{{ $application->created_at?->format('m/d/Y') ?? '' }}</div>
            </td>
            <td style="width: 40%;">
                <span class="field-label">Email Address</span>
                <div class="value">{{ $application->email ?? '' }}</div>
            </td>
            <td style="width: 30%;">
                <span class="field-label">Phone</span>
                <div class="value">{{ $application->phone_number ?? '' }}</div>
            </td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td style="width: 35%; vertical-align: middle;">
                <span class="field-label">Last Name</span>
                <div class="value">{{ $application->last_name ?? '' }}</div>
            </td>
            <td style="width: 35%; vertical-align: middle;">
                <span class="field-label">First Name</span>
                <div class="value">{{ $application->first_name ?? '' }}</div>
            </td>
            <td style="width: 35%;">
                <span class="field-label">Middle Name</span>
                <div class="value">{{ ($application->middle_name ?? '') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td style="width: 100%;">
                <span class="field-label">Current Address</span>
                <div class="value">{{ $application->current_address ?? '' }}</div>
            </td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td style="width: 40%;">
                <span class="field-label">City</span>
                <div class="value">{{ $application->city ?? '' }}</div>
            </td>
            <td style="width: 20%;">
                <span class="field-label">State</span>
                <div class="value">{{ $application->state ?? '' }}</div>
            </td>
            <td style="width: 20%;">
                <span class="field-label">Zip Code</span>
                <div class="value">{{ $application->zip_code ?? '' }}</div>
            </td>
            <td style="width: 20%;">
                <span class="field-label">County</span>
                <div class="value">{{ $application->county ?? '' }}</div>
            </td>
        </tr>
    </table>

    <!-- POSITION DESIRED SECTION -->
    <div class="section-header">POSITION DESIRED</div>
    <table class="form-table">
        <tr>
            <td style="width: 50%;">
                <span class="field-label">Position Applied For</span>
                <div class="value">{{ $application->position_applied_for ?? ($application->position?->title ?? '') }}
                </div>
            </td>
            <td style="width: 50%;">
                <span class="field-label">Employment Type</span>
                <div class="value">
                    @if($application->employment_type === 'full_time')
                    Full Time
                    @elseif($application->employment_type === 'part_time')
                    Part Time
                    @elseif($application->employment_type === 'temporary')
                    Temporary
                    @elseif($application->employment_type === 'other')
                    Other{{ $application->employment_type_other ? ': ' . $application->employment_type_other : '' }}
                    @else
                    {{ $application->employment_type ?? '' }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td style="width: 33%;">
                <span class="field-label">Wage/Salary Expected</span>
                <div class="value">{{ $application->wage_salary_expected ?? '' }}</div>
            </td>
            <td style="width: 33%;">
                <span class="field-label">Date Available to Start</span>
                <div class="value">{{ $application->date_available?->format('m/d/Y') ?? '' }}</div>
            </td>
            <td style="width: 34%;">
                <span class="field-label">Shift Preference</span>
                <div class="value">{{ ucfirst($application->shift_preference ?? '') }}</div>
            </td>
        </tr>
    </table>

    <table class="form-table" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 50%;">
                <span class="field-label">Have You Ever Worked For This Company?</span>
                <div class="value">
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->worked_here_before ? 'X' : '' }}</span> Yes
                    </span>
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->worked_here_before === false ? 'X' : '' }}</span> No
                    </span>
                </div>
            </td>
            <td style="width: 50%;">
                <span class="field-label">If Yes, When And Where?</span>
                <div class="value">{{ $application->worked_here_when_where ?? '' }}</div>
            </td>
        </tr>
    </table>

    <table class="form-table" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 50%;">
                <span class="field-label">Have You Ever Applied To This Company?</span>
                <div class="value">
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->applied_here_before ? 'X' : '' }}</span> Yes
                    </span>
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->applied_here_before === false ? 'X' : '' }}</span> No
                    </span>
                </div>
            </td>
            <td style="width: 50%;">
                <span class="field-label">If Yes, When And Where?</span>
                <div class="value">{{ $application->applied_here_when_where ?? '' }}</div>
            </td>
        </tr>
    </table>

    <table class="form-table" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 50%;">
                <span class="field-label">Do You Have Any Relatives Who Work for the Company?</span>
                <div class="value">
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->relatives_work_here ? 'X' : '' }}</span> Yes
                    </span>
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->relatives_work_here === false ? 'X' : '' }}</span> No
                    </span>
                </div>
            </td>
            <td style="width: 50%;">
                <span class="field-label">If Yes, Please Specify</span>
                <div class="value">{{ $application->relatives_details ?? '' }}</div>
            </td>
        </tr>
    </table>

    <table class="form-table" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 50%;">
                <span class="field-label">Do You Have a Valid Driver's License?</span>
                <div class="value">
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->has_drivers_license ? 'X' : '' }}</span> Yes
                    </span>
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->has_drivers_license === false ? 'X' : '' }}</span> No
                    </span>
                </div>
            </td>
            <td colspan="3" style="width: auto;">
                <span class="field-label">License #/State/Expiration</span>
                <div class="value">{{ $application->drivers_license_number ?? '' }} / {{
                    $application->drivers_license_state ?? '' }} / {{
                    $application->drivers_license_expiration?->format('m/d/Y') ?? '' }}</div>
            </td>
        </tr>
    </table>

    <!-- REFERRAL SOURCE SECTION -->
    <div class="section-header">REFERRAL SOURCE</div>
    <table class="form-table" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 100%;">
                <span class="field-label">How Did You Hear About Us?</span>
                <div class="value">
                    @switch($application->how_heard_about_us)
                    @case('newspaper')
                    Newspaper Ad
                    @break
                    @case('internet')
                    Internet
                    @break
                    @case('school')
                    School
                    @break
                    @case('job_fair')
                    Job Fair
                    @break
                    @case('agency')
                    Employment Agency
                    @break
                    @case('referral')
                    Employee Referral
                    @break
                    @case('walk_in')
                    Walk-In
                    @break
                    @case('other')
                    Other{{ $application->how_heard_other ? ': ' . $application->how_heard_other : '' }}
                    @break
                    @default
                    {{ $application->how_heard_about_us ?? '' }}
                    @endswitch
                </div>
            </td>
        </tr>
    </table>

    <!-- WORK AUTHORIZATION SECTION -->
    <div class="section-header">WORK AUTHORIZATION</div>
    <table class="form-table" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 100%;">
                <span class="field-label">Are You Legally Authorized to Work in the USA?</span>
                <div class="value">
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->authorized_to_work_usa ? 'X' : '' }}</span> Yes
                    </span>
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->authorized_to_work_usa === false ? 'X' : '' }}</span> No
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="form-table" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 100%;">
                <span class="field-label">May We Contact Your Current Employer?</span>
                <div class="value">
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->contact_current_employer ? 'X' : '' }}</span> Yes
                    </span>
                    <span class="inline-item">
                        <span class="checkbox">{{ $application->contact_current_employer === false ? 'X' : '' }}</span>
                        No
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- EMPLOYMENT COMPLIANCE SECTION -->
    <div class="section-header">EMPLOYMENT COMPLIANCE</div>
    <table class="form-table" style="margin-bottom: 2px;">
        <tr>
            <td style="width: 100%; padding: 3px;">
                <p style="font-size: 9px; line-height: 1.3; margin-bottom: 4px;">
                    To comply with the Immigration Reform And Control Act, if you are hired, you will be required to
                    provide documents to establish your identity and your authorization to work in the USA. Such
                    documents will be required within the first three (3) business days following your hire or upon your
                    first work day if your employment will be less than three (3) days.
                </p>
                <p style="font-size: 9px; line-height: 1.3; margin-bottom: 4px;">
                    If employed by the <strong>{{ $facility->name ?? 'MARINER HEALTH CENTRAL, INC.' }}</strong>, you
                    will be subject to its Employee Handbook, Code
                    of Conduct, Employment Dispute Resolution Program, and all policies and procedures.
                </p>
            </td>
        </tr>
    </table>

    <!-- PAGE BREAK FOR PDF -->
    <div style="page-break-before: always;"></div>
    <div style="margin: 4px 0 18px 0; font-size: 10px;">Application Form: {{ $application->first_name ??
        '' }} {{ $application->last_name ?? '' }}</div>

    <!-- WORK EXPERIENCE SECTION (Page 2) -->
    <div class="section-header" style="margin-top: 0;">WORK EXPERIENCE (Most Recent First)</div>
    @for ($i = 0; $i < 3; $i++) @php $exp=$application->work_experience[$i] ?? null;
        @endphp
        <table class="work-exp-table" style="margin-bottom: 12px;">
            <tr style="height: 100px;">
                <td style="width: 3%; text-align: center; font-weight: bold;">{{ $i+1 }}</td>
                <td style="width: 37%;">
                    <span class="field-label">NAME AND ADDRESS OF EMPLOYER</span>
                    <div class="value mt-6">{{ is_array($exp) ? ($exp['employer_name'] ?? '') : ($exp->employer_name ??
                        '')
                        }}</div>
                    <div class="value">{{ is_array($exp) ? ($exp['employer_address'] ?? '') : ($exp->employer_address ??
                        '') }}</div>
                </td>
                <td style="width: 30%;">
                    <span class="field-label">STARTING POSITION</span>
                    <div class="value mt-6">{{ is_array($exp) ? ($exp['starting_position'] ?? 'N/A') :
                        ($exp->starting_position
                        ?? '') }}</div>
                </td>
                <td style="width: 30%;">
                    <span class="field-label">ENDING POSITION</span>
                    <div class="value mt-6">{{ is_array($exp) ? ($exp['ending_position'] ?? 'N/A') :
                        ($exp->ending_position ??
                        '') }}</div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <span class="field-label">FROM Mo___ Yr___ TO Mo___ Yr___</span>
                    <div class="value">{{ is_array($exp) ? ($exp['date_from'] ?? 'N/A') : ($exp->date_from ?? '') }} -
                        {{
                        is_array($exp) ? ($exp['date_to'] ?? '') : ($exp->date_to ?? 'N/A') }}</div>
                    <span class="field-label mt-14">PHONE NUMBER</span>
                    <div class="value">{{ is_array($exp) ? ($exp['phone'] ?? 'N/A') : ($exp->phone ?? 'N/A')
                        }}
                    </div>
                </td>
                <td>
                    <span class="field-label">NAME & TITLE OF SUPERVISOR</span>
                    <div class="value mt-6">{{ is_array($exp) ? ($exp['supervisor_name'] ?? 'N/A') :
                        ($exp->supervisor_name ??
                        '') }}</div>
                </td>
                <td>
                    <span class="field-label">REASON FOR LEAVING</span>
                    <div class="value mt-6">{{ is_array($exp) ? ($exp['reason_for_leaving'] ?? 'N/A') :
                        ($exp->reason_for_leaving ?? '') }}</div>
                    <span class="field-label mt-14">Eligible for rehire?</span>
                    <span class="checkbox">{{ is_array($exp) ? (($exp['eligible_for_rehire'] ?? '') === 'yes' ? 'X' :
                        '') : ($exp->eligible_for_rehire === 'yes' ? 'X' : '') }}</span> Yes
                    <span class="checkbox">{{ is_array($exp) ? (($exp['eligible_for_rehire'] ?? '') === 'no' ? 'X' : '')
                        : ($exp->eligible_for_rehire === 'no' ? 'X' : '') }}</span> No
                </td>
            </tr>
        </table>
        @endfor

        <!-- May we contact your current employer listed above? -->
        <table class="form-table my-10">
            <tr>
                <td style="width: 100%;">
                    <span class="field-label">May we contact your current employer listed above?</span>
                    <span class="checkbox">{{ $application->contact_current_employer ? 'X' : '' }}</span> YES
                    <span class="checkbox">{{ $application->contact_current_employer === false ? 'X' : '' }}</span> NO
                </td>
            </tr>
        </table>

        <!-- Work history description section -->
        <div style="font-size: 9px; margin-bottom: 8px;">
            Use this space to describe any previous work history and or/detail particular job responsibilities listed
            above that you believe are important or should be considered. Include any additional information that you
            feel may be relevant to the job for which you are applying.
        </div>
        <div class="value pb-4"
            style="border-bottom: 1px solid #000; height: 14px; margin-bottom: 8px; margin-top: 20px;">{{
            $application->work_history_details ?? 'N/A' }}</div>

        <!-- Additional references section -->
        <div style="font-size: 9px; margin-bottom: 8px;">
            List additional references, including address and telephone
        </div>
        <div class="value pb-4"
            style="border-bottom: 1px solid #000; height: 14px; margin-bottom: 8px; margin-top: 20px;">{{
            $application->additional_references ?? 'N/A' }}</div>

        <!-- PREVIOUS ADDRESS FOR THE PAST 7 YEARS SECTION -->
        <div class="section-header" style="margin-top: 24px;">PREVIOUS ADDRESS FOR THE PAST 7 YEARS (Most Recent First)
        </div>
        <table class="form-table" style="font-size: 8px; margin-bottom: 8px;">
            <tr>
                <td style="width: 3%; text-align: center; font-weight: bold;">&nbsp;</td>
                <td style="width: 37%; font-weight: bold;">Previous Address</td>
                <td style="width: 24%; font-weight: bold;">City</td>
                <td style="width: 7%; font-weight: bold;">State</td>
                <td style="width: 7%; font-weight: bold;">Zip Code</td>
                <td style="width: 10%; font-weight: bold;">County</td>
                <td style="width: 12%; font-weight: bold;">Phone Number</td>
            </tr>
            @for ($i = 0; $i < 7; $i++) @php $addr=$application->previous_addresses[$i] ?? null;
                @endphp
                <tr>
                    <td style="text-align: center; vertical-align: top;">{{ $i+1 }}</td>
                    <td style="vertical-align: top;">
                        <div class="value">{{ is_array($addr) ? ($addr['address'] ?? '') : ($addr->address ?? '') }}
                        </div>
                    </td>
                    <td colspan="4" style="vertical-align: top;"></td>
                    <td style="vertical-align: top;">
                        <div class="value">{{ is_array($addr) ? ($addr['phone'] ?? '') : ($addr->phone ?? '') }}</div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="vertical-align: top;">
                        <div class="value">{{ is_array($addr) ? ($addr['city'] ?? '') : ($addr->city ?? '') }}</div>
                    </td>
                    <td style="vertical-align: top;">
                        <div class="value">{{ is_array($addr) ? ($addr['state'] ?? '') : ($addr->state ?? '') }}</div>
                    </td>
                    <td style="vertical-align: top;">
                        <div class="value">{{ is_array($addr) ? ($addr['zip'] ?? '') : ($addr->zip ?? '') }}</div>
                    </td>
                    <td style="vertical-align: top;">
                        <div class="value">{{ is_array($addr) ? ($addr['county'] ?? '') : ($addr->county ?? '') }}</div>
                    </td>
                </tr>
                @endfor
        </table>

        <!-- PAGE BREAK FOR PDF -->
        <div style="page-break-before: always;"></div>
        <div style="margin: 4px 0 18px 0; font-size: 10px;">Application Form: {{ $application->first_name ??
            '' }} {{ $application->last_name ?? '' }}</div>

        <!-- RECORD OF EDUCATION SECTION (Page 3) -->
        <div class="section-header" style="margin-top: 0;">RECORD OF EDUCATION</div>
        <table class="form-table" style="font-size: 8px; margin-bottom: 8px;">
            <tr>
                <td rowspan="2" style="width: 22%; font-weight: bold; text-align: center; vertical-align: middle;">Name
                    and Address of School(s)
                </td>
                <td colspan="2" style="width: 14%; font-weight: bold; text-align: center;">Dates Attended</td>
                <td colspan="2" style="width: 8%; font-weight: bold; text-align: center;">Graduated</td>
                <td rowspan="2" style="width: 14%; font-weight: bold; text-align: center; vertical-align: middle;">Type
                    of Degree/Diploma Received or Expected
                </td>
                <td rowspan="2" style="width: 22%; font-weight: bold; text-align: center; vertical-align: middle;">
                    Major/Minor
                    Fields of Study</td>
            </tr>
            <tr>
                <td style="width: 7%; font-weight: bold; text-align: center;">From<br>Mo./Yr.</td>
                <td style="width: 7%; font-weight: bold; text-align: center;">To<br>Mo./Yr.</td>
                <td style="width: 4%; font-weight: bold; text-align: center;">Yes</td>
                <td style="width: 4%; font-weight: bold; text-align: center;">No</td>

            </tr>
            <!-- High School (Last Attended) -->
            <tr>
                <td style="font-weight: bold;" colspan="7">High School (Last Attended)</td>
            </tr>
            <tr class="h-24">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr class="h-24">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <!-- Colleges/Universities -->
            <tr>
                <td style="font-weight: bold;" colspan="7">Colleges/Universities</td>
            </tr>
            <tr class="h-24">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr class="h-24">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <!-- Graduate School -->
            <tr>
                <td style="font-weight: bold;" colspan="7">Graduate School</td>
            </tr>
            <tr class="h-24">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr class="h-24">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <!-- Other (Business, Technical, Secretarial, etc.) -->
            <tr>
                <td style="font-weight: bold;" colspan="7">Other (Business, Technical, Secretarial, etc.)</td>
            </tr>
            <tr class="h-24">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <!-- PROFESSIONAL AFFILIATIONS & LICENSES SECTION -->
        <div style="font-size: 9px; margin-bottom: 6px; margin-top: 12px;">
            Please list any professional affiliations or accreditations that have a direct bearing upon your
            qualification
            for the job for which you are applying. Include all licenses and certifications.
        </div>
        <div style="border-bottom: 1px solid #000; height: 18px; margin-bottom: 8px;"></div>
        <div style="border-bottom: 1px solid #000; height: 18px; margin-bottom: 12px;"></div>

        <div style="font-size: 9px; margin-bottom: 6px;">
            Have you ever had your profession license or certification suspended, revoked or restricted?
            <span style="margin-left: 12px;"> <span
                    style="border: 1px solid #000; width: 10px; height: 10px; display: inline-block; text-align: center;">&nbsp;</span>
                Yes</span>
            <span style="margin-left: 12px;"> <span
                    style="border: 1px solid #000; width: 10px; height: 10px; display: inline-block; text-align: center;">&nbsp;</span>
                No</span>
            <span style="margin-left: 12px;">If yes, please explain:</span>
        </div>
        <div style="border-bottom: 1px solid #000; height: 18px; margin-bottom: 8px;"></div>
        <div style="border-bottom: 1px solid #000; height: 18px; margin-bottom: 12px;"></div>

        <div style="font-size: 9px; margin-bottom: 6px;">
            Do you have any special skills or abilities that directly relate to the job for which you are applying?
        </div>
        <div style="border-bottom: 1px solid #000; height: 18px; margin-bottom: 8px;"></div>
        <div style="border-bottom: 1px solid #000; height: 18px; margin-bottom: 12px;"></div>

</body>

</html>