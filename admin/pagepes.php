  <div class="container collapse notransition" id="pes" data-bs-parent="#actions">
    <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
    <h1 class="my-3">Post-Event Summaries</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pesstyle">View Post-Event Summary Style Guide</button>
    <p><em>It is </em>strongly recommended<em> that you do not draft your summary and impact statements on this page, since reloads may cause you to lose your work.</em></p>
    <form action="addpes.php" method="post">
      <div class="mb-3">
        <label for="addpestitle" class="form-label">Title<span class="required">*</span></label>
        <input type="text" class="form-control" id="addpestitle" name="pestitle" maxlength="255" required placeholder="Summary of the [Service] Service Event">
        <p class="text-muted"><small>Provide a brief but descriptive title that defines the event using the provided format.<br>Maximum character count: 255</small></p>
      </div>
      <div class="mb-3">
        <label for="addpesdate" class="form-label">Summary Date</label>
        <input type="date" class="form-control" id="addpesdate" name="pesdate">
        <p class="text-muted"><small>Optional. If no date is provided, the current date will be used.</small></p>
      </div>
      <div class="mb-3">
        <label for="addpessummary" class="form-label">Issue Summary<span class="required">*</span></label>
        <textarea class="form-control tinymce" id="addpessummary" name="pessummary" maxlength="5000" rows="8" required></textarea>
        <p class="text-muted"><small>Provide a synopsis of the issue here.<br>Maximum character count: 5000</small></p>
      </div>
      <div class="mb-3">
        <label for="addpesimpact" class="form-label">Issue Service Impact<span class="required">*</span></label>
        <textarea class="form-control tinymce" id="addpesimpact" name="pesimpact" maxlength="5000" rows="8" required></textarea>
        <p class="text-muted"><small>Provide a summary of the impact that the issue had on services and on adjuct services.<br>Maximum character count: 5000</small></p>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
  </div>
