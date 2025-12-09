<?php

namespace Tests;

abstract class TenantTestCase extends TestCase
{
    use RefreshDatabaseWithTenant;
}
