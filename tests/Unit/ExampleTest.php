<?php

namespace Tests\Unit;

use App\Services\Import\ImportAction;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_import_action_values_are_stable(): void
    {
        $this->assertSame('created', ImportAction::Created->value);
        $this->assertSame('updated', ImportAction::Updated->value);
        $this->assertSame('skipped', ImportAction::Skipped->value);
        $this->assertSame('failed', ImportAction::Failed->value);
        $this->assertSame('dry_run', ImportAction::DryRun->value);
    }
}
