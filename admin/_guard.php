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

// Every admin/*.php entry point must include this before doing anything else.
// Unlike the old per-file "header('Location: ...')" checks, this actually
// halts execution — PHP does not stop running just because header() was
// called, so the old pattern let unauthenticated requests execute the full
// privileged body beneath it.

require_once __DIR__ . '/_guard_functions.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isAdminSessionValid($_SESSION)) {
  header('Location: ../login.php');
  exit;
}
