<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Permission is hereby granted, free of charge, to any person obtaining a copy of 
  this software and associated documentation files (the “Software”), to deal in 
  the Software without restriction, including without limitation the rights to 
  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies 
  of the Software, and to permit persons to whom the Software is furnished to do 
  so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all 
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
  SOFTWARE.
*/
?>
    </main>
    <footer class="footer mt-auto bg-dark text-light py-1 fixed-bottom">
      <div class="px-3">
        <div class="row">
          <div class="col-sm-6">
<?php
$footer_org  = getSetting($link, 'footer_org');
$org_link    = getSetting($link, 'org_link');
$feedback    = getSetting($link, 'feedback_link');
$privacy     = getSetting($link, 'privacy_policy_link');
?>
            <p class="mb-0"><small class="text-light">&copy; <?=date("Y")?> <a class="link-light" style="text-decoration: none;" href="<?=$org_link?>"><?=$footer_org?></a></small></p>
          </div>
          <div class="col-sm-6 d-none d-sm-block">
            <p class="mb-0" style="text-align: right;">
              <small class="text-muted">
                <a class="link-light" style="text-decoration: underline;" href="/admin">Admin Login</a>
                <a class="ml-3 link-light" style="text-decoration: underline;" href="<?=$privacy?>">Privacy Policy</a>
                <a class="ml-3 link-light" style="text-decoration: underline;" href="<?=$feedback?>">Feedback</a>
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
