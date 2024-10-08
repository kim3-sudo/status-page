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
?>
    </main>
    <footer class="footer mt-auto bg-dark text-light py-1 fixed-bottom">
      <div class="px-3">
        <div class="row">
          <div class="col-sm-6">
<?php
$orow = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'footer_org'"));
$lrow = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'org_link'"));
$frow = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'feedback_link'"));
$prow = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'privacy_policy_link'"));
?>
            <p class="mb-0"><small class="text-light">&copy; <?=date("Y")?> <a class="link-light" style="text-decoration: none;" href="<?=$lrow['setting_value']?>"><?=$orow['setting_value']?></a></small></p>
          </div>
          <div class="col-sm-6 d-none d-sm-block">
            <p class="mb-0" style="text-align: right;">
              <small class="text-muted">
                <a class="link-light" style="text-decoration: underline;" href="/admin">Admin Login</a>
                <a class="ml-3 link-light" style="text-decoration: underline;" href="<?=$prow['setting_value']?>">Privacy Policy</a>
                <a class="ml-3 link-light" style="text-decoration: underline;" href="<?=$frow['setting_value']?>">Feedback</a>
              </small>
            </p>
          </div>
        </div>
      </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <script>
      // Enable tooltips for all tooltip triggers
      let tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      tooltipTriggerList.forEach((el) => {new bootstrap.Tooltip(el);});

      // Search driver
      const accordion = document.getElementsByClassName('accordion-collapse'); //li
      const serviceparent = document.getElementById("statusparent"); //ul
      function evaluatesearch() {
        var searchquery = document.getElementById("accordionsearchbar").value.toLowerCase(); //filter
        console.log("Searching for " + searchquery);
        for (i = 0; i < accordion.length; i++) {
          headelement = accordion[i].getElementsByClassName("servicehead")[0];
          textval = headelement.textContent || headelement.innerText;
          if (textval.toLowerCase().indexOf(searchquery) > -1) {
            // expand
            console.log("Expand " + accordion[i]);
            accordion[i].classList.remove("collapse");
          } else {
            // collapse
            console.log("Collapse " + accordion[i]);
            accordion[i].classList.add("collapse");
          }
        }
      }
    </script>
  </body>
  <!-- Well hello there. If you're reading this, perhaps you want a copy of this software? -->
  <!-- Find it on GitHub at https://github.com/kim3-sudo/status-page -->
</html>
