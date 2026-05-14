<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;


// pest()->extend(TestCase::class)->in('Feature', 'Unit');
pest()->extend(TestCase::class)
->use(DatabaseTransactions::class)
->in('Feature');
