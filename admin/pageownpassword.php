  <div class="container collapse notransition" id="updatepassword" data-bs-parent="#actions">
    <p>Welcome, <?=$_SESSION['firstname']?>!</p>
    <p>Change Your Password</p>
    <form action="updateownpassword.php" method="post">
      <div class="mb-3">
        <label for="oldpassword" class="form-label">Old Password<span class="required">*</span></label>
        <input type="password" class="form-control" id="oldpassword" name="oldpassword" required>
      </div>
      <div class="mb-3">
        <label for="updateownpassword" class="form-label">New Password<span class="required">*</span></label>
        <input type="password" class="form-control" id="updateownpassword" name="updateownpassword" required>
      </div>
      <div class="mb-3">
        <label for="updateownpasswordconfirm" class="form-label">New Password Confirm<span class="required">*</span></label>
        <input type="password" class="form-control" id="updateownpasswordconfirm" name="updateownpasswordconfirm" required>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
  </div>
