<?php

$dir = __DIR__ . '/../app/Livewire/Admin/Facilities/Checklist/PartGSections';
$files = glob($dir . '/*Competency*.php');

$brokenSubmit = <<<'PHP'
                    $this->addError('responses', 'Please rate all competency items before submitting.');

                return;
            }
        }
        }
PHP;

$fixedSubmit = <<<'PHP'
                    $this->addError('responses', 'Please rate all competency items before submitting.');

                    return;
                }
            }
        }
PHP;

$loadPattern = '/(\$this->employeeSignDate = \$assessment->employee_signed_at\?->format\(\'Y-m-d\'\) \?\? \'\'\;)\r?\n    \}\r?\n\r?\n        (\$this->loadSectionExcludedFromAssessment\(\$assessment\);)/';
$loadReplacement = "$1\n\n        $2\n    }";

foreach ($files as $path) {
    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }

    $orig = $content;
    $content = str_replace($brokenSubmit, $fixedSubmit, $content);
    $content = preg_replace($loadPattern, $loadReplacement, $content);

    if ($content !== $orig) {
        file_put_contents($path, $content);
        echo 'Fixed: ' . basename($path) . PHP_EOL;
    }
}
