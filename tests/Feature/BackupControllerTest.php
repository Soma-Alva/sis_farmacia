<?php

namespace Tests\Feature;

use Tests\TestCase;

class BackupControllerTest extends TestCase
{
    public function test_backups_index_requires_authentication(): void
    {
        $response = $this->get('/backups');

        $response->assertStatus(302);
    }
}
