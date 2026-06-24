<?php

return [

    'interval_unit' => [
        'weeks' => 'Week(s)',
        'months' => 'Month(s)',
        'years' => 'Year(s)',
    ],

    'event_type' => [
        'organization' => 'Organization',
        'competition' => 'Competition',
    ],

    'application_status' => [
        'Domain\EventApplications\States\DraftApplicationState' => 'Draft',
        'Domain\EventApplications\States\SubmittedApplicationState' => 'Submitted',
        'Domain\EventApplications\States\InValidationApplicationState' => 'In Validation',
        'Domain\EventApplications\States\ReturnedForCorrectionApplicationState' => 'Returned for Correction',
        'Domain\EventApplications\States\ApprovedApplicationState' => 'Approved',
        'Domain\EventApplications\States\RejectedApplicationState' => 'Rejected',
        'Domain\EventApplications\States\PublishedApplicationState' => 'Published',
    ],

];
