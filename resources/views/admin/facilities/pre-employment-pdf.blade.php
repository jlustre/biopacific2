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
            margin-bottom: 8px;
        }

        .company-name {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            margin-top: 30px;
            margin-bottom: 3px;
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
    <div class="company-name">{{ $facility->name ?? 'BIO-PACIFIC OPERATIONAL, INC.' }}</div>
    <div class="title">EMPLOYMENT APPLICATION</div>

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
            <td style="width: 50%;">
                <span class="field-label">Date</span>
                <div class="value">{{ $application->created_at?->format('m/d/Y') ?? '' }}</div>
            </td>
            <td style="width: 50%;">
                <span class="field-label">Email Address</span>
                <div class="value">{{ $application->email ?? '' }}</div>
            </td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td style="width: 50%; vertical-align: middle;">
                <span class="field-label">Name (Last)</span>
                <div class="value">{{ $application->last_name ?? '' }}</div>
            </td>
            <td style="width: 50%;">
                <span class="field-label">(First) / (Middle)</span>
                <div class="value">{{ ($application->first_name ?? '') }} / {{ ($application->middle_name ?? '') }}
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
            <td style="width: 28%;">
                <span class="field-label">City</span>
                <div class="value">{{ $application->city ?? '' }}</div>
            </td>
            <td style="width: 12%;">
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
            <td style="width: 20%;">
                <span class="field-label">Phone</span>
                <div class="value">{{ $application->phone_number ?? '' }}</div>
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
                <span class="field-label">Have You Ever Worked for This Company?</span>
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
                <span class="field-label">If Yes, When and Where?</span>
                <div class="value">{{ $application->worked_here_when_where ?? '' }}</div>
            </td>
        </tr>
    </table>

    <!-- PREVIOUS EMPLOYMENT SECTION -->
    <div class="section-header">PREVIOUS EMPLOYMENT</div>
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

    <!-- DRIVER'S LICENSE SECTION -->
    <div class="section-header">DRIVER'S LICENSE</div>
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

</body>

</html>