<?php

namespace App\Services\Import;

enum ImportAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Skipped = 'skipped';
    case Failed = 'failed';
    case DryRun = 'dry_run';
}
