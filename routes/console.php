<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('notifications:send')->dailyAt('09:00');
