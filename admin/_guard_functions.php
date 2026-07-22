<?php
/*
    Status Page
    Copyright (C) 2024 Sejin Kim

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

// Pure function only — no session_start/header/exit side effects — so this
// file is safe to require directly from tests. admin/_guard.php requires
// this file and then applies the side-effecting part (redirect + exit).

if (!function_exists('isAdminSessionValid')) {
  function isAdminSessionValid(array $session): bool {
    return isset($session['id']);
  }
}
