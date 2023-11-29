  <div class="container collapse notransition" id="pes" data-bs-parent="#actions">
    <p>Welcome, <?=$_SESSION['firstname']?>!</p>
    <p>Post-Event Summaries</p>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pesstyle">View Post-Event Summary Style Guide</button>
    <p><em>It is </em>strongly recommended<em> that you do not draft your summary and impact statements on this page, since reloads may cause you to lose your work.</em></p>
    <form action="addpes.php" method="post">
      <div class="mb-3">
        <label for="addpestitle" class="form-label">Title</label>
        <input type="text" class="form-control" id="addpestitle" name="pestitle" maxlength="255" required placeholder="Summary of the [Service] Service Event">
        <p class="text-muted"><small>Provide a brief but descriptive title that defines the event using the provided format.<br>Maximum character count: 255</small></p>
      </div>
      <div class="mb-3">
        <label for="addpesdate" class="form-label">Summary Date</label>
        <input type="date" class="form-control" id="addpesdate" name="pesdate">
        <p class="text-muted"><small>If no date is provided, the current date will be used.</small></p>
      </div>
      <div class="mb-3">
        <label for="addpessummary" class="form-label">Issue Summary</label>
        <textarea class="form-control" id="addpessummary" name="pessummary" maxlength="5000" rows="8" required></textarea>
        <p class="text-muted"><small>Provide a synopsis of the issue here.<br>Maximum character count: 5000</small></p>
      </div>
      <div class="mb-3">
        <label for="addpesimpact" class="form-label">Issue Service Impact</label>
        <textarea class="form-control" id="addpesimpact" name="pesimpact" maxlength="5000" rows="8" required></textarea>
        <p class="text-muted"><small>Provide a summary of the impact that the issue had on services and on adjuct services.<br>Maximum character count: 5000</small></p>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
  </div>